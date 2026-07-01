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
 * @var string $registerUrlSafe
 * @var string $loginUrlSafe
 */
?>
<div class="container no-nav bn-p-0">
    <section class="bn-section bg-gradient-reverse welcome">
        <img src="<?php echo esc_attr($this->assetUrl('images/homepage-welcome.png')) ?>" alt="">
        <h2>Start your <strong>14-Day FREE</strong> Trial</h2>
        <a href="<?php echo $registerUrlSafe ?>" target="_blank" class="bunnycdn-button bunnycdn-button--primary bunnycdn-button--xxl">Create An Account</a>
        <p>Already have an account? <a href="<?php echo $loginUrlSafe ?>">Log in</a>.</p>
    </section>
    <section class="bn-section subtext bn-py-7 bn-px-6">
        <p>Supercharge your website in under <strong>5 minutes</strong>.</p>
    </section>
    <section class="bn-section columns-2">
        <div class="bn-text-center">
            <img src="<?php echo esc_attr($this->assetUrl('images/header-cdn.png')) ?>" alt="">
        </div>
        <div>
            <h3>Bunny CDN</h3>
            <h4>Performance like never before</h4>
            <p>Accelerate your website with lightning-fast global content delivery and intelligent edge caching built to maximize performance, speed, and reliability for visitors around the world.</p>
        </div>
    </section>
    <section class="bn-section columns-2">
        <div class="bn-text-center">
            <img src="<?php echo esc_attr($this->assetUrl('images/header-shield.png')) ?>" alt="">
        </div>
        <div>
            <h3>Bunny Shield</h3>
            <h4>Security simplified</h4>
            <p>Protect your website with advanced Web Application Firewall (WAF) and DDoS mitigation designed to block attacks, reduce malicious traffic, and keep your services online.</p>
        </div>
    </section>
    <section class="bn-section columns-2">
        <div class="bn-text-center">
            <img src="<?php echo esc_attr($this->assetUrl('images/header-offloader.png')) ?>" alt="">
        </div>
        <div>
            <h3>Bunny Offloader</h3>
            <h4>Your media files on Bunny Storage</h4>
            <p>Automatically offload your WordPress media library to Bunny Storage for faster file delivery, lower server usage, and scalable global storage built for high performance.</p>
        </div>
    </section>
    <section class="bn-section columns-2">
        <div class="bn-text-center">
            <img src="<?php echo esc_attr($this->assetUrl('images/header-optimizer.png')) ?>" alt="">
        </div>
        <div>
            <h3>Bunny Optimizer</h3>
            <h4>Faster website. Made simple.</h4>
            <p>Automatically optimize images, CSS, and JavaScript files to improve website performance, reduce page load times, and deliver a faster browsing experience to your visitors.</p>
        </div>
    </section>
    <section class="bn-section columns-2">
        <div class="bn-text-center">
            <img src="<?php echo esc_attr($this->assetUrl('images/header-stream.png')) ?>" alt="">
        </div>
        <div>
            <h3>Bunny Stream</h3>
            <h4>A better way to deliver videos</h4>
            <p>Deliver smooth, buffer-free video playback with automatic video encoding, adaptive streaming, and global delivery directly integrated into your WordPress dashboard.</p>
        </div>
    </section>
    <section class="bn-section columns-2">
        <div class="bn-text-center">
            <img src="<?php echo esc_attr($this->assetUrl('images/header-fonts.png')) ?>" alt="">
        </div>
        <div>
            <h3>Bunny Fonts</h3>
            <h4>Choose Privacy. Use Bunny Fonts.</h4>
            <p>Serve privacy-first web fonts with zero tracking, no logging, and fast global delivery while helping your website stay fully GDPR-compliant.</p>
        </div>
    </section>
</div>
