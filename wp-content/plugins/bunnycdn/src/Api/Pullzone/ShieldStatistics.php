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

class ShieldStatistics
{
    /** @var array{string, int}[] */
    public array $chartDdosLogged;
    /** @var array{string, int}[] */
    public array $chartDdosBlocked;
    /** @var array{string, int}[] */
    public array $chartDdosChallenged;
    /** @var array{string, int}[] */
    public array $chartDdosVerified;
    /** @var array{string, int}[] */
    public array $chartWafTriggers;

    /**
     * @param array{string, int}[] $chartDdosLogged
     * @param array{string, int}[] $chartDdosBlocked
     * @param array{string, int}[] $chartDdosChallenged
     * @param array{string, int}[] $chartDdosVerified
     * @param array{string, int}[] $chartWafTriggers
     */
    public function __construct(array $chartDdosLogged, array $chartDdosBlocked, array $chartDdosChallenged, array $chartDdosVerified, array $chartWafTriggers)
    {
        $this->chartDdosLogged = $chartDdosLogged;
        $this->chartDdosBlocked = $chartDdosBlocked;
        $this->chartDdosChallenged = $chartDdosChallenged;
        $this->chartDdosVerified = $chartDdosVerified;
        $this->chartWafTriggers = $chartWafTriggers;
    }
}
