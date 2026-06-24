<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Helpers
 */

namespace AdTribes\PFE\Helpers;

use AdTribes\PFE\Factories\Product_Feed;

/**
 * Helper methods class.
 *
 * @since 4.9.7
 */
class Product_Feed_Helper extends \AdTribes\PFP\Helpers\Product_Feed_Helper {

    /**
     * Get history products chart data.
     *
     * This method is used to get the history products chart data.
     *
     * @since 4.9.7
     * @access public
     *
     * @param Product_Feed $product_feed The product feed object.
     * @return string
     */
    public static function get_history_products_chart_data( $product_feed ) {
        $chart_data       = array();
        $history_products = $product_feed->history_products;
        if ( ! empty( $history_products ) ) {
            // Only show last 30 updates.
            $history_products = array_slice( $history_products, -30, 30, true );
            foreach ( $history_products as $key => $value ) {
                // Check if value is an array and not empty.
                // Newer versions of the plugin store the data in an array.
                if ( is_array( $value ) && ! empty( $value ) ) {
                    // Get first array key.
                    $array_key                = key( $value );
                    $chart_data[ $array_key ] = $value[ $array_key ];
                } else {
                    $chart_data[ $key ] = $value;
                }
            }
        }
        return wp_json_encode( $chart_data );
    }
}
