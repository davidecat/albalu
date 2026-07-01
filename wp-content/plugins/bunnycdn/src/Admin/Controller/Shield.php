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

namespace Bunny\Wordpress\Admin\Controller;

use Bunny\Wordpress\Admin\Container;
use Bunny\Wordpress\Api\Pullzone\Shield as ShieldConfig;

class Shield implements ControllerInterface
{
    private const SUBMENU = ['shield/waf' => 'WAF', 'shield/ddos' => 'DDoS'];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function run(bool $isAjax): void
    {
        $cdnConfig = $this->container->getCdnConfig();
        if ($cdnConfig->isAgencyMode()) {
            if ($isAjax) {
                wp_send_json_error(['error' => 'There is no API key configured.'], 500);
            } else {
                $this->container->renderTemplateFile('error.api-unavailable.php', ['error' => 'There is no API key configured.']);
            }

            return;
        }
        if (!$cdnConfig->isAccelerated()) {
            if ($isAjax) {
                wp_send_json_error(['error' => 'Shield requires Bunny DNS.'], 500);
            } else {
                $this->container->renderTemplateFile('shield.unsupported.php', [], ['cssClass' => 'shield']);
            }

            return;
        }
        try {
            $pzId = $cdnConfig->getPullzoneId();
            if (null !== $pzId) {
                $shieldConfig = $this->container->getApiClient()->getShieldDetails($pzId);
            } else {
                throw new \Exception('Pullzone not found');
            }
        } catch (\Exception $e) {
            if ($isAjax) {
                wp_send_json_error(['error' => $e->getMessage()], 500);
            } else {
                $this->container->renderTemplateFile('error.api-unavailable.php', ['error' => $e->getMessage()]);
            }

            return;
        }
        if ($isAjax && isset($_GET['perform']) && 'statistics-ddos' === $_GET['perform']) {
            $stats = $this->container->getApiClient()->getShieldStatistics($shieldConfig->getShieldZoneId());
            wp_send_json_success(['chart' => ['logged' => $stats->chartDdosLogged, 'blocked' => $stats->chartDdosBlocked, 'challenged' => $stats->chartDdosChallenged, 'verified' => $stats->chartDdosVerified]]);

            return;
        }
        if ($isAjax && isset($_GET['perform']) && 'statistics-waf' === $_GET['perform']) {
            $stats = $this->container->getApiClient()->getShieldStatistics($shieldConfig->getShieldZoneId());
            wp_send_json_success(['chart' => ['triggers' => $stats->chartWafTriggers]]);

            return;
        }
        $subsection = $_GET['subsection'] ?? '';
        $messages = [];
        if (!empty($_POST)) {
            check_admin_referer('bunnycdn-save-shield');
            switch ($subsection) {
                case 'waf':
                    if (isset($_POST['action'])) {
                        $action = ShieldConfig::RULE_ACTIONS[$_POST['action']] ?? null;
                        if (null === $action) {
                            throw new \Exception('Invalid action');
                        }
                        $triggeredRules = $_POST['waf']['triggered_rule'] ?? [];
                        if (count($triggeredRules) > 0) {
                            $this->container->getApiClient()->saveShieldTriggeredRule($shieldConfig->getShieldZoneId(), $triggeredRules[0], $action);
                        } else {
                            $ruleIds = $_POST['waf']['rule'] ?? [];
                            $shieldConfig->importWafRulesFromPost($ruleIds, $action);
                        }
                    } else {
                        $shieldConfig->importWafFromPost($_POST);
                    }
                    break;
                case 'ddos':
                    $shieldConfig->importDdosFromPost($_POST);
                    break;
                default:
                    $this->container->renderTemplateFile('shield.error.php', ['error' => 'Unexpected subsection.']);

                    return;
            }
            $this->container->getApiClient()->savePullzoneShield($pzId, $shieldConfig);
            try {
                $shieldConfig = $this->container->getApiClient()->getShieldDetails($pzId);
            } catch (\Exception $e) {
                $this->container->renderTemplateFile('shield.error.php', ['error' => $e->getMessage()]);

                return;
            }
            $messages['success'] = ['Configuration saved.'];
        }
        wp_enqueue_script('echarts', $this->container->assetUrl('echarts.min.js'), ['jquery']);
        wp_enqueue_script('shield-charts', $this->container->assetUrl('shield-charts.js'), ['jquery', 'echarts']);
        switch ($subsection) {
            case 'waf':
                $rules = $this->container->getApiClient()->getShieldWafRules($shieldConfig->getShieldZoneId());
                $triggeredRules = $this->container->getApiClient()->getShieldWafRulesTriggered($shieldConfig->getShieldZoneId());
                $this->container->renderTemplateFile('shield.waf.php', ['submenu' => self::SUBMENU, 'shieldConfig' => $shieldConfig, 'messages' => $messages, 'rules' => $rules, 'triggeredRules' => $triggeredRules], ['cssClass' => 'shield']);

                return;
            case 'ddos':
                $this->container->renderTemplateFile('shield.ddos.php', ['submenu' => self::SUBMENU, 'shieldConfig' => $shieldConfig, 'messages' => $messages], ['cssClass' => 'shield']);

                return;
            default:
                $this->container->redirectToSection('shield', ['subsection' => 'waf']);

                return;
        }
    }
}
