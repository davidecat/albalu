<?php
/**
 * Integration instance objects.
 *
 * @package AdTribes\PFP
 */

use AdTribes\PFE\Integrations\WPML;
use AdTribes\PFE\Integrations\WCML;
use AdTribes\PFE\Integrations\Aelia_Currency;
use AdTribes\PFE\Integrations\Curcy;
use AdTribes\PFE\Integrations\Polylang;
use AdTribes\PFE\Integrations\All_In_One_SEO_Pack;
use AdTribes\PFE\Integrations\Translate_Press;

defined( 'ABSPATH' ) || exit;

return array_filter(
    array(
        WPML::instance(),
        WCML::instance(),
        Aelia_Currency::instance(),
        Curcy::instance(),
        Polylang::instance(),
        All_In_One_SEO_Pack::instance(),
        Translate_Press::instance(),
    ),
);
