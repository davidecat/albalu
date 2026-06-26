<?php

/**
 * Plugin Name: Product Feed ELITE for WooCommerce
 * Version:     5.0.8
 * Plugin URI:  https://adtribes.io/support/?utm_source=wpadmin&utm_medium=plugin&utm_campaign=woosea_product_feed_pro
 * Description: Configure and maintain your WooCommerce product feeds for Google Shopping, Facebook, Remarketing, Bing, Yandex, Comparison shopping websites and over a 100 channels more.
 * Author:      AdTribes.io
 * Plugin URI:  https://adtribes.io/pro-vs-elite/
 * Author URI:  https://adtribes.io
 * Developer:   AdTribes
 * Requires at least: 5.9
 * Tested up to: 7.0
 *
 * Text Domain: woo-product-feed-elite
 * Domain Path: /languages
 *
 * WC requires at least: 4.4
 * WC tested up to: 10.8.1
 *
 * Product Feed ELITE for WooCommerce is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define plugin constants.
 */
define( 'WOOCOMMERCESEA_ELITE_PLUGIN_VERSION', '5.0.8' );
define( 'WOOCOMMERCESEA_ELITE_REQUIRED_PFP_PLUGIN_VERSION', '13.5.5' );
define( 'WOOCOMMERCESEA_ELITE_PLUGIN_NAME', 'woocommerce-product-feed-elite' );
define( 'WOOCOMMERCESEA_ELITE_PLUGIN_NAME_SHORT', 'woo-product-feed-elite' );

if ( ! defined( 'ADT_PFE_PLUGIN_FILE' ) ) {
    define( 'ADT_PFE_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'ADT_PFE_PLUGIN_DIR_PATH' ) ) {
    define( 'ADT_PFE_PLUGIN_DIR_PATH', plugin_dir_path( ADT_PFE_PLUGIN_FILE ) );
}

if ( ! defined( 'ADT_PFE_BASENAME' ) ) {
    define( 'ADT_PFE_BASENAME', plugin_basename( ADT_PFE_PLUGIN_FILE ) );
}

if ( ! defined( 'ADT_PFE_PLUGIN_URL' ) ) {
    define( 'ADT_PFE_PLUGIN_URL', plugin_dir_url( ADT_PFE_PLUGIN_FILE ) );
}

// Define the url to the plugin images.
if ( ! defined( 'ADT_PFE_IMAGES_URL' ) ) {
    define( 'ADT_PFE_IMAGES_URL', ADT_PFE_PLUGIN_URL . 'static/images/' );
}

// Define the url to the plugin js.
if ( ! defined( 'ADT_PFE_JS_URL' ) ) {
    define( 'ADT_PFE_JS_URL', ADT_PFE_PLUGIN_URL . 'static/js/' );
}

// Define the url to the plugin css.
if ( ! defined( 'ADT_PFE_CSS_URL' ) ) {
    define( 'ADT_PFE_CSS_URL', ADT_PFE_PLUGIN_URL . 'static/css/' );
}

// SLMW.
if ( ! defined( 'ADT_PFE_SLMW_SERVER_URL' ) ) {
    define( 'ADT_PFE_SLMW_SERVER_URL', 'https://adtribes.io' );
}

// Transient keys.
if ( ! defined( 'ADT_TRANSIENT_CUSTOM_ATTRIBUTES' ) ) {
    define( 'ADT_TRANSIENT_CUSTOM_ATTRIBUTES', 'adt_transient_custom_attributes' );
}

// Define the Action Scheduler hook for generating product feeds.
if ( ! defined( 'ADT_PFP_AS_GENERATE_PRODUCT_FEED' ) ) {
    define( 'ADT_PFP_AS_GENERATE_PRODUCT_FEED', 'adt_pfp_as_generate_product_feed' );
}

if ( ! defined( 'ADT_PFE_AS_GENERATE_PRODUCT_FEED_GROUP' ) ) {
    define( 'ADT_PFE_AS_GENERATE_PRODUCT_FEED_GROUP', 'adt_pfe_as_generate_product_feed_group' );
}

define( 'ADT_PFE_SLMW_SOFTWARE_KEY', 'PFE' );
define( 'ADT_PFE_STATIC_PING_FILE', ADT_PFE_SLMW_SERVER_URL . '/PFE.json' );
define( 'ADT_PFE_LICENSE_KEY', 'adt_pfe_license_key' );
define( 'ADT_PFE_ACTIVATION_EMAIL', 'adt_pfe_activation_email' );
define( 'ADT_LICENSE_DATA', 'adt_license_data' );
define( 'ADT_PFE_UPDATE_DATA', 'adt_pfe_update_data' );
define( 'ADT_PFE_LAST_LICENSE_CHECK', 'adt_pfe_last_license_check' );
define( 'ADT_PFE_RETRIEVING_UPDATE_DATA', 'adt_pfe_retrieving_update_data' );
define( 'ADT_PFE_SLMW_ACTIVATE_NOTICE_DISMISSED', 'adt_pfe_slmw_activate_notice_dismissed' );

// Addon management.
define( 'ADT_PFE_ADDON_CHECK_DATA', 'adt_pfe_addon_check_data' );
define( 'ADT_PFE_INSTALLED_ADDONS', 'adt_pfe_installed_addons' );
define( 'ADT_PFE_SHOW_PRODUCT_FEED_TRANSLATION_ADDON_NOTICE', 'adt_show_product_feed_translation_addon_notice' );

// Define the option name for the installed version.
define( 'ADT_PFE_OPTION_INSTALLED_VERSION', 'woocommercesea_elite_option_installed_version' );

/***************************************************************************
 * Loads plugin text domain.
 * **************************************************************************
 *
 * Loads the plugin text domain for translation.
 */
function woosea_elite_textdomain() {

    load_plugin_textdomain(
        'woo-product-feed-elite',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
}

add_action( 'init', 'woosea_elite_textdomain' );

/***************************************************************************
 * Loads the plugin.
 ***************************************************************************
 *
 * Load the plugin if all checks passed.
 */

/**
 * Our bootstrap class instance.
 *
 * @var AdTribes\PFE\App $app
 */
$app = require_once 'bootstrap/app.php';

$app->boot();
