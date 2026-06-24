<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Factories
 */

namespace AdTribes\PFE\Factories;

use AdTribes\PFE\Classes\Custom_Refresh_Interval;

/**
 * Class Product_Feed.
 *
 * @since 4.9.6
 */
class Product_Feed extends \AdTribes\PFP\Factories\Product_Feed {

    /**
     * Return extra default elite data.
     *
     * @since 4.9.7
     * @access protected
     *
     * @return array Extra default elite data.
     */
    public function extra_data() {
        return array(
            'field_manipulation'      => array(),
            'custom_refresh_interval' => array(
                'schedule'       => 'hourly',
                'schedule_hours' => '', // Interval in hours.
                'days'           => array( 1, 2, 3, 4, 5, 6, 7 ), // Days of the week.
                'commence'       => 'now', // Commence time. 'now' or 'date'.
                'commence_date'  => '', // Commence date. 'now' or 'date'.
            ),
            'wpml'                    => '', // WPML integration.
            'wcml'                    => '', // WCML integration.
            'aelia'                   => '', // Aelia integration.
            'curcy'                   => '', // Curcy integration.
            'translatepress'          => '', // Translate Press integration.
            'polylang'                => '', // Polylang Press integration.
        );
    }

    /**
     * Add extra data to the legacy options.
     *
     * @since 4.9.7
     * @access protected
     *
     * @param array $data Legacy options.
     * @return array
     */
    protected function add_legacy_option_extra_data( $data ) {
        $data['field_manipulation'] = $this->data['field_manipulation'] ?? array();
        $data['WPML']               = $this->data['wpml'] ?? '';
        $data['WCML']               = $this->data['wcml'] ?? '';
        $data['AELIA']              = $this->data['aelia'] ?? '';
        $data['CURCY']              = $this->data['curcy'] ?? '';
        $data['TRANSLATEPRESS']     = $this->data['translatepress'] ?? '';
        $data['polylang']           = $this->data['polylang'] ?? '';
        return $data;
    }

    /**
     * Get the custom refresh interval.
     *
     * @since 5.0.3
     * @access public
     *
     * @param string $key The key to get the custom refresh interval.
     * @return array The custom refresh interval.
     */
    public function get_custom_refresh_interval( $key = '' ) {
        if ( ! empty( $key ) ) {
            return $this->data['custom_refresh_interval'][ $key ] ?? '';
        }

        return $this->data['custom_refresh_interval'] ?? array();
    }


    /**
     * Register action scheduler.
     *
     * @since 5.0.3
     * @access public
     */
    public function register_action() {
        if ( 'custom' !== $this->refresh_interval ) {
            parent::register_action();
            return;
        }

        // Unschedule the previous action.
        $this->unregister_action();

        $custom_interval = $this->get_custom_refresh_interval();
        $schedule        = $custom_interval['schedule'] ?? 'hourly';
        $schedule_hours  = absint( $custom_interval['schedule_hours'] ?? 1 );
        $commence        = $custom_interval['commence'] ?? 'now';
        $commence_date   = $custom_interval['commence_date'] ?? '';

        $custom_refresh_interval_instance = Custom_Refresh_Interval::instance();

        // Calculate the next execution timestamp and interval.
        // Note: Day filtering is not handled here - it will be checked in the callback.
        // Action Scheduler will run at the standard intervals, and the callback will
        // check if the current day is in the selected days before processing.
        $timestamp           = $custom_refresh_interval_instance->calculate_custom_timestamp( $schedule, $schedule_hours, $commence, $commence_date );
        $interval_in_seconds = $custom_refresh_interval_instance->calculate_custom_interval( $schedule, $schedule_hours );

        // If the timestamp is 0 or the interval in seconds is 0, do nothing.
        if ( $timestamp <= 0 || $interval_in_seconds <= 0 ) {
            return;
        }

        // Schedule the Action Scheduler event.
        as_schedule_recurring_action(
            $timestamp,
            $interval_in_seconds,
            ADT_PFP_AS_GENERATE_PRODUCT_FEED,
            array( 'feed_id' => $this->id ),
            ADT_PFE_AS_GENERATE_PRODUCT_FEED_GROUP
        );
    }

    /**
     * Unregister action scheduler.
     *
     * Clears entries from both the Pro group (via parent) and the Elite custom-interval
     * group. Without the Elite-group clear, switching a feed from Custom back to a
     * standard interval leaves a stale Elite-group entry alongside the new Pro-group
     * entry, causing double scheduling.
     *
     * @since 5.0.8
     * @access public
     */
    public function unregister_action() {
        parent::unregister_action();

        as_unschedule_all_actions(
            ADT_PFP_AS_GENERATE_PRODUCT_FEED,
            array( 'feed_id' => $this->id ),
            ADT_PFE_AS_GENERATE_PRODUCT_FEED_GROUP
        );
    }

    /**
     * Generate product feed.
     *
     * @since 5.0.3
     * @access public
     *
     * @param string $context The context of the generation. 'schedule' or 'manual'.
     * @return mixed
     */
    public function generate( $context = 'schedule' ) {
        $refresh_interval = $this->refresh_interval ?? '';
        if ( 'custom' === $refresh_interval && 'schedule' === $context && ! $this->is_today_selected_day() ) {
            return;
        }

        return parent::generate( $context );
    }

    /**
     * Check if today is a selected day for the given feed.
     * This method can be used in the feed processing callback to determine
     * if the feed should run today based on the selected days.
     *
     * @since 5.0.3
     * @access public
     *
     * @return bool True if today is a selected day, false otherwise.
     */
    public function is_today_selected_day() {
        // Only apply day filtering for specific schedule types.
        $schedule = $this->get_custom_refresh_interval( 'schedule' );
        if ( ! in_array( $schedule, array( 'hourly', 'hours', 'daily', 'twicedaily' ), true ) ) {
            return true; // Weekly, monthly, yearly don't use day filtering.
        }

        $selected_days = $this->get_custom_refresh_interval( 'days' );
        if ( empty( $selected_days ) || ! is_array( $selected_days ) ) {
            return true; // No day restriction.
        }

        $current_day = (int) gmdate( 'N' ); // 1 (Monday) to 7 (Sunday).
        return in_array( $current_day, array_map( 'absint', $selected_days ), true );
    }
}
