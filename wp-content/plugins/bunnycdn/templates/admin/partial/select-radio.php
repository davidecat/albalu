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
 * @var \Bunny\Wordpress\Container $this
 * @var string $name
 * @var int $value
 * @var array<int, string> $options
 * @var string $class
 */
?>
<div class="<?php echo esc_attr($class) ?>">
    <div class="select-radio">
        <?php foreach ($options as $optionValue => $optionLabel): ?>
            <div class="item">
                <input type="radio" name="<?php echo esc_attr($name) ?>" value="<?php echo esc_attr($optionValue) ?>" id="field_<?php echo esc_attr($name) ?>_<?php echo esc_attr($optionValue) ?>" <?php echo $optionValue === $value ? 'checked' : '' ?>>
                <label for="field_<?php echo esc_attr($name) ?>_<?php echo esc_attr($optionValue) ?>"><?php echo esc_html($optionLabel) ?></label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
