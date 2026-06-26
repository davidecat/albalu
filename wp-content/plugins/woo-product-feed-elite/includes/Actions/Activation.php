<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Actions
 */

namespace AdTribes\PFE\Actions;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Classes\Addon_Manager;
use AdTribes\PFE\Classes\License_Manager;
use AdTribes\PFE\Factories\Product_Feed;

// Updates.
use AdTribes\PFE\Updates\Version_4_9_7_Update;
use AdTribes\PFE\Updates\Version_5_0_4_Update;
use AdTribes\PFE\Updates\Version_5_0_8_Update;

/**
 * Activation class.
 *
 * @since 4.9.5
 */
class Activation extends Abstract_Class {

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
     * Activate the plugin.
     *
     * @since 4.9.5
     * @access private
     *
     * @param int $blog_id Blog ID.
     */
    private function _activate_plugin( $blog_id ) {
        /**
         * If previous multisite installs site store license options using normal get/add/update_option functions.
         * These stores the option on a per sub-site basis. We need move these options network wide in multisite setup
         * via get/add/update_site_option functions.
         */
        if ( is_multisite() ) {
            $installed_version = get_option( ADT_PFE_OPTION_INSTALLED_VERSION );
            if ( $installed_version ) {
                update_site_option( ADT_PFE_OPTION_INSTALLED_VERSION, $installed_version );
                delete_option( ADT_PFE_OPTION_INSTALLED_VERSION );
            }
        }

        /***************************************************************************
         * Version 4.9.7 Update
         ***************************************************************************
         *
         * This version is the custom post type update.
         */
        ( new Version_4_9_7_Update() )->run();

        /***************************************************************************
         * Version 5.0.4 Update
         ***************************************************************************
         *
         * This version is the option prefix migration update.
         */
        ( new Version_5_0_4_Update() )->run();

        /***************************************************************************
         * Version 5.0.8 Update
         ***************************************************************************
         *
         * Runs the two 5.0.8 upgrade migrations:
         *
         * - Heals stale Elite-group Action Scheduler entries on sites that hit the
         *   pre-5.0.8 Custom -> standard interval double-scheduling bug (issue #377).
         * - Audits the `autoload` flag on plugin-owned options — flips admin/cron-only
         *   rows to `'no'` and license-gated feature toggles to `'yes'` so the
         *   alloptions blob only carries options actually read on every request
         *   (issue #378).
         *
         * Must run BEFORE the installed-version option is bumped, so the
         * version_compare gate in the migration still sees the old version on
         * the upgrade pass.
         */
        ( new Version_5_0_8_Update() )->run();

        // Update current installed plugin version.
        update_site_option( ADT_PFE_OPTION_INSTALLED_VERSION, Helper::get_plugin_version() );

        $this->_register_product_feed_custom_refresh_interval_actions();

        // Register action schedulers for license check and addon check.
        License_Manager::instance()->register_action_scheduler();
        Addon_Manager::instance()->register_action_scheduler();

        // Delete the transient for custom attributes.
        delete_transient( ADT_TRANSIENT_CUSTOM_ATTRIBUTES );

        // Intentionally autoloaded: `App::initialize()` reads this option on every `init`
        // hook to detect first-run / dependency-failed installs, so the row should be
        // resolved from the alloptions blob rather than triggering a per-request SELECT.
        update_option( 'adt_pfe_activation_code_triggered', 'yes' );
    }

    /**
     * Register product feed custom refresh interval actions.
     *
     * @since 5.0.3
     * @access private
     */
    private function _register_product_feed_custom_refresh_interval_actions() {
        if ( ! class_exists( 'AdTribes\PFP\Factories\Product_Feed_Query' ) ) {
            return;
        }

        $product_feeds_query = new \AdTribes\PFP\Factories\Product_Feed_Query(
            array(
                'post_status'    => array( 'publish' ),
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'   => 'adt_refresh_interval',
                        'value' => 'custom',
                    ),
                ),
            ),
            'edit'
        );

        if ( $product_feeds_query->have_posts() ) {
            foreach ( $product_feeds_query->get_posts() as $product_feed ) {
                if ( ! $product_feed instanceof Product_Feed ) {
                    continue;
                }

                $product_feed->register_action();
            }
        }
    }

    /**
     * Run plugin activation actions.
     *
     * @since 4.9.5
     * @access public
     */
    public function run() {
        global $wpdb;

        if ( is_multisite() ) {
            if ( $this->network_wide ) {
                // get ids of all sites.
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    $this->_activate_plugin( $blog_id );
                }
                restore_current_blog();
            } else {
                $this->_activate_plugin( $wpdb->blogid );
            }
            // activated on a single site, in a multi-site.
        } else {
            // activated on a single site.
            $this->_activate_plugin( $wpdb->blogid );
        }
    }
}
