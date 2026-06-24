<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes\Admin_Pages
 */

namespace AdTribes\PFE\Classes\Admin_Pages;

use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Factories\Vite_App;

/**
 * Settings_Page class.
 *
 * @since 5.0.1
 */
class Settings_Page extends \AdTribes\PFP\Classes\Admin_Pages\Settings_Page {

    use Singleton_Trait;

    /**
     * Render the tabs.
     *
     * @since 5.0.3
     * @return array
     */
    public function get_tabs() {
        $tabs = parent::get_tabs();

        // Insert into the index 1. After the general tab.
        array_splice(
            $tabs,
            1,
            0,
            array(
                array(
                    'slug'        => 'manage_attributes',
                    'label'       => __( 'Extra fields', 'woo-product-feed-elite' ),
                    'header_text' => __( 'Attribute settings', 'woo-product-feed-elite' ),
                ),
            )
        );

        return $tabs;
    }

    /**
     * Render the tab content.
     *
     * @since 5.0.3
     * @return void
     */
    public function render_tab_content() {
        parent::render_tab_content();

        if ( 'manage_attributes' === $this->active_tab ) {
            Helper::locate_admin_template( 'settings/manage-attributes-tab.php', true );
        }
    }

    /**
     * Get the general settings.
     *
     * @since 5.0.3
     * @return array
     */
    public function get_general_settings() {
        $settings = parent::get_general_settings();

        $settings = array_merge(
            array(
                array(
                    'title'          => __( 'Increase the number of products that will be approved in Google\'s Merchant Center: <br />This option will fix WooCommerce\'s (JSON-LD) structured data bug and add extra structured data elements to your pages.', 'woo-product-feed-elite' ),
                    'type'           => 'checkbox',
                    'id'             => 'adt_structured_data_fix',
                    'read_more_link' => Helper::get_utm_url( 'woocommerce-structured-data-bug', 'pfe', 'manage-settings', 'structureddatabug' ),
                ),
                array(
                    'title' => __( 'Exclude TAX from structured data prices', 'woo-product-feed-elite' ),
                    'type'  => 'checkbox',
                    'id'    => 'adt_structured_vat',
                ),
                array(
                    'title'          => __( 'Enable Product data manipulation feature:', 'woo-product-feed-elite' ),
                    'type'           => 'checkbox',
                    'id'             => 'adt_enable_data_manipulation_support',
                    'read_more_link' => Helper::get_utm_url( 'feature-product-data-manipulation', 'pfe', 'manage-settings', 'productdatamanipulation' ),
                ),
                array(
                    'title'          => __( 'Enable WPML support:', 'woo-product-feed-elite' ),
                    'type'           => 'checkbox',
                    'id'             => 'adt_enable_wpml_support',
                    'read_more_link' => Helper::get_utm_url( 'wpml-support', 'pfe', 'manage-settings', 'wpmlsupport' ),
                ),
                array(
                    'title'          => __( 'Enable Aelia Currency Switcher support:', 'woo-product-feed-elite' ),
                    'type'           => 'checkbox',
                    'id'             => 'adt_enable_aelia_support',
                    'read_more_link' => Helper::get_utm_url( 'aelia-currency-switcher-feature', 'pfe', 'manage-settings', 'aeliasupport' ),
                ),
                array(
                    'title'          => __( 'Enable Curcy Currency Switcher support:', 'woo-product-feed-elite' ),
                    'type'           => 'checkbox',
                    'id'             => 'adt_enable_curcy_support',
                    'read_more_link' => Helper::get_utm_url( 'curcy-currency-switcher-feature', 'pfe', 'manage-settings', 'curcysupport' ),
                ),
                array(
                    'title'          => __( 'Enable Polylang support:', 'woo-product-feed-elite' ),
                    'type'           => 'checkbox',
                    'id'             => 'adt_enable_polylang_support',
                    'read_more_link' => Helper::get_utm_url( 'polylang-support', 'pfe', 'manage-settings', 'polylangsupport' ),
                ),
                array(
                    'title'          => __( 'Enable TranslatePress support:', 'woo-product-feed-elite' ),
                    'type'           => 'checkbox',
                    'id'             => 'adt_enable_translatepress_support',
                    'read_more_link' => Helper::get_utm_url( 'translatepress-support', 'pfe', 'manage-settings', 'translatepresssupport' ),
                ),
            ),
            $settings
        );

        $facebook_capi_settings = array(
            array(
                'title'          => __( 'Enable Facebook Conversion API:', 'woo-product-feed-elite' ),
                'type'           => 'checkbox',
                'id'             => 'adt_enable_facebook_capi',
                'read_more_link' => Helper::get_utm_url( 'facebook-conversion-api', 'pfe', 'manage-settings', 'facebookconversionapi' ),
            ),
            array(
                'title'     => __( 'Insert your Facebook Conversion API token:', 'woo-product-feed-elite' ),
                'type'      => 'textarea',
                'id'        => 'adt_facebook_capi_token',
                'parent_id' => 'adt_enable_facebook_capi',
            ),
        );

        // Place the Facebook Conversion API settings directly after the Facebook Pixel section so related Facebook features sit together.
        $facebook_pixel_anchor = 'adt_facebook_pixel_content_ids';
        $insert_at             = null;
        foreach ( $settings as $index => $setting ) {
            if ( isset( $setting['id'] ) && $facebook_pixel_anchor === $setting['id'] ) {
                $insert_at = $index + 1;
                break;
            }
        }

        if ( null !== $insert_at ) {
            array_splice( $settings, $insert_at, 0, $facebook_capi_settings );
        } else {
            $settings = array_merge( $settings, $facebook_capi_settings );
        }

        /**
         * Filter the general settings arguments.
         *
         * @since 5.0.3
         *
         * @param array $settings Array of settings.
         * @return array
         */
        $settings = apply_filters( 'adt_pfe_general_settings_args', $settings );

        return $settings;
    }

    /**
     * Enqueue scripts for the settings page.
     *
     * @since 5.0.3
     * @return void
     */
    protected function enqueue_scripts() {
        parent::enqueue_scripts();

        $vite = new Vite_App(
            'adt-pfe-settings',
            'src/vanilla/settings/index.ts',
            array( 'jquery', 'wp-i18n' ),
            array(
                'extraAttributesNonce' => wp_create_nonce( 'adt_manage_extra_attributes' ),
            ),
        );

        $vite->enqueue();
    }
}
