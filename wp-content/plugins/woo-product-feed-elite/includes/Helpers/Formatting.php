<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Helpers
 */

namespace AdTribes\PFE\Helpers;

/**
 * Helper methods class.
 *
 * @since 4.9.8
 */
class Formatting {

    /**
     * Localize price.
     *
     * @since 4.9.8
     * @access private
     *
     * @param float $price          The price.
     * @param array $args           Optional. Arguments to localize the price. Default empty array.
     * @param bool  $strip_currency Optional. Whether to strip currency symbol. Default true.
     * @return string
     */
    public static function localize_price( $price, $args = array(), $strip_currency = true ) {
        if ( ! is_numeric( $price ) ) {
            return $price;
        }

        if ( $strip_currency ) {
            $args['currency'] = 'ZZZ'; // Dummy currency to strip currency symbol.
        }

        return html_entity_decode( wc_clean( wc_price( $price, array_filter( $args ) ) ) );
    }


    /**
     * Format date.
     * This method is used to format date based on general purpose.
     *
     * @since 5.0.3
     * @access public
     *
     * @param string|WC_DateTime $date The date to format.
     * @return string
     */
    public static function format_date( $date ) {
        if ( is_string( $date ) ) {
            $date = new \WC_DateTime( $date, new \DateTimeZone( 'UTC' ) );
        }

        if ( ! is_a( $date, 'WC_DateTime' ) ) {
            return '';
        }

        // Convert to site timezone for proper formatting.
        $site_timezone = wc_timezone_string();
        if ( $site_timezone ) {
            $date->setTimezone( new \DateTimeZone( $site_timezone ) );
        }

        $formatted_date = $date->date_i18n( wc_date_format() . ' ' . wc_time_format() );

        return $formatted_date;
    }


    /**
     * Format the custom refresh interval.
     *
     * @since 5.0.3
     * @access public
     *
     * @param string $custom_interval_schedule The custom refresh interval schedule.
     * @param int    $custom_interval_schedule_hours The custom refresh interval schedule hours.
     * @return string
     */
    public static function format_custom_refresh_interval_schedule( $custom_interval_schedule, $custom_interval_schedule_hours = null ) {
        switch ( $custom_interval_schedule ) {
            case 'hourly':
                return __( 'Hourly', 'woo-product-feed-elite' );
            case 'daily':
                return __( 'Daily', 'woo-product-feed-elite' );
            case 'twicedaily':
                return __( 'Twice Daily', 'woo-product-feed-elite' );
            case 'weekly':
                return __( 'Weekly', 'woo-product-feed-elite' );
            case 'monthly':
                return __( 'Monthly', 'woo-product-feed-elite' );
            case 'yearly':
                return __( 'Yearly', 'woo-product-feed-elite' );
            case 'hours':
                // Translators: %s is the number of hours.
                return sprintf( __( 'Every %s Hours', 'woo-product-feed-elite' ), $custom_interval_schedule_hours );
            default:
                return '';
        }
    }

    /**
     * Format the custom refresh interval days.
     * from 1,2,3,4,5,6,7 to Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday.
     * IF all checkboxes are checked, return "Every Day".
     *
     * @since 5.0.3
     * @access public
     *
     * @param array $custom_interval_days The custom refresh interval days.
     * @return string
     */
    public static function format_custom_refresh_interval_days( $custom_interval_days ) {
        if ( count( $custom_interval_days ) === 7 ) {
            return __( 'Every Day', 'woo-product-feed-elite' );
        }

        $days = array(
            '1' => __( 'Monday', 'woo-product-feed-elite' ),
            '2' => __( 'Tuesday', 'woo-product-feed-elite' ),
            '3' => __( 'Wednesday', 'woo-product-feed-elite' ),
            '4' => __( 'Thursday', 'woo-product-feed-elite' ),
            '5' => __( 'Friday', 'woo-product-feed-elite' ),
            '6' => __( 'Saturday', 'woo-product-feed-elite' ),
            '7' => __( 'Sunday', 'woo-product-feed-elite' ),
        );
        return implode( ', ', array_intersect_key( $days, array_flip( $custom_interval_days ) ) );
    }
}
