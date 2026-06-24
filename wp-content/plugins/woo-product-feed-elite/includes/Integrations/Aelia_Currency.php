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
 * Aelia_Currency class.
 *
 * @since 4.9.7
 */
class Aelia_Currency extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Check if 4.9.7 plugin is active.
     *
     * @since 4.9.7
     * @return bool
     */
    public function is_active() {
        return is_plugin_active( 'woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php' ) && get_option( 'adt_enable_aelia_support' ) === 'yes';
    }

    /**
     * Add Aelia_Currency data to product feed.
     *
     * @since 4.9.7
     * @param array $data The accumulated filter data.
     * @return array
     */
    public function add_aelia_currency_data( $data ) {
        $data['aelia'] = '';
        return $data;
    }

    /**
     * Create product feed aelia data props.
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
        $props['aelia'] = sanitize_text_field( $post_data['AELIA'] ?? '' ); // phpcs:disable WordPress.Security.NonceVerification
        return $props;
    }

    /**
     * Edit product feed aelia data props.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $props_to_update The product feed properties to update.
     * @param Product_Feed $product_feed    The product feed.
     * @return array
     */
    public function edit_product_feed_props( $props_to_update, $product_feed ) {
        $props_to_update['aelia'] = sanitize_text_field( wp_unslash( $_POST['AELIA'] ?? '' ) ); // phpcs:disable WordPress.Security.NonceVerification
        return $props_to_update;
    }

    /**
     * Clone product feed aelia data before save.
     *
     * @since 4.9.7
     * @access public
     *
     * @param Product_Feed $feed          The product feed.
     * @param Product_Feed $original_feed The original feed.
     */
    public function clone_product_feed_before_save( $feed, $original_feed ) {
        $feed->set_prop( 'aelia', $original_feed->aelia );
    }

    /**
     * Show Aelia currency switcher field.
     *
     * @since 4.9.7
     * @param Product_Feed $feed Feed data.
     */
    public function show_aelia_currency_switcher_field( $feed ) {
        $aelia_enabled_currencies = apply_filters( 'wc_aelia_cs_enabled_currencies', array( get_woocommerce_currency() ) );
        $aelia_selected_currency  = apply_filters( 'wc_aelia_cs_base_currency', get_woocommerce_currency() );

        // If not new feed, get the feed wpml data.
        if ( $feed ) {
            // If feed doesn't have currency selected, use default currency.
            $feed_aelia_currency     = $feed->aelia ?? '';
            $aelia_selected_currency = ! empty( $feed_aelia_currency ) ? $feed_aelia_currency : $aelia_selected_currency;
        }

        Helper::locate_admin_template(
            'integrations/aelia-currency/aelia-currency-general-settings-currency-switcher-field.php',
            true,
            true,
            array(
                'aelia_enabled_currencies' => $aelia_enabled_currencies,
                'aelia_selected_currency'  => $aelia_selected_currency,
            )
        );
    }

    /**
     * Convert shipping cost with Aelia currency.
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
        $aelia_currency                    = $feed->aelia ?? '';
        $aelia_base_currency               = apply_filters( 'wc_aelia_cs_base_currency', get_woocommerce_currency() );
        $shipping_method                   = \WC_Shipping_Zones::get_shipping_method( $rate->get_instance_id() );
        $is_currency_manual_prices_enabled = false;
        $currency_option                   = null;

        if ( empty( $aelia_currency ) ||
            $aelia_currency === $aelia_base_currency ||
            ( ! method_exists( $shipping_method, 'get_option_key_by_currency' ) && ! method_exists( $shipping_method, 'get_instance_option_key' ) )
        ) {
            return $shipping_cost;
        }

        if ( is_a( $shipping_method, 'Aelia_CS_WC_Shipping_Flat_Rate' ) || is_a( $shipping_method, 'Aelia_CS_WC_Shipping_Free_Shipping' ) ) {
            $option_key_by_currency            = $shipping_method->get_option_key_by_currency( $shipping_method->get_instance_option_key(), $aelia_currency );
            $currency_option                   = get_option( $option_key_by_currency, null );
            $is_currency_manual_prices_enabled = isset( $currency_option['currency_manual_prices_enabled'] ) && 'yes' === $currency_option['currency_manual_prices_enabled'];
        }

        if ( $is_currency_manual_prices_enabled ) {
            $shipping_cost = $currency_option['cost'] ?? $shipping_cost;
        } else {
            $shipping_cost = apply_filters( 'wc_aelia_cs_convert', $shipping_cost, $aelia_base_currency, $aelia_currency );
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
        $aelia_currency      = $feed->aelia ?? '';
        $aelia_base_currency = apply_filters( 'wc_aelia_cs_base_currency', get_woocommerce_currency() );

        if ( empty( $aelia_currency ) ) {
            $aelia_currency = $aelia_base_currency;
        }

        return $aelia_currency;
    }

    /**
     * Convert product data currency with Aelia currency.
     *
     * We're using the "wc_aelia_cs_get_product_base_price_in_currency" filter to get the product price in the selected currency.
     * This is the filter that Aelia Currency Switcher uses to convert the product price to the selected currency.
     *
     * We wont use the wc_aelia_cs_convert filter because it's not recognizing the price by specific currency set in the product edit page.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $product_data The product data.
     * @param Product_Feed $feed         The feed data.
     * @param WC_Product   $product      The product.
     * @return array
     */
    public function aelia_convert_product_data_prices( $product_data, $feed, $product ) {
        if ( empty( $product_data ) ) {
            return $product_data;
        }

        // Get the currency from the feed or use the base currency if no currency is set.
        $aelia_currency = $feed->aelia ?? apply_filters( 'wc_aelia_cs_base_currency', get_woocommerce_currency() );

        // Is the product a bundle, we need to get the price of the bundle product.
        if ( $product instanceof \WC_Product_Bundle && isset( $GLOBALS['woocommerce-aelia-currencyswitcher'] ) ) {
            $aelia_currency_switcher = $GLOBALS['woocommerce-aelia-currencyswitcher'];
            $currencyprices_manager  = $aelia_currency_switcher->currencyprices_manager();
            $selected_currency       = $currencyprices_manager->get_selected_currency();

            /**
             * Hook to get the bundle price based on min or max.
             * So, the store owner can choose to get the min or max price of the bundle.
             *
             * @param string $min_or_max The min or max price.
             * @param Product_Feed $feed The feed data.
             * @return string
             */
            $aelia_bundle_min_or_max = apply_filters( 'adt_pfe_aelia_bundle_price_based_on_min_or_max', 'min', $feed );

            /**
             * Converts an amount from base currency to another.
             * Why? Because the get_bundle_price() method returns the price in selected currency based on the frontend currency.
             * We need to convert the price to the base currency to get the correct price.
             * However, the limitaion is the sale price is not available from product bundle object.
             *
             * @param float amount The amount to convert.
             * @param string to_currency The destination Currency.
             * @param string from_currency The source Currency.
             * @return float The amount converted in the destination currency.
             */
            $product_data['price']         = $currencyprices_manager->convert_from_base( $product->get_bundle_price( $aelia_bundle_min_or_max ), $aelia_currency, $selected_currency );
            $product_data['regular_price'] = $currencyprices_manager->convert_from_base( $product->get_bundle_regular_price( $aelia_bundle_min_or_max ), $aelia_currency, $selected_currency );
        } else {
            // Standard price conversion for non-bundle products.
            $product_data['price']         = apply_filters( 'wc_aelia_cs_get_product_base_price_in_currency', $product->get_price( 'edit' ), $product->get_id(), 'price', $aelia_currency );
            $product_data['regular_price'] = apply_filters( 'wc_aelia_cs_get_product_base_price_in_currency', $product->get_regular_price( 'edit' ), $product->get_id(), 'regular', $aelia_currency );
            $product_data['sale_price']    = $product->get_sale_price() ? apply_filters( 'wc_aelia_cs_get_product_base_price_in_currency', $product->get_sale_price( 'edit' ), $product->get_id(), 'sale', $aelia_currency ) : '';
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
        $converted_prices = array(
            'price'         => $product_data['price'],
            'regular_price' => $product_data['regular_price'],
        );

        // Add sale price to process if product is on sale.
        if ( ! empty( $product_data['sale_price'] ) ) {
            $converted_prices['sale_price'] = $product_data['sale_price'];
        }

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
     * Localize price args by aelia currency settings.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $args The localize price arguments.
     * @param Product_Feed $feed The feed data.
     * @return array
     */
    public function aelia_localize_price_args( $args, $feed ) {
        // Get the currency from the feed or use the base currency if no currency is set.
        $aelia_currency                   = $feed->aelia ?? apply_filters( 'wc_aelia_cs_base_currency', get_woocommerce_currency() );
        $aelia_currency_switcher_settings = \Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher::settings();
        return array(
            'decimals'           => $aelia_currency_switcher_settings->price_decimals( $aelia_currency ),
            'decimal_separator'  => $aelia_currency_switcher_settings->get_currency_decimal_separator( $aelia_currency ),
            'thousand_separator' => $aelia_currency_switcher_settings->get_currency_thousand_separator( $aelia_currency ),
        );
    }

    /**
     * Run Aelie currency switcher integration hooks.
     *
     * @since 4.9.7
     */
    public function run() {
        if ( ! $this->is_active() ) {
            return;
        }

        add_filter( 'adt_product_feed_data', array( $this, 'add_aelia_currency_data' ) );

        // Add Aelia currency data to product feed on creation.
        add_filter( 'adt_create_product_feed_props', array( $this, 'create_product_feed_props' ), 40, 3 );

        // Add Aelia currency data to product feed.
        add_filter( 'adt_edit_feed_general_tab_props', array( $this, 'edit_product_feed_props' ), 40, 2 );

        // Clone Aelia currency data.
        add_action( 'adt_clone_product_feed_before_save', array( $this, 'clone_product_feed_before_save' ), 40, 2 );

        // Show Aelia currency switcher field.
        add_action( 'adt_general_feed_settings_before_country_field', array( $this, 'show_aelia_currency_switcher_field' ), 40, 1 );

        // Convert product data currency.
        add_filter( 'adt_get_product_data', array( $this, 'aelia_convert_product_data_prices' ), 40, 3 );

        // Convert shipping cost.
        add_filter( 'adt_product_feed_convert_shipping_cost', array( $this, 'convert_shipping_cost' ), 40, 3 );

        // Shipping cost currency.
        add_filter( 'adt_product_feed_shipping_cost_currency', array( $this, 'shipping_cost_currency' ), 40, 2 );

        // Localize price args by aelia currency settings.
        add_filter( 'adt_product_feed_localize_price_args', array( $this, 'aelia_localize_price_args' ), 40, 2 );
    }
}
