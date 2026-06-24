<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Classes\License_Manager;
use AdTribes\PFE\Updates\Version_4_9_7_Update;

/**
 * General wp-admin related functionalities and/or overrides.
 *
 * @since 4.9.6
 */
class WP_Admin extends \AdTribes\PFP\Classes\WP_Admin {

    /**
     * Holds the class instance object.
     *
     * @since 4.9.7
     * @access protected
     *
     * @var WP_Admin $instance The class instance object.
     */
    protected static $instance;

    /**
     * Enqueue admin scripts.
     *
     * @since 4.9.6
     * @access public
     *
     * @param string $hook The current admin page.
     */
    public function admin_enqueue_scripts( $hook ) {
        // Enqueue scripts and styles only on the plugin pages.
        if ( Helper::is_plugin_page() ) {
            $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $step   = isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

            wp_enqueue_style( 'woosea_elite_admin-css', ADT_PFE_CSS_URL . 'woosea_admin.css', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION );

            // JS for adding input field validation.
            wp_enqueue_script( 'woosea_elite_validation-js', ADT_PFE_JS_URL . 'woosea_validation.js', '', WOOCOMMERCESEA_PLUGIN_VERSION, true );

            // Field manipulation script.
            wp_enqueue_script( 'woosea_elite_rules-js', ADT_PFE_JS_URL . 'woosea_rules.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );

            // JS for getting channels.
            wp_enqueue_script( 'woosea_elite_channel-js', ADT_PFE_JS_URL . 'woosea_channel.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
        }
    }

    /**
     * Add links to the plugin page.
     *
     * @since 4.9.8
     * @access public
     *
     * @param array $links The links to add.
     * @return array The links to add.
     */
    public function elite_plugin_action_links( $links ) {
        if ( ! is_multisite() ) {
            $plugin_links[] = '<a href="' . admin_url( 'admin.php?page=adt_license_settings_page' ) . '">' . __( 'License', 'woo-product-feed-elite' ) . '</a>';
        } elseif ( is_multisite() && is_super_admin() ) {
            $plugin_links[] = '<a href="' . network_admin_url( 'admin.php?page=adt_license_settings_page' ) . '">' . __( 'License', 'woo-product-feed-elite' ) . '</a>';
        }
        $plugin_links[] = '<a href="https://adtribes.io/support/?utm_source=pfe&utm_medium=pluginpage&utm_campaign=pluginlinksupport" target="_blank">' . __( 'Support', 'woo-product-feed-elite' ) . '</a>';
        $plugin_links[] = '<a href="https://adtribes.io/knowledge-base/?utm_source=pfe&utm_medium=pluginpage&utm_campaign=pluginlinktutorials" target="_blank">' . __( 'Tutorials', 'woo-product-feed-elite' ) . '</a>';
        $plugin_links[] = '<a href="' . admin_url( 'admin.php?page=woosea_manage_settings' ) . '">' . __( 'Settings', 'woo-product-feed-elite' ) . '</a>';

        $license_active = License_Manager::is_license_activated();

        if ( ! $license_active ) {
            $plugin_links[] = '<a href="https://adtribes.io/pro-vs-elite/?utm_source=pfe&utm_medium=pluginpage&utm_campaign=pluginlinkpremiumsupport" target="_blank">' . __( 'Premium Support', 'woo-product-feed-elite' ) . '</a>';
            $plugin_links[] = '<a href="https://adtribes.io/pro-vs-elite/?utm_source=pfe&utm_medium=pluginpage&utm_campaign=pluginlinkupgradetoelite" target="_blank" style="color:green;"><b>' . __( 'Upgrade to Elite', 'woo-product-feed-elite' ) . '</b></a>';
        }

        // Add the links to the list of links already there.
        foreach ( $plugin_links as $link ) {
            if ( is_array( $links ) ) {
                array_unshift( $links, $link );
            }
        }

        return $links;
    }

    /**
     * Change the plugin page title.
     *
     * @since 4.9.7
     * @access public
     *
     * @param string $page_title The page title.
     *
     * @return string
     */
    public function update_page_title( $page_title ) {
        return __( 'Product Feed Elite for WooCommerce', 'woo-product-feed-elite' );
    }

    /**
     * Migrate product feed extra data.
     *
     * @since 4.9.6
     * @access public
     */
    public function migrate_product_feed_extra_data() {
        // Run the migration.
        ( new Version_4_9_7_Update( true ) )->run();
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 4.9.6
     */
    public function run() {
        if ( ! is_admin() ) {
            return;
        }

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10 );

        // Plugin action links.
        add_filter( 'plugin_action_links_' . ADT_PFE_BASENAME, array( $this, 'elite_plugin_action_links' ), 10, 1 );

        // Update plugin page title to Elite.
        add_filter( 'adt_admin_plugin_page_title', array( $this, 'update_page_title' ) );

        // Hide upsell notices.
        add_filter( 'adt_pfp_show_notice_bar_lite', '__return_false' );
        add_filter( 'adt_pfp_show_logo_upgrade_button', '__return_false' );
        add_filter( 'adt_pfp_show_get_elite_notice', '__return_false' );
        add_filter( 'adt_show_pfp_upgrade-to-elite_page', '__return_false' );

        // Add action to migrate extra data.
        add_action( 'adt_after_migrate_to_custom_post_type', array( $this, 'migrate_product_feed_extra_data' ) );
    }
}
