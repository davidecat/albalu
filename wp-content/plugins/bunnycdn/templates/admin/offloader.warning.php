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
 * @var array<string, int> $attachments
 * @var \Bunny\Wordpress\Config\Offloader $config
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
        <?php echo $this->renderPartialFile('cdn-acceleration.alert.php'); ?>
    </div>
    <section class="bn-section statistics bn-section--no-divider">
        <?php echo $this->renderPartialFile('offloader.statistics.php', ['attachments' => $attachments, 'config' => $config, 'attachmentsWithError' => 0]) ?>
    </section>
</div>
