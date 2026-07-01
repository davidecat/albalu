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
 */
?>
<nav class="submenu">
    <?php echo $this->renderSubMenuHtml([
        'shield/waf' => 'WAF',
        'shield/ddos' => 'DDoS',
    ]) ?>
</nav>
<div class="container bg-gradient bn-p-0 bn-pb-5">
    <section class="bn-section bn-section-hero bn-p-5">
        <div>
            <h1>Bunny Shield</h1>
            <h2>Security simplified</h2>
            <p>Bunny Shield protects your website from attacks, malicious traffic, and service disruption. With built-in Web Application Firewall (WAF) and DDoS mitigation, Bunny Shield helps keep your website secure, available, and performing reliably under load.</p>
            <a href="https://bunny.net/shield/" target="_blank" class="bn-link bn-link--external">More Information</a>
        </div>
        <img src="<?php echo esc_attr($this->assetUrl('images/header-shield.png')) ?>" alt="">
    </section>
    <section class="bn-section bn-px-0 bn-section--no-divider bn-m-5">
        <p class="bn-text-200-regular">To enable Bunny Shield, you must first enable Bunny DNS with CDN Proxy in your bunny.net account.</p>
        <a class="bunnycdn-button bunnycdn-button--primary bn-mt-4" href="https://docs.bunny.net/integrations/wordpress/dns" target="_blank">Enable Bunny DNS</a>
    </section>
</div>
