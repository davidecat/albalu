<?php

// bunny.net WordPress Plugin
// Copyright (C) 2024-2026 BunnyWay d.o.o.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
declare(strict_types=1);

namespace Bunny\Wordpress\Api;

use Bunny\Wordpress\Api\Dnszone\Record;
use Bunny\Wordpress\Api\Dnszone\RecordType;
use Bunny\Wordpress\Api\Exception\AuthorizationException;
use Bunny\Wordpress\Api\Exception\InvalidJsonException;
use Bunny\Wordpress\Api\Exception\NotFoundException;
use Bunny\Wordpress\Api\Exception\PullzoneLocalUrlException;
use Bunny\Wordpress\Config\Optimizer;
use Bunny_WP_Plugin\GuzzleHttp\Client as HttpClient;

class Client
{
    public const BASE_URL = 'https://api.bunny.net';
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return array<string, mixed>
     */
    private function getPullzoneData(int $id): array
    {
        return $this->request('GET', sprintf('pullzone/%s', $id));
    }

    public function getPullzoneById(int $id): Pullzone\Info
    {
        $data = $this->getPullzoneData($id);

        return Pullzone\Info::fromApiResponse($data);
    }

    public function getPullzoneDetails(int $id): Pullzone\Details
    {
        $data = $this->getPullzoneData($id);
        $config = Optimizer::fromApiResponse($data);
        $edgerules = array_map(fn ($item) => Pullzone\Edgerule::fromApiResponse($item), $data['EdgeRules']);
        $hostnames = array_map(fn ($item) => $item['Value'], $data['Hostnames']);
        $bandwidthUsed = (int) $data['MonthlyBandwidthUsed'];
        $charges = (float) $data['MonthlyCharges'];

        return new Pullzone\Details($data['Id'], $data['Name'], $hostnames, $data['EnableAccessControlOriginHeader'], $data['AccessControlOriginHeaderExtensions'], $config, $bandwidthUsed, $charges, $edgerules, (bool) $data['ZoneSecurityEnabled'], $data['ZoneSecurityKey']);
    }

    public function getPullzoneStatistics(int $id, \DateTime $dateFrom, \DateTime $dateTo): Pullzone\Statistics
    {
        $data = $this->request('GET', 'statistics?'.http_build_query(['pullZone' => $id, 'dateFrom' => $dateFrom->format('Y-m-d'), 'dateTo' => $dateTo->format('Y-m-d')]));

        return new Pullzone\Statistics($data);
    }

    public function getBilling(): Billing\Info
    {
        $data = $this->request('GET', 'billing');
        $balance = (float) $data['Balance'];

        return new Billing\Info((int) floor($balance * 100));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    private function request(string $method, string $uri, ?string $body = null): array
    {
        $options = ['headers' => []];
        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options['headers']['Content-Type'] = 'application/json';
            $options['body'] = $body;
        }
        $response = $this->httpClient->request($method, $uri, $options);
        if (401 === $response->getStatusCode()) {
            throw new AuthorizationException();
        }
        if (404 === $response->getStatusCode()) {
            throw new NotFoundException();
        }
        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            throw new \Exception('api.bunny.net: no response ('.$response->getStatusCode().')');
        }
        $data = json_decode($response->getBody()->getContents(), true);
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            throw new \Exception('api.bunny.net: invalid JSON response');
        }

        return $data;
    }

    public function saveOptimizerConfig(Optimizer $config, int $pullzoneId): void
    {
        $this->savePullzoneDetails($pullzoneId, $config->toApiPostRequest());
    }

    /**
     * @param array<string, mixed> $data
     */
    public function savePullzoneDetails(int $pullzoneId, array $data): void
    {
        $body = json_encode($data, \JSON_THROW_ON_ERROR);
        $this->request('POST', sprintf('pullzone/%s', $pullzoneId), $body);
    }

    public function getUser(): User
    {
        $data = $this->request('GET', 'user');
        if (empty($data)) {
            throw new \Exception('Failure loading user from the api');
        }
        $name = '';
        if (!empty($data['FirstName']) || !empty($data['LastName'])) {
            $name = sprintf('%s %s', $data['FirstName'], $data['LastName']);
        }

        return new User($name, $data['Email']);
    }

    public function createPullzoneForCdn(string $name, string $originUrl): Pullzone\Info
    {
        $body = json_encode(['Name' => $name, 'OriginUrl' => $originUrl, 'IgnoreQueryStrings' => false, 'QueryStringVaryParameters' => ['ver'], 'UseStaleWhileOffline' => true, 'UseStaleWhileUpdating' => true, 'AccessControlOriginHeaderExtensions' => ['eot', 'ttf', 'woff', 'woff2', 'css', 'js']], \JSON_THROW_ON_ERROR);

        return $this->createPullzone($body);
    }

    private function createPullzone(string $body): Pullzone\Info
    {
        $options = ['headers' => ['Content-Type' => 'application/json'], 'body' => $body];
        $response = $this->httpClient->request('POST', '/pullzone', $options);
        $data = json_decode($response->getBody()->getContents(), true);
        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('api.bunny.net: invalid JSON response');
        }
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() <= 299) {
            return Pullzone\Info::fromApiResponse($data);
        }
        if (isset($data['ErrorKey'])) {
            if ('pullzone.validation' === $data['ErrorKey'] && "localhost URL is not supported\r\nParameter name: OriginUrl" === $data['Message']) {
                throw new PullzoneLocalUrlException();
            }
            throw new \Exception($data['Message'] ?? 'api.bunny.net: error while creating a pullzone.');
        }
        throw new \Exception('api.bunny.net: invalid response');
    }

    public function purgePullzoneCache(int $id): void
    {
        $this->request('POST', sprintf('pullzone/%s/purgeCache', $id));
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function updatePullzone(int $id, array $data): void
    {
        if (0 === $id) {
            throw new \Exception('Invalid pullzone ID');
        }
        $body = json_encode($data);
        if (false === $body) {
            throw new InvalidJsonException();
        }
        $this->request('POST', sprintf('pullzone/%d', $id), $body);
    }

    public function getStorageZone(int $id): Storagezone\Details
    {
        $data = $this->request('GET', sprintf('storagezone/%d', $id));

        return new Storagezone\Details($data['Id'], $data['Name'], $data['Password']);
    }

    /**
     * @param string[] $replicationRegions
     */
    public function createStorageZone(string $name, string $region, array $replicationRegions = []): Storagezone\Details
    {
        $replicationRegions = array_map(fn ($item) => strtoupper($item), $replicationRegions);
        $body = json_encode(['Name' => $name, 'Region' => $region, 'ReplicationRegions' => $replicationRegions, 'ZoneTier' => '1']);
        if (false === $body) {
            throw new InvalidJsonException();
        }
        $data = $this->request('POST', 'storagezone', $body);

        return new Storagezone\Details($data['Id'], $data['Name'], $data['Password']);
    }

    public function updateStorageZoneForOffloader(int $id, int $dnsZoneId, int $dnsRecordId, string $pathPrefix, string $syncToken): void
    {
        $body = json_encode(['OriginDnsZoneId' => $dnsZoneId, 'OriginDnsRecordId' => $dnsRecordId, 'WordPressCronToken' => $syncToken, 'WordPressCronPath' => $pathPrefix]);
        if (false === $body) {
            throw new InvalidJsonException();
        }
        $this->request('POST', sprintf('storagezone/%d', $id), $body);
    }

    public function updateStorageZoneCron(int $id, string $pathPrefix, string $syncToken): void
    {
        if (0 === $id) {
            throw new \Exception('Invalid storage zone ID');
        }
        $body = json_encode(['WordPressCronToken' => $syncToken, 'WordPressCronPath' => $pathPrefix]);
        if (false === $body) {
            throw new InvalidJsonException();
        }
        $this->request('POST', sprintf('storagezone/%d', $id), $body);
    }

    /**
     * @return Dnszone\Info[]
     */
    private function searchDnsZones(string $domain): array
    {
        $data = $this->request('GET', sprintf('dnszone?search=%s', $domain));
        if (!isset($data['TotalItems']) || !isset($data['Items'])) {
            throw new \Exception('Error requesting DNS zones.');
        }
        $zones = [];
        foreach ($data['Items'] as $item) {
            $zones[] = Dnszone\Info::fromArray($item);
        }

        return $zones;
    }

    public function findDnsRecordForHostname(string $hostname): ?Record
    {
        $parts = explode('.', $hostname);
        if (count($parts) < 2) {
            throw new \Exception('Invalid hostname: '.$hostname);
        }
        // filter out IP addresses
        if (preg_match('/\\d+$/', $parts[count($parts) - 1])) {
            throw new \Exception('Invalid hostname: '.$hostname);
        }
        $domain = sprintf('%s.%s', $parts[count($parts) - 2], $parts[count($parts) - 1]);
        $zones = $this->searchDnsZones($domain);
        foreach ($zones as $zone) {
            foreach ($zone->getRecords() as $record) {
                if (!in_array($record->getType(), [RecordType::A, RecordType::AAAA, RecordType::CNAME], true)) {
                    continue;
                }
                if ('' === $record->getName()) {
                    $full = $zone->getDomain();
                } else {
                    $full = $record->getName().'.'.$zone->getDomain();
                }
                if ($hostname === $full) {
                    return $record;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function addEdgeRuleToPullzone(int $pullzoneId, array $data): void
    {
        $body = json_encode($data, \JSON_THROW_ON_ERROR);
        $this->request('POST', sprintf('pullzone/%d/edgerules/addOrUpdate', $pullzoneId), $body);
    }

    public function findPullzoneByName(string $name): Pullzone\Info
    {
        $rows = $this->request('GET', sprintf('pullzone?search=%s', $name));
        foreach ($rows['Items'] as $data) {
            if ($data['Name'] !== $name) {
                continue;
            }

            return Pullzone\Info::fromApiResponse($data);
        }
        throw new \Exception('Could not find pullzone.');
    }

    /**
     * @return Pullzone\Info[]
     */
    public function searchPullzonesByOriginUrl(string $originUrl): array
    {
        $rows = $this->request('GET', sprintf('pullzone?search=%s', $originUrl));
        $result = [];
        foreach ($rows['Items'] as $data) {
            if (0 !== $data['OriginType']) {
                continue;
            }
            if ($data['OriginUrl'] !== $originUrl) {
                continue;
            }
            $result[] = Pullzone\Info::fromApiResponse($data);
        }

        return $result;
    }

    /**
     * @return Stream\Library[]
     */
    public function getStreamLibraries(): array
    {
        $rows = $this->request('GET', 'videolibrary');

        return array_map(fn ($item) => Stream\Library::fromApiResponse($item), $rows);
    }

    public function getStreamLibrary(int $id): Stream\Library
    {
        $item = $this->request('GET', sprintf('videolibrary/%d', $id));

        return Stream\Library::fromApiResponse($item);
    }

    /**
     * @param string[] $replicationRegions
     */
    public function createStreamLibrary(string $name, array $replicationRegions): Stream\Library
    {
        $replicationRegions = array_map(fn ($item) => strtoupper($item), $replicationRegions);
        $body = json_encode(['Name' => $name, 'ReplicationRegions' => $replicationRegions, 'PlayerVersion' => 2]);
        if (false === $body) {
            throw new InvalidJsonException();
        }
        $data = $this->request('POST', 'videolibrary', $body);

        return Stream\Library::fromApiResponse($data);
    }

    /**
     * @return Stream\Collection[]
     */
    public function getStreamCollections(Stream\Library $library): array
    {
        // @TODO pagination
        $response = $this->httpClient->request('GET', sprintf('https://video.bunnycdn.com/library/%d/collections?itemsPerPage=1000', $library->getId()), ['headers' => ['AccessKey' => $library->getAccessKey(), 'Content-Type' => 'application/json']]);
        if (200 !== $response->getStatusCode()) {
            throw new \Exception($response->getBody()->getContents());
        }
        $data = json_decode($response->getBody()->getContents(), true);
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            throw new \Exception('api.bunny.net: invalid JSON response');
        }

        return array_map(fn ($item) => Stream\Collection::fromApiResponse($item), $data['items']);
    }

    public function createStreamVideo(Stream\Library $library, string $title): Stream\Video
    {
        $response = $this->httpClient->request('POST', sprintf('https://video.bunnycdn.com/library/%d/videos', $library->getId()), ['headers' => ['AccessKey' => $library->getAccessKey(), 'Content-Type' => 'application/json'], 'body' => json_encode(['title' => $title], \JSON_THROW_ON_ERROR)]);
        if (200 !== $response->getStatusCode()) {
            throw new \Exception($response->getBody()->getContents());
        }
        $data = json_decode($response->getBody()->getContents(), true);
        if (null === $data) {
            throw new \Exception('api.bunny.net: empty response');
        }
        if (!is_array($data)) {
            throw new \Exception('api.bunny.net: invalid JSON response');
        }

        return Stream\Video::fromApiResponse($data);
    }

    public function getStreamVideo(Stream\Library $library, string $uuid): Stream\Video
    {
        $response = $this->httpClient->request('GET', sprintf('https://video.bunnycdn.com/library/%d/videos/%s', $library->getId(), $uuid), ['headers' => ['AccessKey' => $library->getAccessKey()]]);
        if (200 !== $response->getStatusCode()) {
            throw new \Exception($response->getBody()->getContents());
        }
        $data = json_decode($response->getBody()->getContents(), true);
        if (null === $data) {
            throw new \Exception('api.bunny.net: empty response');
        }
        if (!is_array($data)) {
            throw new \Exception('api.bunny.net: invalid JSON response');
        }

        return Stream\Video::fromApiResponse($data);
    }

    /**
     * @return Stream\Video[]
     */
    public function getStreamVideos(Stream\Library $library, ?string $collectionId): array
    {
        $url = sprintf('https://video.bunnycdn.com/library/%d/videos', $library->getId());
        if (null !== $collectionId) {
            $url .= sprintf('?collection=%s', $collectionId);
        }
        $response = $this->httpClient->request('GET', $url, ['headers' => ['AccessKey' => $library->getAccessKey()]]);
        if (200 !== $response->getStatusCode()) {
            throw new \Exception($response->getBody()->getContents());
        }
        $data = json_decode($response->getBody()->getContents(), true);
        if (null === $data) {
            throw new \Exception('api.bunny.net: empty response');
        }
        if (!is_array($data)) {
            throw new \Exception('api.bunny.net: invalid JSON response');
        }

        return array_map(fn ($item) => Stream\Video::fromApiResponse($item), $data['items']);
    }

    public function getShieldDetails(int $pullzoneId): Pullzone\Shield
    {
        $data = $this->request('GET', sprintf('shield/shield-zone/get-by-pullzone/%d', $pullzoneId));
        if (null === $data['data'] && 'not_found_or_unauthorised_access.shieldzone' === $data['error']['errorKey']) {
            $data = $this->request('POST', 'shield/shield-zone', json_encode(['pullZoneId' => $pullzoneId, 'shieldZone' => [
                'planType' => 0,
                'wafProfileId' => 1,
                // wordpress
                'learningMode' => false,
                'wafExecutionMode' => 0,
            ]], \JSON_THROW_ON_ERROR));
        }
        $wafEngine = $this->request('GET', 'shield/waf/engine-config');
        $buildArgs = ['shieldZoneId' => $data['data']['shieldZoneId'], 'wafEnabled' => $data['data']['wafEnabled'], 'wafExecutionMode' => $data['data']['wafExecutionMode'], 'wafDisabledRules' => $data['data']['wafDisabledRules'], 'wafLogonlyRules' => $data['data']['wafLogOnlyRules'], 'ddosChallengeWindow' => $data['data']['dDoSChallengeWindow'], 'ddosSensitivity' => $data['data']['dDoSShieldSensitivity']];
        foreach ($wafEngine['data'] as $item) {
            if ('detection_paranoia_level' === $item['name']) {
                $buildArgs['wafDetectionLevel'] = (int) $item['valueEncoded'];
                continue;
            }
            if ('executing_paranoia_level' === $item['name']) {
                $buildArgs['wafExecutionLevel'] = (int) $item['valueEncoded'];
                continue;
            }
            if ('blocking_paranoia_level' === $item['name']) {
                $buildArgs['wafBlockingLevel'] = (int) $item['valueEncoded'];
                continue;
            }
        }
        if (isset($data['data']['wafEngineConfig']) && is_array($data['data']['wafEngineConfig'])) {
            /** @var array{name: string, valueEncoded: int}[] $wafEngineConfig */
            $wafEngineConfig = $data['data']['wafEngineConfig'];
            foreach ($wafEngineConfig as $item) {
                if ('detection_paranoia_level' === $item['name']) {
                    $buildArgs['wafDetectionLevel'] = (int) $item['valueEncoded'];
                    continue;
                }
                if ('executing_paranoia_level' === $item['name']) {
                    $buildArgs['wafExecutionLevel'] = (int) $item['valueEncoded'];
                    continue;
                }
                if ('blocking_paranoia_level' === $item['name']) {
                    $buildArgs['wafBlockingLevel'] = (int) $item['valueEncoded'];
                    continue;
                }
            }
        }

        return new Pullzone\Shield($buildArgs);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public function getShieldWafRules(int $shieldZoneId): array
    {
        return $this->request('GET', sprintf('shield/waf/rules/%d', $shieldZoneId));
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public function getShieldWafRulesTriggered(int $shieldZoneId): array
    {
        $data = $this->request('GET', sprintf('shield/waf/rules/review-triggered/%d', $shieldZoneId));
        $rules = [];
        foreach ($data['triggeredRules'] as $rule) {
            $lastTimestamp = 0;
            $urls = [];
            foreach (array_keys($rule['topTargetedUrls']) as $url) {
                $urls[] = $url;
            }
            foreach ($rule['ruleLogs'] as $log) {
                if ($log['timestamp'] > $lastTimestamp) {
                    $lastTimestamp = $log['timestamp'];
                }
            }
            $rules[] = ['id' => $rule['ruleId'], 'description' => $rule['ruleDescription'], 'lastTimestamp' => (int) floor($lastTimestamp / 1000), 'urls' => $urls];
        }

        return $rules;
    }

    public function savePullzoneShield(int $pullzoneId, Pullzone\Shield $shieldConfig): void
    {
        $data = ['shieldZoneId' => $shieldConfig->getShieldZoneId(), 'shieldZone' => ['shieldZoneId' => $shieldConfig->getShieldZoneId(), 'wafEnabled' => $shieldConfig->isWafEnabled(), 'wafExecutionMode' => $shieldConfig->getWafExecutionMode(), 'wafDisabledRules' => $shieldConfig->getWafDisabledRules(), 'wafLogOnlyRules' => $shieldConfig->getWafLogOnlyRules(), 'wafEngineConfig' => [['name' => 'detection_paranoia_level', 'valueEncoded' => sprintf('%d', $shieldConfig->getWafDetectionLevel())], ['name' => 'executing_paranoia_level', 'valueEncoded' => sprintf('%d', $shieldConfig->getWafExecutionLevel())], ['name' => 'blocking_paranoia_level', 'valueEncoded' => sprintf('%d', $shieldConfig->getWafBlockingLevel())]], 'dDoSChallengeWindow' => $shieldConfig->getDDoSChallengeWindow(), 'dDoSShieldSensitivity' => $shieldConfig->getDDoSSensitivity()]];
        $body = json_encode($data, \JSON_THROW_ON_ERROR);
        $this->request('PATCH', 'shield/shield-zone', $body);
    }

    public function saveShieldTriggeredRule(int $shieldZoneId, string $ruleId, int $action): void
    {
        $this->request('POST', sprintf('shield/waf/rules/review-triggered/%d', $shieldZoneId), json_encode(['ruleId' => $ruleId, 'action' => $action], \JSON_THROW_ON_ERROR));
    }

    public function resetShieldWafRules(int $shieldZoneId): void
    {
        $this->request('PATCH', 'shield/shield-zone', json_encode(['shieldZoneId' => $shieldZoneId, 'shieldZone' => [
            'planType' => 0,
            'wafProfileId' => 1,
            // wordpress
            'learningMode' => false,
            'wafExecutionMode' => 0,
            // log
            'wafDisabledRules' => [],
            'wafLogOnlyRules' => [],
        ]], \JSON_THROW_ON_ERROR));
    }

    public function getShieldStatistics(int $shieldZoneId): Pullzone\ShieldStatistics
    {
        $chartDdosLogged = [];
        $chartDdosBlocked = [];
        $chartDdosChallenged = [];
        $chartDdosVerified = [];
        $chartWafTriggers = [];
        $data = $this->request('GET', sprintf('shield/metrics/overview/%d', $shieldZoneId));
        foreach ($data['data']['dDoSOverviewPastTwentyEightDays'] as $key => $info) {
            $date = \DateTime::createFromFormat('d-m-Y', $key);
            if (false === $date) {
                continue;
            }
            $dateStr = $date->format('Y-m-d');
            $chartDdosLogged[] = [$dateStr, $info['loggedRequests']];
            $chartDdosBlocked[] = [$dateStr, $info['blockedRequests']];
            $chartDdosChallenged[] = [$dateStr, $info['challengedRequests']];
            $chartDdosVerified[] = [$dateStr, $info['verifiedRequests']];
        }
        foreach ($data['data']['overviewPastTwentyEightDays'] as $key => $info) {
            $date = \DateTime::createFromFormat('d-m-Y', $key);
            if (false === $date) {
                continue;
            }
            $dateStr = $date->format('Y-m-d');
            $chartWafTriggers[] = [$dateStr, $info['wafTriggeredRules']];
        }

        return new Pullzone\ShieldStatistics($chartDdosLogged, $chartDdosBlocked, $chartDdosChallenged, $chartDdosVerified, $chartWafTriggers);
    }
}
