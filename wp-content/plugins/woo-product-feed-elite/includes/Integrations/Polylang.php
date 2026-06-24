<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Integrations
 */

namespace AdTribes\PFE\Integrations;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Helpers\Helper;

/**
 * Polylang class.
 *
 * @since 4.9.7
 */
class Polylang extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Check if Polylang plugin is active.
     *
     * @since 4.9.7
     * @return bool
     */
    public function is_active() {
        return ( is_plugin_active( 'polylang/polylang.php' ) || is_plugin_active( 'polylang-pro/polylang.php' ) ) && get_option( 'adt_enable_polylang_support' ) === 'yes';
    }

    /**
     * Add Polylang data to product feed.
     *
     * @since 4.9.7
     * @param array $data The accumulated filter data.
     * @return array
     */
    public function add_polylang_data( $data ) {
        $data['polylang'] = '';
        return $data;
    }

    /**
     * Show polylang language field.
     *
     * @since 4.9.7
     * @param array $feed Feed data.
     */
    public function show_polylang_language_field( $feed ) {
        if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_default_language' ) ) {
            return;
        }

        $polylang_selected_language = pll_default_language();
        $polylang_active_languages  = pll_languages_list( array( 'fields' => '' ) );

        // If not new feed, get the feed wpml data.
        if ( $feed ) {
            // If feed doesn't have language selected, use current language.
            $feed_polylang_language     = $feed->polylang ?? '';
            $polylang_selected_language = ! empty( $feed_polylang_language ) ? $feed_polylang_language : $polylang_selected_language;
        }

        Helper::locate_admin_template(
            'integrations/polylang/polylang-general-settings-language-field.php',
            true,
            true,
            array(
                'polylang_selected_language' => $polylang_selected_language,
                'polylang_active_languages'  => $polylang_active_languages,
            )
        );
    }

    /**
     * Create product feed polylang data props.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $props     The product feed properties.
     * @param Product_Feed $feed      The feed data.
     * @param array        $post_data The post data from the legacy code base.
     * @return array
     */
    public function create_product_feed_props( $props, $feed, $post_data ) {
        $props['polylang'] = sanitize_text_field( $post_data['polylang'] ?? '' ); // phpcs:disable WordPress.Security.NonceVerification
        return $props;
    }

    /**
     * Edit product feed polylang data props.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $props_to_update The product feed properties to update.
     * @param Product_Feed $product_feed    The product feed.
     * @return array
     */
    public function edit_product_feed_props( $props_to_update, $product_feed ) {
        $props_to_update['polylang'] = sanitize_text_field( wp_unslash( $_POST['polylang'] ?? '' ) ); // phpcs:disable WordPress.Security.NonceVerification
        return $props_to_update;
    }

    /**
     * Clone product feed polylang data before save.
     *
     * @since 4.9.7
     * @access public
     *
     * @param Product_Feed $feed          The product feed.
     * @param Product_Feed $original_feed The original feed.
     */
    public function clone_product_feed_before_save( $feed, $original_feed ) {
        $feed->set_prop( 'polylang', $original_feed->polylang );
    }

    /**
     * Set suppress filters to false.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $args The query args.
     * @param Product_Feed $feed The feed data.
     * @return array
     */
    public function set_feed_query_args_language( $args, $feed ) {
        // Get the selected language from the feed.
        $args['lang'] = $feed->polylang ? $feed->polylang : pll_default_language();
        return $args;
    }

    /**
     * Run Polylang integration hooks.
     *
     * @since 4.9.7
     */
    public function run() {
        if ( ! $this->is_active() ) {
            return;
        }

        // Add extra data to product feed.
        add_filter( 'adt_product_feed_data', array( $this, 'add_polylang_data' ) );

        // Show Polylang language field.
        add_action( 'adt_general_feed_settings_before_country_field', array( $this, 'show_polylang_language_field' ), 70, 1 );

        // Add Polylang data to product feed on creation.
        add_filter( 'adt_create_product_feed_props', array( $this, 'create_product_feed_props' ), 70, 3 );

        // Add Polylang data to product feed.
        add_filter( 'adt_edit_feed_general_tab_props', array( $this, 'edit_product_feed_props' ), 70, 2 );

        // Clone Polylang data.
        add_action( 'adt_clone_product_feed_before_save', array( $this, 'clone_product_feed_before_save' ), 70, 2 );

        // Set query args language.
        add_filter( 'adt_product_feed_get_products_query_args', array( $this, 'set_feed_query_args_language' ), 70, 2 );
    }
}
