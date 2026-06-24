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
 * WCML class.
 *
 * @since 4.9.7
 */
class WCML extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Check if WCML plugin is active.
     *
     * @since 4.9.7
     * @return bool
     */
    public function is_active() {
        return is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) && get_option( 'adt_enable_wpml_support' ) === 'yes';
    }

    /**
     * Add WCML data to product feed.
     *
     * @since 4.9.7
     * @param array $data The accumulated filter data.
     * @return array
     */
    public function add_wcml_data( $data ) {
        $data['wcml'] = '';
        return $data;
    }

    /**
     * Add WCML extra attributes.
     *
     * @since 4.9.7
     * @param array $attributes The attributes array.
     * @return array
     */
    public function add_wcml_extra_attributes( $attributes ) {
        $wcml_attributes = array(
            'non_geo_wcml_price'                => 'Non GEO WCML price',
            'non_geo_wcml_price_net_price'      => 'Non GEO WCML price excl. VAT',
            'non_geo_wcml_sale_price'           => 'Non GEO WCML sale price',
            'non_geo_wcml_sale_price_net_price' => 'Non GEO WCML sale price excl. VAT',
        );

        // Add WCML attributes under the 'WCML Attributes' section.
        $attributes['WCML Attributes'] = ! empty( $attributes['WCML Attributes'] ) ?
            array_merge( $attributes['WCML Attributes'], $wcml_attributes )
            : $wcml_attributes;

        return $attributes;
    }

    /**
     * Show WCML currency field.
     *
     * @since 4.9.7
     * @param array $feed Feed data.
     */
    public function show_wcml_currency_field( $feed ) {
        global $woocommerce_wpml;
        $wcml_selected_currency = $this->get_base_currency();
        $wcml_currencies        = isset( $woocommerce_wpml->multi_currency ) && wcml_is_multi_currency_on()
            ? $woocommerce_wpml->multi_currency->get_currency_codes()
            : array();

        // If not new feed, get the feed wpml data.
        if ( $feed ) {
            // If feed doesn't have currency selected, use default currency.
            $feed_wcml_currency     = $feed->wcml ?? '';
            $wcml_selected_currency = ! empty( $feed_wcml_currency ) ? $feed_wcml_currency : $wcml_selected_currency;
        }
        Helper::locate_admin_template(
            'integrations/wcml/wcml-general-settings-currency-field.php',
            true,
            true,
            array(
                'wcml_selected_currency' => $wcml_selected_currency,
                'wcml_currencies'        => $wcml_currencies,
            )
        );
    }

    /**
     * Create product feed wcml data props.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $props     The product feed properties.
     * @param Product_Feed $feed      The feed data.
     * @param array        $post_data The post data.
     * @return array
     */
    public function create_product_feed_props( $props, $feed, $post_data ) {
        $props['wcml'] = sanitize_text_field( $post_data['WCML'] ?? '' ); // phpcs:disable WordPress.Security.NonceVerification
        return $props;
    }

    /**
     * Edit product feed wcml data props.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $props_to_update The product feed properties to update.
     * @param Product_Feed $product_feed    The product feed.
     * @return array
     */
    public function edit_product_feed_props( $props_to_update, $product_feed ) {
        $props_to_update['wcml'] = sanitize_text_field( wp_unslash( $_POST['WCML'] ?? '' ) ); // phpcs:disable WordPress.Security.NonceVerification
        return $props_to_update;
    }

    /**
     * Clone product feed wcml data before save.
     *
     * @since 4.9.7
     * @access public
     *
     * @param Product_Feed $feed          The product feed.
     * @param Product_Feed $original_feed The original feed.
     */
    public function clone_product_feed_before_save( $feed, $original_feed ) {
        $feed->set_prop( 'wcml', $original_feed->wcml );
    }


    /**
     * Convert WPML shipping cost.
     *
     * @since 4.9.7
     * @access public
     *
     * @param float        $shipping_cost   The shipping cost.
     * @param object       $rate            The shipping rate object.
     * @param Product_Feed $feed            The feed data.
     * @return array
     */
    public function convert_shipping_cost( $shipping_cost, $rate, $feed ) {
        $wcml_currency = $feed->wcml ?? '';

        if ( empty( $wcml_currency ) || $this->get_base_currency() === $wcml_currency ) {
            return $shipping_cost;
        }

        $shipping_method = \WC_Shipping_Zones::get_shipping_method( $rate->get_instance_id() );

        // Get shipping cost in the selected currency.
        // The shipping cost is stored in the shipping method instance settings if it's a custom shipping method.
        // If shipping cost is set manually for the currency, use it.
        if ( isset( $shipping_method->instance_settings[ 'cost_' . $wcml_currency ] ) ) {
            $shipping_cost = $shipping_method->instance_settings[ 'cost_' . $wcml_currency ];
        } else {
            // If shipping cost is not set manually, convert the default shipping cost.
            $shipping_cost = apply_filters( 'wcml_raw_price_amount', (float) $shipping_cost, $wcml_currency );
        }

        return $shipping_cost;
    }

    /**
     * Shipping cost currency.
     *
     * @since 4.9.7
     * @access public
     *
     * @param string       $currency      The currency.
     * @param Product_Feed $feed          The feed data.
     * @return array
     */
    public function shipping_cost_currency( $currency, $feed ) {
        $wcml_currency = $feed->wcml ?? '';

        if ( empty( $wcml_currency ) || $this->get_base_currency() === $wcml_currency ) {
            return $currency;
        }

        return $wcml_currency;
    }

    /**
     * Override product feed price and currency data.
     *
     * @since 4.9.7
     * @access public
     *
     * @param string     $product_data The product data.
     * @param array      $feed         The feed data.
     * @param WC_Product $product      The product data.
     * @return string
     */
    public function wcml_product_data_currency( $product_data, $feed, $product ) {
        if ( empty( $product_data ) ) {
            return $product_data;
        }

        global $woocommerce_wpml;
        if ( null === $woocommerce_wpml->multi_currency ) {
            return $product_data;
        }

        $wcml_currency = $feed->wcml ?? '';

        if ( empty( $wcml_currency ) || $this->get_base_currency() === $wcml_currency ) {
            return $product_data;
        }

        $product_data['currency'] = $wcml_currency;

        // WCML manual prices have been entered.
        $wcml_custom_prices = $woocommerce_wpml->multi_currency->custom_prices->get_product_custom_prices( $product->get_id(), $wcml_currency );
        $converted_prices   = array(
            'price'         => $product->get_price( 'edit' ),
            'regular_price' => $product->get_regular_price( 'edit' ),
        );

        // Add sale price to prices to convert if product is on sale.
        if ( $product->is_on_sale() ) {
            $converted_prices['sale_price'] = $product->get_sale_price( 'edit' );
        }

        /**
         * We want to also localize the prices and get new converted prices excluding and including tax.
         * Then populate the net and forced prices to the product data.
         * Also convert rounded prices.
         *
         * Note:
         * Net price is the price excluding tax.
         * Forced price is the price including tax.
         */
        foreach ( $converted_prices as $key => $price ) {
            // Use WCML custom prices if they exist.
            // Otherwise, convert the prices.
            $price = isset( $wcml_custom_prices[ '_' . $key ] )
                ? $wcml_custom_prices[ '_' . $key ]
                : apply_filters( 'wcml_raw_price_amount', (float) $price, $wcml_currency );

            $product_data[ $key ]             = Product_Feed_Helper::get_price_including_tax( $price, array(), $feed, $product );
            $product_data[ $key . '_forced' ] = Product_Feed_Helper::get_price_including_tax( $price, array(), $feed, $product );
            $product_data[ 'net_' . $key ]    = Product_Feed_Helper::get_price_excluding_tax( $price, array(), $feed, $product );

            // Round prices.
            $product_data[ 'rounded_' . $key ]          = round( (float) $product_data[ $key ], 0 );
            $product_data[ $key . '_forced_rounded' ]   = round( (float) $product_data[ $key . '_forced' ], 0 );
            $product_data[ 'net_' . $key . '_rounded' ] = round( (float) $product_data[ 'net_' . $key ], 0 );
        }

        /**
         * Non GEO WCML price attributes.
         * These are the custom WCML attribute.
         */
        $base_tax_rates = \WC_Tax::get_base_tax_rates( $product->get_tax_class() );
        $tax_rate       = isset( $base_tax_rates[0]['rate'] ) ? $base_tax_rates[0]['rate'] : 0;

        $product_data['non_geo_wcml_price']                = $product->get_regular_price( 'edit' );
        $product_data['non_geo_wcml_sale_price']           = $product->get_sale_price( 'edit' );
        $product_data['non_geo_wcml_price_net_price']      = $product_data['non_geo_wcml_price'];
        $product_data['non_geo_wcml_sale_price_net_price'] = $product_data['non_geo_wcml_sale_price'];

        if ( $tax_rate && is_numeric( $tax_rate ) ) {
            $product_data['non_geo_wcml_price_net_price']      = ( $product_data['non_geo_wcml_price'] / ( ( 100 + $tax_rate ) / 100 ) );
            $product_data['non_geo_wcml_sale_price_net_price'] = ( $product_data['non_geo_wcml_sale_price'] / ( ( 100 + $tax_rate ) / 100 ) );
        }

        return $product_data;
    }

    /**
     * Localize price args.
     *
     * @since 5.0.0
     * @access public
     *
     * @param array        $args The localize price arguments.
     * @param Product_Feed $feed The feed data.
     * @return array
     */
    public function localize_price_args( $args, $feed ) {
        global $woocommerce_wpml;
        if ( null === $woocommerce_wpml->multi_currency ) {
            return $args;
        }

        // Get the currency from the feed or use the base currency if no currency is set.
        $wcml_currency     = $feed->wcml ?? '';
        $wcml_currency     = $feed->wcml ?? $this->get_base_currency();
        $currency_settings = $woocommerce_wpml->multi_currency->get_currency_details_by_code( $wcml_currency );
        return ! empty( $currency_settings ) ? array(
            'decimal_separator'  => $currency_settings['decimal_sep'],
            'thousand_separator' => $currency_settings['thousand_sep'],
            'decimals'           => $currency_settings['num_decimals'],
        ) : $args;
    }

    /**
     * Override product feed installments currency.
     *
     * @since 4.9.7
     * @access public
     *
     * @param string $currency The currency.
     * @param array  $feed     The feed data.
     * @return string
     */
    public function wcml_installment_currency( $currency, $feed ) {
        $wcml_currency = $feed->wcml ?? '';

        return ! empty( $wcml_currency ) ? $wcml_currency : $currency;
    }

    /**
     * Get base currency.
     *
     * @since 4.9.7
     * @access private
     *
     * @return string
     */
    private function get_base_currency() {
        return function_exists( 'wcml_get_woocommerce_currency_option' )
            ? wcml_get_woocommerce_currency_option()
            : get_option( 'woocommerce_currency' );
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
        add_filter( 'adt_product_feed_data', array( $this, 'add_wcml_data' ) );

        // Add WCML data to product feed on creation.
        add_filter( 'adt_create_product_feed_props', array( $this, 'create_product_feed_props' ), 30, 3 );

        // Add WCML data to product feed.
        add_filter( 'adt_edit_feed_general_tab_props', array( $this, 'edit_product_feed_props' ), 30, 2 );

        // Clone WCML data.
        add_action( 'adt_clone_product_feed_before_save', array( $this, 'clone_product_feed_before_save' ), 30, 2 );

        // Add WCML attributes.
        add_filter( 'adt_product_feed_attributes', array( $this, 'add_wcml_extra_attributes' ), 30, 1 );

        // Show WCML currency field.
        add_action( 'adt_general_feed_settings_before_country_field', array( $this, 'show_wcml_currency_field' ), 30, 1 );

        // Convert product data currency.
        add_filter( 'adt_get_product_data', array( $this, 'wcml_product_data_currency' ), 30, 3 );

        // Override product feed installments currency.
        add_filter( 'adt_product_feed_installment_currency', array( $this, 'wcml_installment_currency' ), 30, 2 );

        // Convert shipping cost.
        add_filter( 'adt_product_feed_convert_shipping_cost', array( $this, 'convert_shipping_cost' ), 30, 3 );

        // Shipping cost currency.
        add_filter( 'adt_product_feed_shipping_cost_currency', array( $this, 'shipping_cost_currency' ), 30, 2 );

        // Localize price args by aelia currency settings.
        add_filter( 'adt_product_feed_localize_price_args', array( $this, 'localize_price_args' ), 50, 2 );
    }
}
