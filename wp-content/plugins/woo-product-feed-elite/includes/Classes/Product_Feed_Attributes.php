<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

/**
 * Product_Feed_Attributes class.
 *
 * @since 4.9.7
 */
class Product_Feed_Attributes extends \AdTribes\PFP\Classes\Product_Feed_Attributes {

    /**
     * Holds the class instance object.
     *
     * @since 4.9.7
     * @access protected
     *
     * @var Product_Feed_Attributes $instance The class instance object.
     */
    protected static $instance;

    /**
     * Add extra attributes to the product feed.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array $attributes The attributes array.
     * @return array
     */
    public function add_extra_attributes( $attributes ) {
        $extra_attributes = get_option( 'adt_extra_attributes' );
        if ( ! empty( $extra_attributes ) ) {
            $extra_attributes_arr = array();
            foreach ( $extra_attributes as $key => $label ) {
                $extra_attributes_arr[ $key ] = trim( ucfirst( $label ) . ' (Added Custom attribute)' );
            }

            /**
             * Filter the extra attributes array.
             *
             * @since 4.9.7
             *
             * @param array $extra_attributes_arr The extra attributes array.
             * @return array
             */
            $extra_attributes_arr = apply_filters( 'adt_product_feed_extra_attributes', $extra_attributes_arr );

            $attributes['Added Custom Attributes'] = ! empty( $attributes['Added Custom Attributes'] ) ?
                array_merge( $attributes['Added Custom Attributes'], $extra_attributes_arr )
                : $extra_attributes_arr;
        }
        return $attributes;
    }

    /**
     * Add the global unique ID attribute to the product feed.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array $attributes The attributes array.
     * @return array
     */
    public function add_woo_global_unique_id_attribute( $attributes ) {
        $attributes['Main attributes']['global_unique_id']        = 'Global Unique ID (GTIN, UPC, EAN, or ISBN)';
        $attributes['Main attributes']['global_unique_id_parent'] = 'Global Unique ID (GTIN, UPC, EAN, or ISBN) - Parent';
        return $attributes;
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 4.9.7
     */
    public function run() {
        add_action( 'adt_product_feed_attributes', array( $this, 'add_extra_attributes' ) );

        add_action( 'adt_product_feed_attributes', array( $this, 'add_woo_global_unique_id_attribute' ) );
    }
}
