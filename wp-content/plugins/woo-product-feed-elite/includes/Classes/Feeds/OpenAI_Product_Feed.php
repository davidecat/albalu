<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes\Feeds
 */

namespace AdTribes\PFE\Classes\Feeds;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;

/**
 * OpenAI Product Feed class.
 *
 * @since 5.0.5
 */
class OpenAI_Product_Feed extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Feed type.
     *
     * @since 5.0.5
     *
     * @var string
     */
    protected $feed_type = 'openai_product_feed';

    /**
     * OpenAI channel attributes.
     * Update each suggested attribute with the custom attribute value.
     * Only add the attribute if the extra attribute enabled in the settings.
     *
     * @since 5.0.5
     * @access public
     *
     * @param array $attributes The attributes array.
     * @return array
     */
    public function openai_channel_attributes( $attributes ) {
        $extra_attributes = get_option( 'adt_extra_attributes', array() );
        if ( ! empty( $extra_attributes ) ) {
            // Enable Search.
            if ( isset( $extra_attributes['custom_attributes__woosea_open_ai_enable_search'] ) ) {
                $attributes['OpenAI Flags']['Enable Search'] = array(
                    'name'        => 'enable_search',
                    'feed_name'   => 'enable_search',
                    'format'      => 'required',
                    'woo_suggest' => 'custom_attributes__woosea_open_ai_enable_search',
                );
            }

            // Enable Checkout.
            if ( isset( $extra_attributes['custom_attributes__woosea_open_ai_enable_checkout'] ) ) {
                $attributes['OpenAI Flags']['Enable Checkout'] = array(
                    'name'        => 'enable_checkout',
                    'feed_name'   => 'enable_checkout',
                    'format'      => 'required',
                    'woo_suggest' => 'custom_attributes__woosea_open_ai_enable_checkout',
                );
            }

            // Material.
            if ( isset( $extra_attributes['custom_attributes__woosea_material'] ) ) {
                $attributes['Item Information']['Material'] = array(
                    'name'        => 'material',
                    'feed_name'   => 'material',
                    'format'      => 'required',
                    'woo_suggest' => 'custom_attributes__woosea_material',
                );
            }

            // Brand.
            if ( isset( $extra_attributes['custom_attributes__woosea_brand'] ) ) {
                $attributes['Item Information']['Brand'] = array(
                    'name'        => 'brand',
                    'feed_name'   => 'brand',
                    'format'      => 'required',
                    'woo_suggest' => 'custom_attributes__woosea_brand',
                );
            }

            // GTIN.
            if ( isset( $extra_attributes['custom_attributes__woosea_gtin'] ) ) {
                $attributes['Basic Product Data']['GTIN'] = array(
                    'name'        => 'gtin',
                    'feed_name'   => 'gtin',
                    'format'      => 'required',
                    'woo_suggest' => 'custom_attributes__woosea_gtin',
                );
            } else {
                // Fallback to woocommerce global unique ID. (only available for Elite).
                $attributes['Basic Product Data']['GTIN'] = array(
                    'name'        => 'gtin',
                    'feed_name'   => 'gtin',
                    'format'      => 'required',
                    'woo_suggest' => 'global_unique_id',
                );
            }

            // MPN.
            if ( isset( $extra_attributes['custom_attributes__woosea_mpn'] ) ) {
                $attributes['Basic Product Data']['MPN'] = array(
                    'name'        => 'mpn',
                    'feed_name'   => 'mpn',
                    'format'      => 'required',
                    'woo_suggest' => 'custom_attributes__woosea_mpn',
                );
            }
        }

        return $attributes;
    }

    /**
     * Filter the extra attribute value.
     *
     * @since 5.0.5
     * @access public
     *
     * @param mixed       $meta_value The meta value.
     * @param string      $field_key  The field key.
     * @param string      $meta_key   The meta key.
     * @param \WC_Product $product    The product object.
     * @return mixed
     */
    public function filter_extra_attribute_value( $meta_value, $field_key, $meta_key, $product ) {
        $default_values = array(
            '_woosea_open_ai_enable_search'   => 'true',
            '_woosea_open_ai_enable_checkout' => 'false',
        );

        // Skip if not an OpenAI flag.
        if ( ! in_array( $meta_key, array_keys( $default_values ), true ) ) {
            return $meta_value;
        }

        $meta_exists = false;
        $final_value = '';

        // Check if meta exists on product.
        if ( $product->is_type( 'variation' ) ) {
            // For variations, check variation first, then parent.
            if ( $product->meta_exists( $meta_key ) ) {
                $meta_exists = true;
                $final_value = $product->get_meta( $meta_key, true );
            } else {
                // Check parent product.
                $parent_product = wc_get_product( $product->get_parent_id() );
                if ( $parent_product && $parent_product->meta_exists( $meta_key ) ) {
                    $meta_exists = true;
                    $final_value = $parent_product->get_meta( $meta_key, true );
                }
            }
        } elseif ( $product->meta_exists( $meta_key ) ) {
            // For simple products.
            $meta_exists = true;
            $final_value = $product->get_meta( $meta_key, true );
        }

        // If meta exists, convert WordPress 'yes'/'no' to OpenAI 'true'/'false'.
        if ( $meta_exists ) {
            return 'yes' === $final_value ? 'true' : 'false';
        }

        // Meta doesn't exist anywhere, use default values for OpenAI flags.
        if ( isset( $default_values[ $meta_key ] ) ) {
            return $default_values[ $meta_key ];
        }

        return 'false';
    }

    /**
     * Run the class.
     *
     * @since 5.0.5
     */
    public function run() {
        add_filter( 'adt_openai_channel_attributes', array( $this, 'openai_channel_attributes' ) );

        add_filter( 'adt_product_data_extra_attribute_value', array( $this, 'filter_extra_attribute_value' ), 10, 4 );
    }
}
