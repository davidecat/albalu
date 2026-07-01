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

namespace Bunny\Wordpress\Api\Pullzone;

class Shield
{
    public const DDOS_CHALLENGE_WINDOW_OPTIONS = [900 => '15 Minutes', 1800 => '30 Minutes', 3600 => '1 Hour', 21600 => '6 Hours', 43200 => '12 Hours', 86400 => '24 Hours'];
    public const DDOS_SENSITIVITY_OPTIONS = [0 => 'Lowest', 1 => 'Low', 2 => 'Medium', 3 => 'High', 4 => 'Extreme'];
    public const RULE_ACTIONS = ['enable' => 0, 'logonly' => 1, 'disable' => 2];
    private int $shieldZoneId;
    private bool $wafEnabled;
    private int $wafExecutionMode;
    private int $wafDetectionLevel;
    private int $wafExecutionLevel;
    private int $wafBlockingLevel;
    /** @var string[] */
    private array $wafDisabledRules;
    /** @var string[] */
    private array $wafLogonlyRules;
    private int $ddosChallengeWindow;
    private int $ddosSensitivity;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->shieldZoneId = (int) $data['shieldZoneId'];
        $this->wafEnabled = (bool) $data['wafEnabled'];
        $this->wafExecutionMode = (int) $data['wafExecutionMode'];
        $this->wafDetectionLevel = (int) $data['wafDetectionLevel'];
        $this->wafExecutionLevel = (int) $data['wafExecutionLevel'];
        $this->wafBlockingLevel = (int) $data['wafBlockingLevel'];
        $this->wafDisabledRules = (array) $data['wafDisabledRules'];
        $this->wafLogonlyRules = (array) $data['wafLogonlyRules'];
        $this->ddosChallengeWindow = (int) $data['ddosChallengeWindow'];
        $this->ddosSensitivity = (int) $data['ddosSensitivity'];
    }

    /**
     * @param array<string, array<string, mixed>> $data
     */
    public function importWafFromPost(array $data): void
    {
        if (isset($data['waf']['enabled'])) {
            $this->wafEnabled = (bool) $data['waf']['enabled'];
        } else {
            $this->wafEnabled = false;
        }
        if (isset($data['waf']['execution_mode'])) {
            $this->wafExecutionMode = (int) $data['waf']['execution_mode'];
        }
        if (isset($data['waf']['detection_level'])) {
            $this->wafDetectionLevel = (int) $data['waf']['detection_level'];
        }
        if (isset($data['waf']['execution_level'])) {
            $this->wafExecutionLevel = (int) $data['waf']['execution_level'];
        }
        if (isset($data['waf']['blocking_level'])) {
            $this->wafBlockingLevel = (int) $data['waf']['blocking_level'];
        }
    }

    /**
     * @param string[] $ruleIds
     */
    public function importWafRulesFromPost(array $ruleIds, int $action): void
    {
        foreach ($ruleIds as $ruleId) {
            $key = array_search($ruleId, $this->wafDisabledRules);
            if (false !== $key) {
                unset($this->wafDisabledRules[$key]);
            }
            $key = array_search($ruleId, $this->wafLogonlyRules);
            if (false !== $key) {
                unset($this->wafLogonlyRules[$key]);
            }
        }
        switch ($action) {
            case 0:
                // enable
                // noop
                break;
            case 1:
                // logonly
                $this->wafLogonlyRules = array_merge($this->wafLogonlyRules, $ruleIds);
                break;
            case 2:
                // disable
                $this->wafDisabledRules = array_merge($this->wafDisabledRules, $ruleIds);
                break;
        }
        $this->wafDisabledRules = array_values($this->wafDisabledRules);
        $this->wafLogonlyRules = array_values($this->wafLogonlyRules);
    }

    /**
     * @param array<string, array<string, mixed>> $data
     */
    public function importDdosFromPost(array $data): void
    {
        if (isset($data['ddos']['challenge_window'])) {
            $this->ddosChallengeWindow = (int) $data['ddos']['challenge_window'];
        }
        if (isset($data['ddos']['sensitivity'])) {
            $this->ddosSensitivity = (int) $data['ddos']['sensitivity'];
        }
    }

    public function getShieldZoneId(): int
    {
        return $this->shieldZoneId;
    }

    public function isWafEnabled(): bool
    {
        return $this->wafEnabled;
    }

    public function getWafExecutionMode(): int
    {
        return $this->wafExecutionMode;
    }

    public function getWafDetectionLevel(): int
    {
        return $this->wafDetectionLevel;
    }

    public function getWafExecutionLevel(): int
    {
        return $this->wafExecutionLevel;
    }

    public function getWafBlockingLevel(): int
    {
        return $this->wafBlockingLevel;
    }

    public function getDdosChallengeWindow(): int
    {
        return $this->ddosChallengeWindow;
    }

    public function getDdosSensitivity(): int
    {
        return $this->ddosSensitivity;
    }

    /**
     * @return string[]
     */
    public function getWafDisabledRules(): array
    {
        return $this->wafDisabledRules;
    }

    /**
     * @return string[]
     */
    public function getWafLogonlyRules(): array
    {
        return $this->wafLogonlyRules;
    }
}
