<?php
/**
 * Class objects instance list.
 *
 * @since   5.0.5
 * @package AdTribes\PFP
 */

use AdTribes\PFE\Classes\Feeds\OpenAI_Product_Feed;
use AdTribes\PFE\Classes\Feeds\Facebook_DRM;

defined( 'ABSPATH' ) || exit;

return array(
    OpenAI_Product_Feed::instance(),
    Facebook_DRM::instance(),
);
