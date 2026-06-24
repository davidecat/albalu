<?php
/**
 * Class objects instance list.
 *
 * @since   4.9.5
 * @package AdTribes\PFE
 */

use AdTribes\PFE\Classes\License_Manager;
use AdTribes\PFE\Classes\Addon_Manager;
use AdTribes\PFE\Classes\Translation_Migration;
use AdTribes\PFE\Classes\Update_Manager;
use AdTribes\PFE\Classes\WP_Admin;
use AdTribes\PFE\Classes\Feed_Statistics;
use AdTribes\PFE\Classes\Product_Data;
use AdTribes\PFE\Classes\Product_Feed_Attributes;
use AdTribes\PFE\Classes\Product_Data_Manipulation;
use AdTribes\PFE\Classes\Custom_Refresh_Interval;
use AdTribes\PFE\Classes\Rules;
use AdTribes\PFE\Classes\Export_Import_Tools;
use AdTribes\PFE\Classes\Shipping_Data;
use AdTribes\PFE\Classes\Extra_Attributes;
use AdTribes\PFE\Classes\Admin_Pages\Settings_Page;
use AdTribes\PFE\Classes\Facebook_CAPI;

defined( 'ABSPATH' ) || exit;

return array(
    License_Manager::instance(),
    Addon_Manager::instance(),
    Translation_Migration::instance(),
    Update_Manager::instance(),
    WP_Admin::instance(),
    Feed_Statistics::instance(),
    Product_Data::instance(),
    Product_Feed_Attributes::instance(),
    Product_Data_Manipulation::instance(),
    Custom_Refresh_Interval::instance(),
    Rules::instance(),
    Export_Import_Tools::instance(),
    Shipping_Data::instance(),
    Extra_Attributes::instance(),
    Settings_Page::instance(),
    Facebook_CAPI::instance(),
);
