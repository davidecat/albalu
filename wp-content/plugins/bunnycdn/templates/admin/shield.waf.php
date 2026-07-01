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
 * @var array $rules
 * @var array $triggeredRules
 */
?>
<nav class="submenu">
    <?php echo $this->renderSubMenuHtml($submenu) ?>
</nav>
<div class="container bg-gradient bn-p-0 bn-pb-5">
    <section class="bn-section bn-section-hero bn-p-5">
        <div>
            <h1>Bunny Shield</h1>
            <h2>Web Application Firewall</h2>
            <p>Bunny Shield Web Application Firewall is designed to detect, block, and stop malicious requests before they ever reach your server. Protect your website from exploits, SQL Injections, attacks, and other requests intended to harm your website.</p>
            <a href="https://docs.bunny.net/shield/waf" target="_blank" class="bn-link bn-link--external">More Information</a>
        </div>
        <img src="<?php echo esc_attr($this->assetUrl('images/header-shield-waf.png')) ?>" alt="">
    </section>
    <div class="bn-px-5 bn-pt-5">
        <?php foreach ($messages as $category => $items): ?>
            <div class="alert <?php echo esc_attr($category) ?>"><?php echo nl2br(esc_html(join(\PHP_EOL, $items))) ?></div>
        <?php endforeach; ?>
    </div>
    <div class="tabs bn-px-5">
        <ul class="tab-header">
            <li><a href="#waf-settings" id="waf-settings-link"><?php echo esc_html__('Settings') ?></a></li>
            <li><a href="#waf-rules" id="waf-rules-link"><?php echo esc_html__('WAF Rules') ?></a></li>
            <li><a href="#waf-triggered" id="waf-triggered-link"><?php echo esc_html__('Triggered Rules') ?></a></li>
        </ul>
        <div class="tab-panels bn-pt-5">
            <section class="tab-panel" id="waf-settings">
                <form method="POST" class="bg-white" autocomplete="off">
                    <section class="bn-section bn-px-0 bn-pt-0 columns-2">
                        <div class="bn-block">
                            <input type="checkbox" class="bunnycdn-toggle" id="waf-enabled" name="waf[enabled]" value="1" <?php echo $shieldConfig->isWafEnabled() ? 'checked' : '' ?> />
                            <label for="waf-enabled">WAF</label>
                            <p class="bn-mt-2">Our Web Application Firewall Engine is designed to detect, block, and stop malicious requests before they ever reach your origin. Protect your website from exploits, SQL Injections, attacks, and other requests intended to harm your server.</p>
                        </div>
                        <div class="bn-block">
                            <label for="shield-waf-execution-mode">Rule Execution Mode</label>
                            <?php echo $this->renderPartialFile('select-radio.php', [
                                'name' => 'waf[execution_mode]',
                                'value' => $shieldConfig->getWafExecutionMode(),
                                'class' => 'bn-mt-2',
                                'options' => [
                                    0 => __('Log', 'bunnycdn'),
                                    1 => __('Block', 'bunnycdn'),
                                ],
                            ]) ?>
                            <p class="bn-mt-2">Ensure you review your event logs for false positives before toggling Blocking mode.</p>
                        </div>
                    </section>
                    <section class="bn-section bn-px-0">
                        <label><?php echo esc_html__('WAF rule sensitivity') ?></label>
                        <div class="text">
                            <p><?php echo esc_html__('Adjust how strictly the WAF filters incoming traffic. Choose from Low to Extreme sensitivity. From minimal restrictions with fewer false positives to maximum protection for high-risk applications.') ?></p>
                            <p><?php echo esc_html__('Low is recommended for most websites and everyday use, while Extreme is best suited for high-risk apps or environments that require strict security, even if it means more false positives.') ?></p>
                        </div>
                        <div class="section-list-waf-sensitivity">
                            <section data-dependency="ShieldWafEnabled">
                                <div class="text">
                                    <label for="field_ShieldWafDetectionLevel"><?php echo esc_html__('Detection level') ?></label>
                                    <p><?php echo esc_html__('Determines the severity level of rules that will trigger a detection log.') ?></p>
                                </div>
                                <?php echo $this->renderPartialFile('select-radio.php', [
                                    'name' => 'waf[detection_level]',
                                    'value' => $shieldConfig->getWafDetectionLevel(),
                                    'options' => [
                                        1 => __('Low'),
                                        2 => __('Medium'),
                                        3 => __('High'),
                                        4 => __('Extreme'),
                                    ],
                                ]) ?>
                            </section>
                            <section data-dependency="ShieldWafEnabled">
                                <div class="text">
                                    <label for="field_ShieldWafExecutionLevel"><?php echo esc_html__('Execution level') ?></label>
                                    <p><?php echo esc_html__('Determines the severity level of rules that will trigger rule execution and their associated actions.') ?></p>
                                </div>
                                <?php echo $this->renderPartialFile('select-radio.php', [
                                    'name' => 'waf[execution_level]',
                                    'value' => $shieldConfig->getWafExecutionLevel(),
                                    'options' => [
                                        1 => __('Low'),
                                        2 => __('Medium'),
                                        3 => __('High'),
                                        4 => __('Extreme'),
                                    ],
                                ]) ?>
                            </section>
                            <section data-dependency="ShieldWafEnabled">
                                <div class="text">
                                    <label for="field_ShieldWafBlockingLevel"><?php echo esc_html__('Blocking level') ?></label>
                                    <p><?php echo esc_html__('Determines the severity level of rules that will block requests.') ?></p>
                                </div>
                                <?php echo $this->renderPartialFile('select-radio.php', [
                                    'name' => 'waf[blocking_level]',
                                    'value' => $shieldConfig->getWafBlockingLevel(),
                                    'options' => [
                                        1 => __('Low'),
                                        2 => __('Medium'),
                                        3 => __('High'),
                                        4 => __('Extreme'),
                                    ],
                                ]) ?>
                            </section>
                        </div>
                    </section>
                    <input type="submit" value="Save Settings" class="bunnycdn-button bunnycdn-button--primary bunnycdn-button--lg bn-mt-2 hide-enabled">
                    <?php wp_nonce_field('bunnycdn-save-shield') ?>
                </form>
            </section>
            <section class="tab-panel" id="waf-rules">
                <form class="container bg-transparent bn-p-0 bn-pb-5 waf-rule-form" method="POST" autocomplete="off">
                    <div class="massactions">
                        <button name="action" value="enable" class="bunnycdn-button bunnycdn-button--primary">Enable</button>
                        <button name="action" value="disable" class="bunnycdn-button bunnycdn-button--primary">Disable</button>
                        <button name="action" value="logonly" class="bunnycdn-button bunnycdn-button--primary">Log Only</button>
                    </div>

                    <?php foreach ($rules as $ruleSet): ?>
                        <div class="ruleset">
                            <h4><?php echo esc_html($ruleSet['name']) ?></h4>
                            <?php foreach ($ruleSet['ruleGroups'] as $ruleGroup): ?>
                                <?php if (0 === count($ruleGroup['rules'])) {
                                    continue;
                                } ?>
                                <details class="rulegroup">
                                    <summary><span class="name"><?php echo esc_html($ruleGroup['name']) ?></span></summary>
                                    <?php foreach ($ruleGroup['rules'] as $rule): ?>
                                        <div class="rule">
                                            <input type="checkbox" name="waf[rule][]" value="<?php echo esc_attr($rule['ruleId']) ?>" id="waf-rule-<?php echo esc_attr($rule['ruleId']) ?>">
                                            <?php if (in_array($rule['ruleId'], $shieldConfig->getWafDisabledRules(), true)): ?>
                                                <img src="<?php echo esc_attr($this->assetUrl('icon-error.svg')) ?>" alt="disabled" title="rule is disabled">
                                            <?php elseif (in_array($rule['ruleId'], $shieldConfig->getWafLogonlyRules(), true)): ?>
                                                <img src="<?php echo esc_attr($this->assetUrl('icon-logonly.svg')) ?>" alt="log only" title="rule is log only">
                                            <?php else: ?>
                                                <img src="<?php echo esc_attr($this->assetUrl('icon-ok.svg')) ?>" alt="enabled" title="rule is enabled">
                                            <?php endif; ?>
                                            <label for="waf-rule-<?php echo esc_attr($rule['ruleId']) ?>"><?php echo esc_html($rule['description']) ?></label>
                                            <div class="actions">
                                                <?php if (in_array($rule['ruleId'], $shieldConfig->getWafDisabledRules(), true) || in_array($rule['ruleId'], $shieldConfig->getWafLogonlyRules(), true)): ?>
                                                    <button name="waf[rule][]" value="<?php echo esc_attr($rule['ruleId']) ?>" type="submit" form="waf-rule-enable" class="bunnycdn-button bunnycdn-button--secondary bunnycdn-button--sm">Enable</button>
                                                <?php endif; ?>

                                                <?php if (!in_array($rule['ruleId'], $shieldConfig->getWafDisabledRules(), true)): ?>
                                                    <button name="waf[rule][]" value="<?php echo esc_attr($rule['ruleId']) ?>" type="submit" form="waf-rule-disable" class="bunnycdn-button bunnycdn-button--secondary bunnycdn-button--sm">Disable</button>
                                                <?php endif; ?>

                                                <?php if (!in_array($rule['ruleId'], $shieldConfig->getWafLogonlyRules(), true)): ?>
                                                    <button name="waf[rule][]" value="<?php echo esc_attr($rule['ruleId']) ?>" type="submit" form="waf-rule-logonly" class="bunnycdn-button bunnycdn-button--secondary bunnycdn-button--sm">Log Only</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="massactions">
                        <button name="action" value="enable" class="bunnycdn-button bunnycdn-button--primary">Enable</button>
                        <button name="action" value="disable" class="bunnycdn-button bunnycdn-button--primary">Disable</button>
                        <button name="action" value="logonly" class="bunnycdn-button bunnycdn-button--primary">Log Only</button>
                    </div>
                    <?php wp_nonce_field('bunnycdn-save-shield') ?>
                </form>
            </section>
            <section class="tab-panel" id="waf-triggered">
                <div class="bn-chart bn-mb-5 bn-p-0">
                    <div class="bn-section__title bn-px-5 bn-pt-5 bn-mb-0">WAF Triggers</div>
                    <div data-chart="shield-waf">
                        <div class="bn-mt-6 bn-px-5">Loading...</div>
                    </div>
                </div>
                <?php if (0 === count($triggeredRules)): ?>
                <p>No rules have been triggered recently.</p>
                <?php else: ?>
                <ul class="waf-rule-triggered">
                    <?php foreach ($triggeredRules as $rule): ?>
                        <li>
                            <details>
                                <summary>
                                    <span class="name"><?php echo esc_html($rule['description']) ?></span>
                                    <time datetime="<?php echo esc_attr(date(\DATE_RFC3339, $rule['lastTimestamp'])) ?>" class="timestamp"><?php echo esc_html(date('Y-m-d H:i:s T', $rule['lastTimestamp'])) ?></time>
                                </summary>
                                <div class="details">
                                    <div class="urls">
                                        <label for="rule-<?php echo esc_attr($rule['id']) ?>-urls">Top targeted URLs</label>
                                        <ul id="rule-<?php echo esc_attr($rule['id']) ?>-urls">
                                            <?php foreach ($rule['urls'] as $url): ?>
                                                <li><a href="<?php echo esc_attr($url) ?>" rel="noopener nofollow" target="_blank"><?php echo esc_html($url) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="actions">
                                        <button name="waf[triggered_rule][]" value="<?php echo esc_attr($rule['id']) ?>" type="submit" form="waf-rule-enable" class="bunnycdn-button bunnycdn-button--secondary bunnycdn-button--sm">Ignore</button>
                                        <button name="waf[triggered_rule][]" value="<?php echo esc_attr($rule['id']) ?>" type="submit" form="waf-rule-logonly" class="bunnycdn-button bunnycdn-button--secondary bunnycdn-button--sm">Log</button>
                                        <button name="waf[triggered_rule][]" value="<?php echo esc_attr($rule['id']) ?>" type="submit" form="waf-rule-disable" class="bunnycdn-button bunnycdn-button--secondary bunnycdn-button--sm">Disable</button>
                                    </div>
                                </div>
                            </details>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>
<style>
    article.shield:not(:has(:target)) #waf-settings {
        display: flex;
    }

    article.shield:not(:has(:target)) li:has(#waf-settings-link),
    article.shield:has(#waf-settings:target) li:has(#waf-settings-link),
    article.shield:has(#waf-rules:target) li:has(#waf-rules-link),
    article.shield:has(#waf-triggered:target) li:has(#waf-triggered-link) {
        background-color: var(--color-blue-7);

        &:has(a:hover) {
            background-color: var(--color-blue-6);
        }

        a {
            color: var(--color-white);
        }
    }
</style>

<form method="POST" autocomplete="off" id="waf-rule-enable">
    <input type="hidden" name="action" value="enable">
    <?php wp_nonce_field('bunnycdn-save-shield') ?>
</form>

<form method="POST" autocomplete="off" id="waf-rule-disable">
    <input type="hidden" name="action" value="disable">
    <?php wp_nonce_field('bunnycdn-save-shield') ?>
</form>

<form method="POST" autocomplete="off" id="waf-rule-logonly">
    <input type="hidden" name="action" value="logonly">
    <?php wp_nonce_field('bunnycdn-save-shield') ?>
</form>

<script>
    // Stop href="#hashtarget" links jumping around the page
    var hashLinks = document.querySelectorAll("a[href^='#']");
    [].forEach.call(hashLinks, function (link) {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            history.pushState({}, "", link.href);
            history.pushState({}, "", link.href);
            history.back();
        });
    });
</script>
