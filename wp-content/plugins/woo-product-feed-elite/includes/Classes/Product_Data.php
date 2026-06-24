<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;

/**
 * Product_Data class.
 *
 * @since 4.9.8
 */
class Product_Data extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Add the global unique id to the product data.
     *
     * Populates `global_unique_id` / `global_unique_id_parent` for every product
     * type that exposes `WC_Product::get_global_unique_id()` — not just simple
     * and variation. Composite, bundle, variable (parent), external, grouped,
     * subscription, etc. are treated like simple: both keys take the product's
     * own value. For variations, `global_unique_id_parent` always falls back to
     * the parent's GUID when the variation's own GUID is empty.
     *
     * @since 4.9.8
     * @since 5.0.8 Generalized to all product types exposing get_global_unique_id();
     *              added empty/null guards and preserved variation→parent GUID fallback
     *              when the variation's own GUID is empty.
     * @access public
     *
     * @param array       $product_data The product data.
     * @param array       $feed         The feed data.
     * @param \WC_Product $product      The product object.
     * @return array
     */
    public function add_global_unique_id( $product_data, $feed, $product ) {
        if ( empty( $product_data ) || ! is_a( $product, 'WC_Product' ) || ! method_exists( $product, 'get_global_unique_id' ) ) {
            return $product_data;
        }

        $global_unique_id = (string) $product->get_global_unique_id();
        $is_variation     = 'variation' === $product->get_type();

        // For non-variations, bail when there's no own GUID — nothing to map.
        // Variations must continue so we can still populate `_parent` from the parent.
        if ( '' === $global_unique_id && ! $is_variation ) {
            return $product_data;
        }

        if ( '' !== $global_unique_id ) {
            $product_data['global_unique_id'] = $global_unique_id;
        }

        if ( ! $is_variation ) {
            $product_data['global_unique_id_parent'] = $global_unique_id;
            return $product_data;
        }

        $parent_product = wc_get_product( $product->get_parent_id() );
        if ( ! $parent_product ) {
            return $product_data;
        }

        $parent_global_unique_id = (string) $parent_product->get_global_unique_id();
        if ( '' !== $parent_global_unique_id ) {
            $product_data['global_unique_id_parent'] = $parent_global_unique_id;
        }

        return $product_data;
    }

    /**
     * Add extra fields to the product data.
     * Location: Product Feed Elite > Settings > Extra fields.
     *
     * @since 5.0.0
     * @access public
     *
     * @param array       $product_data The product data.
     * @param array       $feed         The feed data.
     * @param \WC_Product $product      The product data.
     * @return array
     */
    public function add_extra_fields_product_data( $product_data, $feed, $product ) {
        if ( empty( $product_data ) ) {
            return $product_data;
        }

        $field_condition           = $product->get_meta( '_woosea_condition', true );
        $field_condition           = empty( $field_condition ) && isset( $product_data['condition'] ) ? $product_data['condition'] : $field_condition;
        $product_data['condition'] = ! empty( $field_condition ) ? $field_condition : $product_data['condition'] ?? '';

        return $product_data;
    }

    /**
     * Add the free shipping threshold data to the product data.
     *
     * @since 5.0.3
     * @access public
     *
     * @param array       $product_data The product data.
     * @param array       $feed         The feed data.
     * @param \WC_Product $product      The product data.
     * @return array
     */
    public function add_free_shipping_threshold( $product_data, $feed, $product ) {
        if ( empty( $product_data ) ) {
            return $product_data;
        }

        // Only add threshold data if the feed has the free_shipping_threshold attribute configured.
        if ( ! empty( $feed ) && is_object( $feed ) ) {
            $feed_attributes         = $feed->attributes ?? array();
            $has_threshold_attribute = false;

            // Check if free_shipping_threshold is configured in the feed.
            foreach ( $feed_attributes as $feed_attribute ) {
                if ( isset( $feed_attribute['mapfrom'] ) && 'free_shipping_threshold' === $feed_attribute['mapfrom'] ) {
                    $has_threshold_attribute = true;
                    break;
                }
            }

            if ( $has_threshold_attribute ) {
                $shipping_data_instance = Shipping_Data::instance();
                $threshold_data         = $shipping_data_instance->get_free_shipping_threshold_data( $product, $feed );

                // Format threshold data for Google Shopping format.
                if ( ! empty( $threshold_data ) ) {
                    $product_data['free_shipping_threshold'] = $threshold_data;
                }
            }
        }

        return $product_data;
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 4.9.8
     */
    public function run() {
        add_filter( 'adt_get_product_data', array( $this, 'add_global_unique_id' ), 10, 3 );
        add_filter( 'adt_get_product_data', array( $this, 'add_extra_fields_product_data' ), 10, 3 );
        add_filter( 'adt_get_product_data', array( $this, 'add_free_shipping_threshold' ), 10, 3 );
    }
}
