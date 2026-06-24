<?php
/**
 * Uninstall script for AdTribes Product Feed Plugin Elite.
 *
 * @package AdTribes\PFE
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

wp_clear_scheduled_hook( 'woosea_cron_hook' );

// Clear update data.
delete_site_option( 'adt_pfe_update_data' );

// Clear cron jobs.
as_unschedule_all_actions( 'adt_pfe_license_check', array(), 'adt_pfe' );

delete_option( 'woosea_license_notification_closed' );

// Delete ELITE-specific legacy options (unprefixed).
delete_option( 'structured_data_fix' );
delete_option( 'structured_vat' );
delete_option( 'add_manipulation_support' );
delete_option( 'add_wpml_support' );
delete_option( 'add_aelia_support' );
delete_option( 'add_curcy_support' );
delete_option( 'add_polylang_support' );
delete_option( 'add_translatepress_support' );
delete_option( 'add_facebook_capi' );
delete_option( 'woosea_facebook_capi_token' );

// Delete ELITE-specific new prefixed options.
delete_option( 'adt_structured_data_fix' );
delete_option( 'adt_structured_vat' );
delete_option( 'adt_enable_data_manipulation_support' );
delete_option( 'adt_enable_wpml_support' );
delete_option( 'adt_enable_aelia_support' );
delete_option( 'adt_enable_curcy_support' );
delete_option( 'adt_enable_polylang_support' );
delete_option( 'adt_enable_translatepress_support' );
delete_option( 'adt_enable_facebook_capi' );
delete_option( 'adt_facebook_capi_token' );

// Also clean up site-wide options for multisite.
delete_site_option( 'adt_structured_data_fix' );
delete_site_option( 'adt_structured_vat' );
