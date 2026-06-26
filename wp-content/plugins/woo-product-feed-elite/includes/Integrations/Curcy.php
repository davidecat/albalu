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
 * Curcy class.
 *
 * @since 4.9.7
 */
class Curcy extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Curcy plugin class data.
     *
     * @access public
     * @since 4.9.8
     *
     * @var null|object
     */
    public $woomulti_currency_data = null;

    /**
     * Class constructor.
     *
     * @since 4.9.8
     * @access public
     */
    public function __construct() {
        if ( class_exists( 'WOOMULTI_CURRENCY_Data' ) ) {
            $this->woomulti_currency_data = \WOOMULTI_CURRENCY_Data::get_ins();
        } elseif ( class_exists( 'WOOMULTI_CURRENCY_F_Data' ) ) {
            $this->woomulti_currency_data = \WOOMULTI_CURRENCY_F_Data::get_ins();
        }
    }

    /**
     * Check if Curcy plugin is active.
     *
     * @since 4.9.7
     * @return bool
     */
    public function is_active() {
        return (
            is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) ||
            is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' )
        ) && get_option( 'adt_enable_curcy_support' ) === 'yes';
    }

    /**
     * Add Curcy data to product feed.
     *
     * @since 4.9.7
     * @param array $data The accumulated filter data.
     * @return array
     */
    public function add_curcy_data( $data ) {
        $data['curcy'] = '';
        return $data;
    }

    /**
     * Show Curcy currency field.
     *
     * @since 4.9.7
     * @param array $feed Feed data.
     */
    public function show_curcy_currency_field( $feed ) {
        if ( null === $this->woomulti_currency_data ) {
            return;
        }

        $curcy_selected_currency = $this->woomulti_currency_data->get_default_currency();
        $curcy_currencies        = $this->woomulti_currency_data->get_currencies();

        // If not new feed, get the feed curcy data.
        if ( $feed ) {
            // If feed doesn't have currency selected, use default currency.
            $feed_curcy_currency     = $feed->curcy ?? '';
            $curcy_selected_currency = ! empty( $feed_curcy_currency ) ? $feed_curcy_currency : $curcy_selected_currency;
        }

        Helper::locate_admin_template(
            'integrations/curcy/curcy-general-settings-currency-field.php',
            true,
            true,
            array(
                'curcy_selected_currency' => $curcy_selected_currency,
                'curcy_currencies'        => $curcy_currencies,
            )
        );
    }

    /**
     * Create product feed curcy data props.
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
        $props['curcy'] = sanitize_text_field( $post_data['CURCY'] ?? '' ); // phpcs:disable WordPress.Security.NonceVerification
        return $props;
    }

    /**
     * Edit product feed curcy data props.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $props_to_update The product feed properties to update.
     * @param Product_Feed $product_feed    The product feed.
     * @return array
     */
    public function edit_product_feed_props( $props_to_update, $product_feed ) {
        $props_to_update['curcy'] = sanitize_text_field( wp_unslash( $_POST['CURCY'] ?? '' ) ); // phpcs:disable WordPress.Security.NonceVerification
        return $props_to_update;
    }

    /**
     * Clone product feed curcy data before save.
     *
     * @since 4.9.7
     * @access public
     *
     * @param Product_Feed $feed          The product feed.
     * @param Product_Feed $original_feed The original feed.
     */
    public function clone_product_feed_before_save( $feed, $original_feed ) {
        $feed->set_prop( 'curcy', $original_feed->wcml );
    }


    /**
     * Convert Curcy shipping cost.
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
        if ( null === $this->woomulti_currency_data ) {
            return $shipping_cost;
        }

        $curcy_default_currency = $this->woomulti_currency_data->get_default_currency();
        $curcy_currency         = $feed->curcy ?? '';

        if ( empty( $curcy_currency ) || $curcy_default_currency === $curcy_currency ) {
            return $shipping_cost;
        }

        // Get shipping cost in the selected currency.
        return wmc_get_price( $shipping_cost, $curcy_currency, true, true );
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
    public function curcy_product_data_currency( $product_data, $feed, $product ) {
        if ( empty( $product_data ) || null === $this->woomulti_currency_data ) {
            return $product_data;
        }

        $curcy_default_currency = $this->woomulti_currency_data->get_default_currency();
        $curcy_currency         = $feed->curcy ?? '';

        if (
            empty( $curcy_currency ) ||
            $curcy_default_currency === $curcy_currency ||
            ! function_exists( 'wmc_get_price' ) ||
            ! function_exists( 'wmc_adjust_fixed_price' )
        ) {
            return $product_data;
        }

        // Change the currency to the selected currency.
        $product_data['currency'] = $curcy_currency;

        /**
         * Curcy fixed prices.
         * Get the fixed prices for the product if set.
         * Always check if the feature is enabled first.
         * The meta keys are '_regular_price_wmcp' and '_sale_price_wmcp'.
         * It is stored as a JSON string.
         */
        $curcy_fixed_price         = null;
        $curcy_fixed_regular_price = $this->woomulti_currency_data->check_fixed_price()
            ? wmc_adjust_fixed_price( $this->format_json_price_meta( $product->get_meta( '_regular_price_wmcp', true ) ) )
            : null;
        $curcy_fixed_sale_price    = $this->woomulti_currency_data->check_fixed_price()
            ? wmc_adjust_fixed_price( $this->format_json_price_meta( $product->get_meta( '_sale_price_wmcp', true ) ) )
            : null;

        // Set the fixed price based if the product is on sale or not.
        if ( isset( $curcy_fixed_regular_price[ $curcy_currency ] ) && ! $product->is_on_sale() && $curcy_fixed_regular_price[ $curcy_currency ] > 0 ) {
            $curcy_fixed_price = $curcy_fixed_regular_price[ $curcy_currency ];
        } elseif ( isset( $curcy_fixed_sale_price[ $curcy_currency ] ) && $curcy_fixed_sale_price[ $curcy_currency ] > 0 ) {
            $curcy_fixed_price = $curcy_fixed_sale_price[ $curcy_currency ];
        }

        // Get the fixed price in currency.
        $curcy_fixed_regular_price = $curcy_fixed_regular_price[ $curcy_currency ] ?? null;
        $curcy_fixed_sale_price    = $curcy_fixed_sale_price[ $curcy_currency ] ?? null;

        $converted_prices = array(
            'price'         => null !== $curcy_fixed_price ? $curcy_fixed_price : wmc_get_price( $product->get_price( 'edit' ), $curcy_currency, false, true ),
            'regular_price' => null !== $curcy_fixed_regular_price ? $curcy_fixed_regular_price : wmc_get_price( $product->get_regular_price( 'edit' ), $curcy_currency, false, true ),
        );

        // Add sale price to prices to convert if product is on sale.
        if ( $product->is_on_sale() ) {
            $converted_prices['sale_price'] = null !== $curcy_fixed_sale_price ? $curcy_fixed_sale_price : wmc_get_price( $product->get_sale_price( 'edit' ), $curcy_currency, false, true );
        }

        /**
         * We want to also get the prices with taxes and get new converted prices excluding and including tax.
         * Then populate the net and forced prices to the product data.
         * Also convert rounded prices.
         *
         * Note:
         * Net price is the price excluding tax.
         * Forced price is the price including tax.
         */
        foreach ( $converted_prices as $key => $price ) {
            $product_data[ $key ]             = Product_Feed_Helper::get_price_including_tax( $price, array(), $feed, $product );
            $product_data[ $key . '_forced' ] = Product_Feed_Helper::get_price_including_tax( $price, array(), $feed, $product );
            $product_data[ 'net_' . $key ]    = Product_Feed_Helper::get_price_excluding_tax( $price, array(), $feed, $product );

            // Round prices.
            $product_data[ 'rounded_' . $key ]          = round( (float) $product_data[ $key ], 0 );
            $product_data[ $key . '_forced_rounded' ]   = round( (float) $product_data[ $key . '_forced' ], 0 );
            $product_data[ 'net_' . $key . '_rounded' ] = round( (float) $product_data[ 'net_' . $key ], 0 );
        }

        return $product_data;
    }

    /**
     * Shipping cost currency.
     *
     * @since 5.0.0
     * @access public
     *
     * @param string       $currency      The currency.
     * @param Product_Feed $feed          The feed data.
     * @return array
     */
    public function shipping_cost_currency( $currency, $feed ) {
        $curcy_default_currency = $this->woomulti_currency_data->get_default_currency();
        return $feed->curcy ?? $curcy_default_currency;
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
        // Get the currency from the feed or use the base currency if no currency is set.
        $curcy_default_currency = $this->woomulti_currency_data->get_default_currency();
        $curcy_currency         = $feed->curcy ?? $curcy_default_currency;
        $currency_settings      = $this->woomulti_currency_data->currencies_list[ $curcy_currency ] ?? array();
        return ! empty( $currency_settings ) ? array(
            'decimals'           => $currency_settings['decimals'],
            'decimal_separator'  => $currency_settings['decimal_sep'],
            'thousand_separator' => $currency_settings['thousand_sep'],
        ) : $args;
    }

    /**
     * Format JSON price meta.
     *
     * @since 4.9.7
     * @access private
     *
     * @param string $price_meta The price meta.
     * @return array
     */
    private function format_json_price_meta( $price_meta ) {
        return is_string( $price_meta ) ? json_decode( $price_meta, true ) : $price_meta;
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
        add_filter( 'adt_product_feed_data', array( $this, 'add_curcy_data' ) );

        // Add Curcy data to product feed on creation.
        add_filter( 'adt_create_product_feed_props', array( $this, 'create_product_feed_props' ), 50, 3 );

        // Add Curcy data to product feed on edit.
        add_filter( 'adt_edit_feed_general_tab_props', array( $this, 'edit_product_feed_props' ), 50, 2 );

        // Clone Curcy data.
        add_action( 'adt_clone_product_feed_before_save', array( $this, 'clone_product_feed_before_save' ), 50, 2 );

        // Show Curcy currency field.
        add_action( 'adt_general_feed_settings_before_country_field', array( $this, 'show_curcy_currency_field' ), 50, 1 );

        // Convert product data currency.
        add_filter( 'adt_get_product_data', array( $this, 'curcy_product_data_currency' ), 50, 3 );

        // Convert shipping cost.
        add_filter( 'adt_product_feed_convert_shipping_cost', array( $this, 'convert_shipping_cost' ), 50, 3 );

        // Shipping cost currency.
        add_filter( 'adt_product_feed_shipping_cost_currency', array( $this, 'shipping_cost_currency' ), 50, 2 );

        // Localize price args by aelia currency settings.
        add_filter( 'adt_product_feed_localize_price_args', array( $this, 'localize_price_args' ), 50, 2 );
    }
}
