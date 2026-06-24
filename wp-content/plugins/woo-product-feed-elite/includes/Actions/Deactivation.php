<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Actions
 */

namespace AdTribes\PFE\Actions;

use AdTribes\PFE\Abstracts\Abstract_Class;

/**
 * Deactivation class.
 *
 * @since 4.9.5
 */
class Deactivation extends Abstract_Class {

    /**
     * Holds boolean value whether the plugin is being activated network wide.
     *
     * @since 4.9.5
     * @access protected
     *
     * @var bool
     */
    protected $network_wide;

    /**
     * Constructor.
     *
     * @since 4.9.5
     * @access public
     *
     * @param bool $network_wide Whether the plugin is being activated network wide.
     */
    public function __construct( $network_wide ) {

        $this->network_wide = $network_wide;
    }


    /**
     * Plugin deactivation.
     *
     * @since 4.9.7
     * @access public
     *
     * @param int $blog_id Blog ID.
     */
    private function _deactivate_plugin( $blog_id ) {
        delete_option( 'adt_pfe_activation_code_triggered' );
        delete_site_option( ADT_PFE_OPTION_INSTALLED_VERSION );

        $this->cleanup_action_scheduler();
        $this->cleanup_options();
        $this->cleanup_transients();
    }

    /**
     * Cleanup action scheduler.
     *
     * @since 4.9.5
     * @access public
     */
    protected function cleanup_action_scheduler() {
        // Clear license check cron job.
        as_unschedule_all_actions( 'adt_pfe_license_check', array(), 'adt_pfe' );

        if ( function_exists( 'as_unschedule_all_actions' ) ) {
            as_unschedule_all_actions( '', array(), ADT_PFE_AS_GENERATE_PRODUCT_FEED_GROUP );
        }
    }

    /**
     * Cleanup options.
     *
     * @since 4.9.5
     * @access public
     */
    protected function cleanup_options() {
        // Clear update data.
        delete_site_option( 'adt_pfe_update_data' );
    }

    /**
     * Cleanup transients.
     *
     * @since 5.0.2
     * @access public
     */
    protected function cleanup_transients() {
        delete_transient( ADT_TRANSIENT_CUSTOM_ATTRIBUTES );
    }

    /**
     * Run plugin deactivation actions.
     *
     * @since 4.9.5
     * @access public
     */
    public function run() {
        // Delete the flag that determines if plugin activation code is triggered.
        global $wpdb;

        // check if it is a multisite network.
        if ( is_multisite() ) {

            // check if the plugin has been activated on the network or on a single site.
            if ( $this->network_wide ) {
                // get ids of all sites.
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    $this->_deactivate_plugin( $blog_id );
                }

                restore_current_blog();
            } else {
                // activated on a single site, in a multi-site.
                $this->_deactivate_plugin( $wpdb->blogid );
            }
        } else {
            // activated on a single site.
            $this->_deactivate_plugin( $wpdb->blogid );
        }
    }
}
