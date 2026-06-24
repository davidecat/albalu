<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Abstracts\Abstract_Class;

/**
 * Shipping_Data class.
 *
 * @since 5.0.3
 */
class Shipping_Data extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Add shipping attributes to the product feed.
     *
     * @since 5.0.3
     * @access public
     *
     * @param array $attributes The attributes array.
     * @return array
     */
    public function add_shipping_attributes( $attributes ) {
        $free_shipping_threshold = array(
            'free_shipping_threshold' => 'Free shipping threshold',
        );

        $main_attributes               = $attributes['Main attributes'];
        $attributes['Main attributes'] = array_merge( $main_attributes, $free_shipping_threshold );
        return $attributes;
    }

    /**
     * Get free shipping threshold data for Google Shopping feeds.
     *
     * @since 5.0.3
     * @access public
     *
     * @param WC_Product $product The product object.
     * @param object     $feed    The feed object.
     * @return array
     */
    public function get_free_shipping_threshold_data( $product, $feed ) {
        // Initialize WC session if it doesn't exist (for cron context).
        $this->maybe_init_wc_session();

        $threshold_data = array();
        $feed_channel   = $feed->get_channel();
        if ( empty( $feed_channel ) ) {
            return $threshold_data;
        }

        $shipping_zones    = \WC_Shipping_Zones::get_zones();
        $shipping_currency = apply_filters( 'adt_product_feed_shipping_cost_currency', get_woocommerce_currency(), $feed );
        $country_code      = $feed->country;

        // Get shipping options.
        $options = array(
            'add_all_shipping'               => get_option( 'adt_add_all_shipping', 'no' ),
            'only_free_shipping'             => get_option( 'adt_remove_other_shipping_classes_on_free_shipping', 'no' ), // Only include free shipping if free shipping is met.
            'remove_free_shipping'           => get_option( 'adt_remove_free_shipping', 'no' ),  // Remove free shipping method.
            'remove_local_pickup_shipping'   => get_option( 'adt_remove_local_pickup_shipping', 'no' ), // Remove local pickup shipping method.
            'include_all_shipping_countries' => ( true === $feed->include_all_shipping_countries || 'yes' === $feed->include_all_shipping_countries ) ? 'yes' : 'no',
        );

        // Collect all countries that have specific zones to avoid duplication with "Everywhere" zones.
        $countries_with_specific_zones = array();
        foreach ( $shipping_zones as $shipping_zone ) {
            $zone_locations = $shipping_zone['zone_locations'] ?? array();
            if ( ! empty( $zone_locations ) ) {
                foreach ( $zone_locations as $zone_location ) {
                    if ( 'country' === $zone_location->type || 'code' === $zone_location->type ) {
                        $countries_with_specific_zones[] = $zone_location->code;
                    } elseif ( 'state' === $zone_location->type ) {
                        $zone_expl = explode( ':', $zone_location->code );
                        if ( ! empty( $zone_expl[0] ) ) {
                            $countries_with_specific_zones[] = $zone_expl[0];
                        }
                    }
                }
            }
        }
        $countries_with_specific_zones = array_unique( $countries_with_specific_zones );

        if ( ! empty( $shipping_zones ) ) {
            foreach ( $shipping_zones as $shipping_zone ) {
                $zone_threshold_data = $this->_get_zone_free_shipping_thresholds( $shipping_zone, $options, $country_code, $shipping_currency, $feed, $countries_with_specific_zones );

                if ( ! empty( $zone_threshold_data ) ) {
                    $threshold_data = array_merge( $threshold_data, $zone_threshold_data );
                }
            }
        }

        // Remove duplicates based on country.
        $unique_thresholds = array();
        foreach ( $threshold_data as $threshold ) {
            $country = $threshold['country'];
            if ( ! isset( $unique_thresholds[ $country ] ) ||
                ( isset( $unique_thresholds[ $country ]['price_threshold'] ) &&
                    $threshold['price_threshold'] < $unique_thresholds[ $country ]['price_threshold'] ) ) {
                $unique_thresholds[ $country ] = $threshold;
            }
        }

        /**
         * Filter the free shipping threshold data.
         *
         * @since 5.0.3
         *
         * @param array  $unique_thresholds The threshold data.
         * @param object $product  The product object.
         * @param object $feed     The feed object.
         * @return array
         */
        return apply_filters( 'adt_product_feed_free_shipping_threshold_data', array_values( $unique_thresholds ), $product, $feed );
    }

    /**
     * Get free shipping threshold data from a specific shipping zone.
     *
     * @since 5.0.3
     * @access private
     *
     * @param array  $shipping_zone    The shipping zone data.
     * @param array  $options          The shipping options.
     * @param string $country_code     The feed country code.
     * @param string $shipping_currency The shipping currency.
     * @param object $feed             The feed object.
     * @param array  $countries_with_specific_zones The countries with specific zones.
     * @return array
     */
    private function _get_zone_free_shipping_thresholds( $shipping_zone, $options, $country_code, $shipping_currency, $feed, $countries_with_specific_zones ) {
        $threshold_data = array();
        $zone_locations = $shipping_zone['zone_locations'] ?? array();

        // Check if this zone is set to "Everywhere" (no specific locations).
        $is_everywhere_zone = empty( $zone_locations );

        // Check if feed country is not set.
        $feed_country_not_set = empty( $country_code );

        // Collect all countries and states in this zone.
        $countries_in_zone = array();
        $states_by_country = array();

        foreach ( $zone_locations as $zone_location ) {
            if ( 'country' === $zone_location->type || 'code' === $zone_location->type ) {
                $countries_in_zone[] = $zone_location->code;
            } elseif ( 'state' === $zone_location->type ) {
                $zone_expl = explode( ':', $zone_location->code );
                if ( ! empty( $zone_expl[0] ) && ! empty( $zone_expl[1] ) ) {
                    if ( ! isset( $states_by_country[ $zone_expl[0] ] ) ) {
                        $states_by_country[ $zone_expl[0] ] = array();
                    }
                    $states_by_country[ $zone_expl[0] ][] = $zone_expl[1];

                    if ( ! in_array( $zone_expl[0], $countries_in_zone, true ) ) {
                        $countries_in_zone[] = $zone_expl[0];
                    }
                }
            }
        }

        // If this is an "Everywhere" zone, get all allowed countries from WooCommerce.
        if ( $is_everywhere_zone ) {
            $countries_in_zone = $this->_get_everywhere_zone_countries( $options, $country_code, $feed_country_not_set, $countries_with_specific_zones, $feed );
        }

        // Skip this zone if it's not for the feed country and Add all shipping is not enabled.
        // Exception: Include "Everywhere" zones for the feed country.
        // Also exception: When include_all_shipping_countries is enabled AND feed country is not set, show all zones.
        if ( ! $is_everywhere_zone && ! in_array( $country_code, $countries_in_zone, true ) && 'yes' !== $options['add_all_shipping'] && ( 'yes' !== $options['include_all_shipping_countries'] || ! $feed_country_not_set ) ) {
            return $threshold_data;
        }

        // Get shipping methods for this zone.
        $wc_shipping_zone = new \WC_Shipping_Zone( $shipping_zone['id'] );
        $methods          = $wc_shipping_zone->get_shipping_methods( true );

        // Find free shipping methods with minimum amounts.
        $free_shipping_methods = array();
        foreach ( $methods as $method ) {
            if ( 'free_shipping' === $method->id && 'yes' === $method->enabled ) {
                // Check if the method has a minimum amount requirement.
                if ( in_array( $method->requires, array( 'min_amount', 'either', 'both' ), true ) && $method->min_amount > 0 ) {
                    $free_shipping_methods[] = $method;
                }
            }
        }

        // If no free shipping methods with minimum amounts, return empty.
        if ( empty( $free_shipping_methods ) ) {
            return $threshold_data;
        }

        // Process countries in zone.
        if ( 'yes' === $options['add_all_shipping'] || ( 'yes' === $options['include_all_shipping_countries'] && $feed_country_not_set ) ) {
            // Show thresholds for all countries in the zone.
            $countries_to_process = $countries_in_zone;
        } elseif ( $is_everywhere_zone || in_array( $country_code, $countries_in_zone, true ) ) {
            // Show threshold for the specific feed country.
            $countries_to_process = $is_everywhere_zone ? $countries_in_zone : array( $country_code );
        } else {
            return $threshold_data;
        }

        foreach ( $countries_to_process as $zone_country_code ) {
            foreach ( $free_shipping_methods as $method ) {
                $threshold = array(
                    'country'         => $zone_country_code,
                    'price_threshold' => number_format( (float) $method->min_amount, 2, '.', '' ) . ' ' . $shipping_currency,
                );

                /**
                 * Filter the free shipping threshold for a specific country.
                 *
                 * @since 5.0.3
                 *
                 * @param array  $threshold The threshold data.
                 * @param object $method    The shipping method object.
                 * @param string $zone_country_code The country code.
                 * @param object $feed      The feed object.
                 * @return array
                 */
                $threshold = apply_filters( 'adt_product_feed_free_shipping_threshold_country', $threshold, $method, $zone_country_code, $feed );

                if ( ! empty( $threshold ) ) {
                    $threshold_data[] = $threshold;
                }
            }
        }

        return $threshold_data;
    }

    /**
     * Get countries for an "Everywhere" shipping zone.
     *
     * @since 5.0.3
     * @access private
     *
     * @param array  $options                      The shipping options.
     * @param string $country_code                 The feed country code.
     * @param bool   $feed_country_not_set         Whether feed country is not set.
     * @param array  $countries_with_specific_zones Countries that have specific zones.
     * @param object $feed                         The feed object.
     * @return array
     */
    private function _get_everywhere_zone_countries( $options, $country_code, $feed_country_not_set, $countries_with_specific_zones, $feed ) {
        // Default to feed country if not including all shipping countries.
        if ( 'yes' !== $options['include_all_shipping_countries'] ) {
            $feed_country_array = array( $country_code );
            return array_diff( $feed_country_array, $countries_with_specific_zones );
        }

        // If feed country is not set, get all shipping countries from WooCommerce.
        if ( $feed_country_not_set ) {
            $shipping_countries = WC()->countries->get_shipping_countries();
            $shipping_countries = apply_filters( 'adt_product_feed_shipping_countries', $shipping_countries, $feed );

            if ( empty( $shipping_countries ) ) {
                // Fallback to feed country if no shipping countries available.
                $feed_country_array = array( $country_code );
                return array_diff( $feed_country_array, $countries_with_specific_zones );
            }

            $all_countries = array_keys( $shipping_countries );
            return array_diff( $all_countries, $countries_with_specific_zones );
        }

        // Use specific feed country.
        $feed_country_array = array( $country_code );
        return array_diff( $feed_country_array, $countries_with_specific_zones );
    }

    /**
     * Initialize WooCommerce cart session if it doesn't exist
     * This prevents errors when running via cron with table rate shipping.
     *
     * @since 5.0.3
     * @access private
     */
    private function maybe_init_wc_session() {
        // Check if WC exists but session is not initialized.
        if ( wp_doing_cron() && function_exists( 'wc_load_cart' ) && ( ! isset( WC()->session ) || ! is_object( WC()->session ) ) ) {
            // Use wc_load_cart to initialize session and cart properly.
            wc_load_cart();
        }
    }

    /**
     * Add the custom flat rate fields to the shipping methods.
     *
     * @since 5.0.6
     * @access public
     *
     * @param array $settings The shipping settings.
     * @return array
     */
    public function add_custom_flat_rate_fields( $settings ) {
        $settings['adt_shipping_fields'] = array(
            'title'       => __( 'Product Feed ELITE for WooCommerce', 'woo-product-feed-elite' ),
            'type'        => 'title',
            'description' => __( 'Configure transit times for Google Merchant Center. Transit Time is how long shipping takes after the product leaves your warehouse. These values help calculate the total delivery time displayed to customers.', 'woo-product-feed-elite' ),
            'default'     => '',
            'desc_tip'    => true,
            'placeholder' => __( 'Enter values in business days.', 'woo-product-feed-elite' ),
        );

        // Add the transit time fields to the shipping methods.
        $settings['adt_min_transit_time'] = array(
            'title'       => __( 'Min Transit Time', 'woo-product-feed-elite' ) . ' (min_transit_time)',
            'type'        => 'number',
            'description' => __( 'Enter the minimum transit time in days.', 'woo-product-feed-elite' ),
            'default'     => '',
            'desc_tip'    => true,
            'placeholder' => __( 'e.g. 5', 'woo-product-feed-elite' ),
        );
        $settings['adt_max_transit_time'] = array(
            'title'       => __( 'Max Transit Time', 'woo-product-feed-elite' ) . ' (max_transit_time)',
            'type'        => 'number',
            'description' => __( 'Enter the maximum transit time in days.', 'woo-product-feed-elite' ),
            'default'     => '',
            'desc_tip'    => true,
            'placeholder' => __( 'e.g. 8', 'woo-product-feed-elite' ),
        );

        return $settings;
    }

    /**
     * Add transit and handling time to shipping array.
     *
     * Note: This method is designed for product feed generation where each product
     * is evaluated individually. The package always contains a single product in
     * this context. Only the first product's handling time is used, which is the
     * expected behavior for generating shipping data in product feeds.
     *
     * @since 5.0.6
     * @access public
     *
     * @param array  $shipping The shipping data.
     * @param object $rate     The shipping rate object.
     * @param object $feed     The feed object.
     * @param array  $package  The package data containing product information (single product).
     * @return array
     */
    public function add_shipping_times_to_array( $shipping, $rate, $feed, $package = array() ) {
        // Get shipping method instance by rate.
        $shipping_method = $rate->get_method_id();
        if ( 'flat_rate' !== $shipping_method ) {
            return $shipping;
        }

        // Use WooCommerce API to get the properly configured shipping method instance.
        $shipping_method_instance = \WC_Shipping_Zones::get_shipping_method( $rate->get_instance_id() );

        // If the shipping method instance is not found, return the shipping array as is.
        if ( ! $shipping_method_instance ) {
            return $shipping;
        }

        // Get transit time from flat rate settings.
        $min_transit_time = $shipping_method_instance->get_option( 'adt_min_transit_time' );
        $max_transit_time = $shipping_method_instance->get_option( 'adt_max_transit_time' );

        if ( ! empty( $min_transit_time ) ) {
            $shipping['min_transit_time'] = $min_transit_time;
        }
        if ( ! empty( $max_transit_time ) ) {
            $shipping['max_transit_time'] = $max_transit_time;
        }

        // Get handling time from product or shipping class.
        // Note: In product feed generation context, the package always contains a single product.
        // We extract the first (and only) product to determine handling time for the feed.
        $product = null;
        if ( ! empty( $package['contents'] ) ) {
            $first_item = reset( $package['contents'] );
            if ( isset( $first_item['data'] ) && $first_item['data'] instanceof \WC_Product ) {
                $product = $first_item['data'];
            }
        }

        if ( $product ) {
            $min_handling_time = $this->get_handling_time_with_fallback( $product, 'min' );
            $max_handling_time = $this->get_handling_time_with_fallback( $product, 'max' );

            if ( ! empty( $min_handling_time ) ) {
                $shipping['min_handling_time'] = $min_handling_time;
            }
            if ( ! empty( $max_handling_time ) ) {
                $shipping['max_handling_time'] = $max_handling_time;
            }
        }

        return $shipping;
    }

    /**
     * Get handling time with fallback logic.
     *
     * @since 5.0.6
     * @access public
     *
     * @param \WC_Product $product The product object.
     * @param string      $type    The type: 'min' or 'max'.
     * @return string
     */
    public function get_handling_time_with_fallback( $product, $type = 'min' ) {
        $meta_key = '_woosea_' . $type . '_handling_time';

        // First, check product-level meta.
        $handling_time = $product->get_meta( $meta_key, true );

        // Validate it's a valid non-negative number (defense-in-depth).
        if ( '' !== $handling_time && null !== $handling_time && is_numeric( $handling_time ) && (int) $handling_time >= 0 ) {
            return (string) (int) $handling_time; // Normalize to integer string.
        }

        // If product is a variation, check parent product.
        if ( $product->is_type( 'variation' ) ) {
            $parent_product = wc_get_product( $product->get_parent_id() );
            if ( $parent_product ) {
                $handling_time = $parent_product->get_meta( $meta_key, true );
                // Validate it's a valid non-negative number (defense-in-depth).
                if ( '' !== $handling_time && null !== $handling_time && is_numeric( $handling_time ) && (int) $handling_time >= 0 ) {
                    return (string) (int) $handling_time; // Normalize to integer string.
                }
            }
        }

        // Second, check shipping class.
        $shipping_class_id = $product->get_shipping_class_id();
        if ( $shipping_class_id ) {
            $handling_time = get_term_meta( $shipping_class_id, 'adt_' . $type . '_handling_time', true );
            // Validate it's a valid non-negative number (defense-in-depth).
            if ( '' !== $handling_time && null !== $handling_time && is_numeric( $handling_time ) && (int) $handling_time >= 0 ) {
                return (string) (int) $handling_time; // Normalize to integer string.
            }
        }

        // Return empty string if no value found.
        return '';
    }


    /**
     * Add handling time columns to shipping classes.
     *
     * @since 5.0.6
     * @access public
     *
     * @param array $columns Shipping class columns.
     * @return array
     */
    public function add_shipping_class_columns( $columns ) {
        $columns['wc-shipping-class-adt-min-handling'] = __( 'Min Handling Time', 'woo-product-feed-elite' ) . ' (min_handling_time)';
        $columns['wc-shipping-class-adt-max-handling'] = __( 'Max Handling Time', 'woo-product-feed-elite' ) . ' (max_handling_time)';
        return $columns;
    }

    /**
     * Add min handling time field to shipping class modal and row.
     *
     * @since 5.0.6
     * @access public
     */
    public function add_min_handling_time_field() {
        ?>
        <div class="view">{{ data.adt_min_handling_time }}</div>
        <div class="edit">
            <input type="number" name="adt_min_handling_time" data-attribute="adt_min_handling_time" value="{{ data.adt_min_handling_time }}" placeholder="<?php esc_attr_e( 'e.g. 1', 'woo-product-feed-elite' ); ?>" min="0" step="1" />
            <div class="wc-shipping-class-modal-help-text"><strong><?php esc_html_e( 'Product Feed ELITE for WooCommerce:', 'woo-product-feed-elite' ); ?></strong> <?php esc_html_e( 'Minimum time in days between order and shipment for this shipping class', 'woo-product-feed-elite' ); ?></div>
        </div>
        <?php
    }

    /**
     * Add max handling time field to shipping class modal and row.
     *
     * @since 5.0.6
     * @access public
     */
    public function add_max_handling_time_field() {
        ?>
        <div class="view">{{ data.adt_max_handling_time }}</div>
        <div class="edit">
            <input type="number" name="adt_max_handling_time" data-attribute="adt_max_handling_time" value="{{ data.adt_max_handling_time }}" placeholder="<?php esc_attr_e( 'e.g. 3', 'woo-product-feed-elite' ); ?>" min="0" step="1" />
            <div class="wc-shipping-class-modal-help-text"><strong><?php esc_html_e( 'Product Feed ELITE for WooCommerce:', 'woo-product-feed-elite' ); ?></strong> <?php esc_html_e( 'Maximum time in days between order and shipment for this shipping class', 'woo-product-feed-elite' ); ?></div>
        </div>
        <?php
    }

    /**
     * Add handling time data to shipping classes array.
     *
     * @since 5.0.6
     * @access public
     *
     * @param array $classes Shipping classes array.
     * @return array
     */
    public function add_handling_time_to_shipping_classes( $classes ) {
        if ( empty( $classes ) || ! is_array( $classes ) ) {
            return $classes;
        }

        foreach ( $classes as $key => $class ) {
            // Handle both WP_Term objects and arrays.
            if ( is_object( $class ) && isset( $class->term_id ) ) {
                $term_id                                = $class->term_id;
                $classes[ $key ]->adt_min_handling_time = get_term_meta( $term_id, 'adt_min_handling_time', true );
                $classes[ $key ]->adt_max_handling_time = get_term_meta( $term_id, 'adt_max_handling_time', true );
            } elseif ( is_array( $class ) && isset( $class['term_id'] ) ) {
                $term_id                                  = $class['term_id'];
                $classes[ $key ]['adt_min_handling_time'] = get_term_meta( $term_id, 'adt_min_handling_time', true );
                $classes[ $key ]['adt_max_handling_time'] = get_term_meta( $term_id, 'adt_max_handling_time', true );
            }
        }
        return $classes;
    }

    /**
     * Save handling time for shipping class via AJAX.
     *
     * @since 5.0.6
     * @access public
     *
     * @param int   $term_id   Shipping class term ID.
     * @param array $data      Shipping class data.
     */
    public function save_shipping_class_handling_time( $term_id, $data ) {
        if ( isset( $data['adt_min_handling_time'] ) ) {
            $value = sanitize_text_field( $data['adt_min_handling_time'] );
            // Validate and convert to non-negative integer, or save as empty string if not provided.
            $value = '' !== $value ? absint( $value ) : '';
            update_term_meta( $term_id, 'adt_min_handling_time', $value );
        }
        if ( isset( $data['adt_max_handling_time'] ) ) {
            $value = sanitize_text_field( $data['adt_max_handling_time'] );
            // Validate and convert to non-negative integer, or save as empty string if not provided.
            $value = '' !== $value ? absint( $value ) : '';
            update_term_meta( $term_id, 'adt_max_handling_time', $value );
        }
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 5.0.3
     */
    public function run() {
        add_filter( 'adt_product_feed_attributes', array( $this, 'add_shipping_attributes' ) );

        add_filter( 'woocommerce_shipping_instance_form_fields_flat_rate', array( $this, 'add_custom_flat_rate_fields' ), 10, 1 );
        add_filter( 'adt_product_feed_shipping_array', array( $this, 'add_shipping_times_to_array' ), 10, 4 );

        // Shipping class handling time fields.
        add_filter( 'woocommerce_shipping_classes_columns', array( $this, 'add_shipping_class_columns' ), 10, 1 );
        add_action( 'woocommerce_shipping_classes_column_wc-shipping-class-adt-min-handling', array( $this, 'add_min_handling_time_field' ) );
        add_action( 'woocommerce_shipping_classes_column_wc-shipping-class-adt-max-handling', array( $this, 'add_max_handling_time_field' ) );
        add_filter( 'woocommerce_get_shipping_classes', array( $this, 'add_handling_time_to_shipping_classes' ), 10, 1 );
        add_action( 'woocommerce_shipping_classes_save_class', array( $this, 'save_shipping_class_handling_time' ), 10, 2 );
    }
}
