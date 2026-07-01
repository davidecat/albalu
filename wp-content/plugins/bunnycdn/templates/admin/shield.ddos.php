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

// Don't load directly.
if (!defined('ABSPATH')) {
    exit('-1');
}

/**
 * @var \Bunny\Wordpress\Admin\Container $this
 * @var \Bunny\Wordpress\Api\Pullzone\Shield $shieldConfig
 * @var array<string, string> $submenu
 * @var array<string, string[]> $messages
 */
?>
<nav class="submenu">
    <?php echo $this->renderSubMenuHtml($submenu) ?>
</nav>
<form class="container bg-gradient bn-p-0 bn-pb-5" method="POST" autocomplete="off">
    <section class="bn-section bn-section-hero bn-p-5">
        <div>
            <h1>Bunny Shield</h1>
            <h2>DDoS Mitigation</h2>
            <p>DDoS mitigation system automatically detects and blocks malicious traffic before it reaches your server. It covers both network-level attacks (Layers 3 & 4) and website-level threats (Layer 7), keeping your website secure and online even if under attack.</p>
            <a href="https://docs.bunny.net/shield/ddos" target="_blank" class="bn-link bn-link--external">More Information</a>
        </div>
        <img src="<?php echo esc_attr($this->assetUrl('images/header-shield-ddos.png')) ?>" alt="">
    </section>
    <div class="bn-px-5">
        <?php if (count($messages) > 0): ?>
            <div class="bn-pt-5">
                <?php foreach ($messages as $category => $items): ?>
                    <div class="alert <?php echo esc_attr($category) ?>"><?php echo nl2br(esc_html(join(\PHP_EOL, $items))) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <section class="bn-section bn-px-0 columns-2">
            <div class="bn-block">
                <label for="ddos-challenge-window"><?php echo esc_html__('Valid challenge window', 'bunnycdn') ?></label>
                <select name="ddos[challenge_window]" id="ddos-challenge-window" class="bn-select">
                    <?php foreach (\Bunny\Wordpress\Api\Pullzone\Shield::DDOS_CHALLENGE_WINDOW_OPTIONS as $value => $label): ?>
                        <option value="<?php echo esc_attr($value) ?>" <?php echo $shieldConfig->getDdosChallengeWindow() === $value ? 'selected' : '' ?>><?php echo esc_html__($label, 'bunnycdn') ?></option>
                    <?php endforeach; ?>
                </select>
                <p><?php echo esc_html__('Sets how long a visitor can access your site after passing a challenge. Once the time expires, they must complete a new challenge.') ?></p>
            </div>
            <div class="bn-block">
                <label for="ddos-sensitivity"><?php echo esc_html__('DDoS Sensitivity', 'bunnycdn') ?></label>
                <select name="ddos[sensitivity]" id="ddos-sensitivity" class="bn-select">
                    <?php foreach (\Bunny\Wordpress\Api\Pullzone\Shield::DDOS_SENSITIVITY_OPTIONS as $value => $label): ?>
                        <option value="<?php echo esc_attr($value) ?>" <?php echo $shieldConfig->getDdosSensitivity() === $value ? 'selected' : '' ?>><?php echo esc_html__($label, 'bunnycdn') ?></option>
                    <?php endforeach; ?>
                </select>
                <p><?php echo esc_html__('Controls how aggressively DDoS protection responds to suspicious traffic.') ?></p>
            </div>
        </section>
        <input type="submit" value="Save Settings" class="bunnycdn-button bunnycdn-button--primary bunnycdn-button--lg bn-mt-2 hide-enabled">
    </div>
    <?php wp_nonce_field('bunnycdn-save-shield') ?>
</form>

<div class="bn-chart bn-mt-5 bn-p-0">
    <div class="bn-section__title bn-px-5 bn-pt-5 bn-mb-0">Threat Activity</div>
    <div data-chart="shield-ddos">
        <div class="bn-mt-6 bn-px-5">Loading...</div>
    </div>
</div>
