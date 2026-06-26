<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Integrations
 */

namespace AdTribes\PFE\Integrations;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Helpers\Product_Feed_Helper;
use AdTribes\PFE\Helpers\Helper;

/**
 * WPML class.
 *
 * @since 4.9.7
 */
class WPML extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Current language.
     *
     * @since 4.9.9
     * @var string
     */
    private $_current_language = '';

    /**
     * Check if 4.9.7 plugin is active.
     *
     * @since 4.9.7
     * @return bool
     */
    public function is_active() {
        return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) && get_option( 'adt_enable_wpml_support' ) === 'yes';
    }

    /**
     * Add WPML data to product feed.
     *
     * @since 4.9.7
     * @param array $data The accumulated filter data.
     * @return array
     */
    public function add_wpml_data( $data ) {
        $data['wpml'] = '';
        return $data;
    }

    /**
     * Show WPML language field.
     *
     * @since 4.9.7
     * @param array $feed Feed data.
     */
    public function show_wpml_language_field( $feed ) {
        global $sitepress;
        $wpml_active_languages  = $sitepress->get_active_languages();
        $wpml_selected_language = apply_filters( 'wpml_default_language', null );

        // If not new feed, get the feed wpml data.
        if ( $feed ) {
            // If feed doesn't have language selected, use current language.
            $feed_wpml_language     = $feed->wpml ?? '';
            $wpml_selected_language = ! empty( $feed_wpml_language ) ? $feed_wpml_language : $wpml_selected_language;
        }

        Helper::locate_admin_template(
            'integrations/wpml/wpml-general-settings-language-field.php',
            true,
            true,
            array(
                'wpml_active_languages'  => $wpml_active_languages,
                'wpml_selected_language' => $wpml_selected_language,
            )
        );
    }

    /**
     * Create product feed wpml data props.
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
        $props['wpml'] = sanitize_text_field( $post_data['WPML'] ?? '' ); // phpcs:disable WordPress.Security.NonceVerification
        return $props;
    }

    /**
     * Edit product feed wpml data props.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $props_to_update The product feed properties to update.
     * @param Product_Feed $product_feed    The product feed.
     * @return array
     */
    public function edit_product_feed_props( $props_to_update, $product_feed ) {
        $props_to_update['wpml'] = sanitize_text_field( wp_unslash( $_POST['WPML'] ?? '' ) ); // phpcs:disable WordPress.Security.NonceVerification
        return $props_to_update;
    }

    /**
     * Clone product feed wpml data before save.
     *
     * @since 4.9.7
     * @access public
     *
     * @param Product_Feed $feed          The product feed.
     * @param Product_Feed $original_feed The original feed.
     */
    public function clone_product_feed_before_save( $feed, $original_feed ) {
        $feed->set_prop( 'wpml', $original_feed->wpml );
    }

    /**
     * Set language before processing the feed.
     *
     * @since 4.9.9
     * @access public
     *
     * @param Product_Feed $feed The feed data.
     */
    public function set_language_before_processing( $feed ) {
        global $sitepress;

        // Get the selected language from the feed.
        $lang_code = $feed->wpml ? $feed->wpml : apply_filters( 'wpml_default_language', null );

        $current_language = $sitepress->get_current_language();

        $sitepress->switch_lang( $lang_code );

        // Set the current language to be used later.
        $this->_current_language = $current_language;
    }

    /**
     * Reset language after processing the feed.
     *
     * @since 4.9.9
     * @access public
     *
     * @param int $feed_id The feed id.
     */
    public function reset_language_after_processing( $feed_id ) {
        global $sitepress;

        // Switch back to the original language.
        $sitepress->switch_lang( $this->_current_language );
    }

    /**
     * Add WPML language to categories args.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array $categories The categories.
     * @param array $args       The get_terms args.
     * @param array $feed_id    The feed id.
     * @return array
     */
    public function get_categories_by_lang( $categories, $args, $feed_id ) {
        // Get the selected language from the feed.
        $lang_code = apply_filters( 'wpml_default_language', null );

        if ( 0 === $feed_id ) {
            $feed_temp = get_option( ADT_OPTION_TEMP_PRODUCT_FEED, array() );
            $lang_code = $feed_temp['WPML'] ?? $lang_code;
        } else {
            $feed = Product_Feed_Helper::get_product_feed( $feed_id );
            if ( Product_Feed_Helper::is_a_product_feed( $feed ) ) {
                $lang_code = $feed->wpml;
            }
        }

        global $sitepress;
        $current_language = $sitepress->get_current_language();
        $sitepress->switch_lang( $lang_code );

        $categories = get_terms( $args );

        $sitepress->switch_lang( $current_language );

        return $categories;
    }

    /**
     * Convert cart link.
     * Seems like there's an issue with built in wc_get_page_permalink & wc_get_cart_url that we used to get the cart link.
     * In some cases, it returns the main language url instead of the current language url, despite the fact that the switch_lang has been used.
     * So, we're using the translate_object_id filter to get the correct url.
     *
     * @since 5.0.2
     * @param array $product_data The product data.
     * @return array
     */
    public function convert_cart_link( $product_data ) {
        if ( empty( $product_data ) ) {
            return $product_data;
        }

        $product_data['add_to_cart_link'] = get_permalink( apply_filters( 'translate_object_id', wc_get_page_id( 'shop' ), 'page', true ) ) . '?add-to-cart=' . $product_data['id'];
        $product_data['cart_link']        = get_permalink( apply_filters( 'translate_object_id', wc_get_page_id( 'cart' ), 'page', true ) ) . '?add-to-cart=' . $product_data['id'];

        return $product_data;
    }

    /**
     * Get feed channel link.
     *
     * @since 5.0.2
     * @param string       $link The link.
     * @param Product_Feed $feed The feed data.
     * @return string
     */
    public function get_feed_channel_link( $link, $feed ) {
        // Get the selected language from the feed.
        $lang_code = $feed->wpml ? $feed->wpml : apply_filters( 'wpml_default_language', null );

        if ( empty( $lang_code ) ) {
            return $link;
        }

        global $sitepress;
        $link = $sitepress->language_url( $lang_code );

        return $link;
    }

    /**
     * Get original post id of translated product.
     * Because WPML uses the different id for translated products, we need to get the original post id.
     *
     * @since 5.0.2
     * @param int $post_id The post id.
     * @return int
     */
    public function get_facebook_pixel_post_id( $post_id ) {
        // Get original post id.
        $original_post_id = apply_filters( 'wpml_original_element_id', null, $post_id );

        if ( empty( $original_post_id ) ) {
            return $post_id;
        }

        return $original_post_id;
    }

    /**
     * Run 4.9.7 integration hooks.
     *
     * @since 4.9.7
     */
    public function run() {
        if ( ! $this->is_active() ) {
            return;
        }

        // Add extra data to product feed.
        add_filter( 'adt_product_feed_data', array( $this, 'add_wpml_data' ) );

        // Show WPML language field.
        add_action( 'adt_general_feed_settings_before_country_field', array( $this, 'show_wpml_language_field' ), 20, 1 );

        // Add WPML data to product feed on creation.
        add_filter( 'adt_create_product_feed_props', array( $this, 'create_product_feed_props' ), 20, 3 );

        // Add WPML data to product feed.
        add_filter( 'adt_edit_feed_general_tab_props', array( $this, 'edit_product_feed_props' ), 20, 2 );

        // Clone WPML data.
        add_action( 'adt_clone_product_feed_before_save', array( $this, 'clone_product_feed_before_save' ), 20, 2 );

        // Set language before processing the feed.
        add_filter( 'woosea_before_get_products', array( $this, 'set_language_before_processing' ), 20, 1 );

        // Reset language after processing the feed.
        add_filter( 'adt_after_product_feed_generation', array( $this, 'reset_language_after_processing' ), 20, 1 );

        // Convert cart link.
        add_filter( 'adt_get_product_data', array( $this, 'convert_cart_link' ), 20, 1 );

        // Add WPML language to categories dropdown.
        add_filter( 'adt_pfp_get_categories_dropdown', array( $this, 'get_categories_by_lang' ), 20, 3 );
        add_filter( 'adt_product_feed_hierarchical_categories_mapping', array( $this, 'get_categories_by_lang' ), 20, 3 );
        add_filter( 'adt_product_feed_hierarchical_categories_children_mapping', array( $this, 'get_categories_by_lang' ), 20, 3 );

        // Add WPML language to feed channel link.
        add_filter( 'adt_pfp_google_shopping_feed_channel_link', array( $this, 'get_feed_channel_link' ), 20, 2 );

        // Facebook pixel post id.
        add_filter( 'adt_facebook_pixel_post_id', array( $this, 'get_facebook_pixel_post_id' ), 20, 1 );
    }
}
