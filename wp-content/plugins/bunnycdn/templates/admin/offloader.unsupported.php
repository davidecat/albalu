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
<div class="container bg-gradient bn-p-0 bn-pb-5">
    <section class="bn-section bn-section-hero bn-p-5">
        <div>
            <h1>Bunny Offloader</h1>
            <h2>Your media files on Bunny Storage</h2>
            <p class="bn-text-200-regular">Bunny Offloader automatically transfers your WordPress media files to Bunny Storage for faster delivery, reduced server load, and scalable global storage. New uploads are seamlessly offloaded and replicated across multiple regions for high performance and reliability.</p>
            <a href="https://docs.bunny.net/integrations/wordpress/offloader" target="_blank" class="bn-link bn-link--external">More Information</a>
        </div>
        <img src="<?php echo esc_attr($this->assetUrl('images/header-offloader.png')) ?>" alt="">
    </section>
    <div class="bn-m-5">
        <div class="alert red">
            <p>Content Offloading is not supported on your WordPress installation.</p>
            <p>We currently do not support installations that make use of customized <a href="https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#moving-wp-content-folder" target="_blank">wp-content</a> or <a href="https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#moving-uploads-folder" target="_blank">uploads</a> folder locations.</p>
        </div>
    </div>
</div>
