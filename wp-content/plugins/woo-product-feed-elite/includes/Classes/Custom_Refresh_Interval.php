<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Helpers\Product_Feed_Helper;
use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Factories\Vite_App;
use AdTribes\PFE\Factories\Product_Feed;

/**
 * Custom Refresh Interval class.
 *
 * @since 5.0.3
 */
class Custom_Refresh_Interval extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Enqueue scripts and styles.
     *
     * @since 5.0.3
     * @access public
     *
     * @return void
     */
    public function admin_enqueue_scripts() {
        // Enqueue scripts and styles only on the plugin pages.
        if ( Helper::is_plugin_page() ) {
            $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $tab  = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

            // Edit feed page.
            if ( 'adt-edit-feed' === $page && ( 'general' === $tab || '' === $tab ) ) {
                wp_enqueue_style( 'adt-pfe-flatpickrjs', ADT_PFE_JS_URL . 'lib/flatpickr/flatpickr.min.css', array(), WOOCOMMERCESEA_ELITE_PLUGIN_VERSION );
                wp_enqueue_script( 'adt-pfe-flatpickrjs', ADT_PFE_JS_URL . 'lib/flatpickr/flatpickr.min.js', array(), WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );

                $vite_app = new Vite_App(
                    'adt-pfe-custom-refresh-interval',
                    'src/vanilla/custom-refresh-interval/index.ts',
                    array( 'jquery' )
                );
                $vite_app->enqueue();
            }
        }
    }

    /**
     * Add custom refresh interval labels.
     *
     * @since 13.4.6
     * @access public
     *
     * @param array       $intervals The refresh interval options.
     * @param object|null $feed The feed object.
     * @return array
     */
    public function add_custom_refresh_interval_labels_manage_feeds_table( $intervals, $feed = null ) {
        $intervals['custom'] = __( 'Custom', 'woo-product-feed-elite' );

        if ( $feed && Product_Feed_Helper::is_a_product_feed( $feed ) ) {
            $custom_interval  = $feed->get_custom_refresh_interval();
            $refresh_interval = $feed->refresh_interval ?? '';

            if ( 'custom' === $refresh_interval && ! empty( $custom_interval ) ) {
                ob_start();
                Helper::locate_admin_template(
                    'manage-feeds/custom-refresh-interval-label.php',
                    true,
                    false,
                    array( 'custom_interval' => $custom_interval )
                );
                $intervals['custom'] = ob_get_clean();
            }
        }

        return $intervals;
    }

    /**
     * Add custom refresh interval.
     *
     * @since 5.0.3
     * @access public
     *
     * @param Product_Feed|array|null $feed an instance of Product_Feed if existing feed, array if new feed, null if no feed.
     * @return void
     */
    public function add_custom_refresh_interval( $feed ) {
        $data = array();
        $args = array(
            'schedule'       => 'hourly',
            'schedule_hours' => '',
            'days'           => array( 1, 2, 3, 4, 5, 6, 7 ),
            'commence'       => 'now',
            'commence_date'  => '',
        );

        if ( $feed && Product_Feed_Helper::is_a_product_feed( $feed ) ) {
            $data = $feed->get_custom_refresh_interval();
        } elseif ( is_array( $feed ) ) {
            $data = $feed['custom_refresh_interval'] ?? array();
        }

        $data = wp_parse_args( $data, $args );

        // Convert UTC commence_date to local time for display.
        if ( ! empty( $data['commence_date'] ) ) {
            $data['commence_date'] = Helper::convert_utc_datetime_to_local( $data['commence_date'] );
        }

        Helper::locate_admin_template(
            'edit-feed/custom-refresh-interval.php',
            true,
            true,
            array(
                'feed' => $feed,
                'data' => $data,
            )
        );
    }

    /**
     * Add custom refresh interval options.
     *
     * @since 5.0.3
     * @access public
     *
     * @param array $refresh_arr The refresh interval options.
     * @return array
     */
    public function add_custom_refresh_interval_options( $refresh_arr ) {
        $refresh_arr[] = 'custom';
        return $refresh_arr;
    }

    /**
     * Add custom refresh interval labels.
     *
     * @since 5.0.3
     * @access public
     *
     * @param array $refresh_labels The refresh interval labels.
     * @return array
     */
    public function add_custom_refresh_interval_labels( $refresh_labels ) {
        $refresh_labels['custom'] = 'Custom';
        return $refresh_labels;
    }

    /**
     * Process custom refresh interval data from form input.
     *
     * @since 5.0.3
     * @access public
     *
     * @param array $data The form data containing custom refresh interval fields.
     * @return array The processed custom refresh interval data.
     */
    public function process_custom_refresh_interval_data( $data ) {
        $schedule       = isset( $data['custom_refresh_interval_schedule'] ) ? sanitize_text_field( wp_unslash( $data['custom_refresh_interval_schedule'] ) ) : 'hourly';
        $schedule_hours = isset( $data['custom_refresh_interval_schedule_hours'] ) ? intval( sanitize_text_field( wp_unslash( $data['custom_refresh_interval_schedule_hours'] ) ) ) : '';
        $days           = isset( $data['custom_refresh_interval_days'] ) ? array_map( 'intval', wp_unslash( $data['custom_refresh_interval_days'] ) ) : array();
        $commence       = isset( $data['custom_refresh_interval_commence'] ) ? sanitize_text_field( wp_unslash( $data['custom_refresh_interval_commence'] ) ) : 'now';

        // If the commence is now, set the commence to date and the commence date to now.
        // Ignore the commence date from the form.
        if ( 'now' === $commence ) {
            $commence      = 'date';
            $commence_date = gmdate( 'Y-m-d H:i:s' );
        } elseif ( 'date' === $commence && empty( $commence_date ) ) {
            $commence_date = isset( $data['custom_refresh_interval_commence_date'] ) ? sanitize_text_field( wp_unslash( $data['custom_refresh_interval_commence_date'] ) ) : '';

            if ( ! empty( $commence_date ) ) {
                // Convert commence_date to UTC for storage.
                $commence_date = Helper::convert_local_datetime_to_utc( $commence_date );
            } else {
                $commence_date = gmdate( 'Y-m-d H:i:s' );
            }
        }

        return array(
            'schedule'       => $schedule,
            'schedule_hours' => 0 !== $schedule_hours ? $schedule_hours : '',
            'days'           => $days,
            'commence'       => $commence,
            'commence_date'  => $commence_date,
        );
    }

    /**
     * Save custom refresh interval.
     *
     * @since 5.0.3
     * @access public
     *
     * @param array             $props_to_update The properties to update.
     * @param Product_Feed|null $feed an instance of Product_Feed if existing feed, null if no feed.
     * @param array             $feed_data The feed for the new feed.
     * @return array
     */
    public function save_custom_refresh_interval( $props_to_update, $feed = null, $feed_data = null ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $data = array();
        // For existing feed, the form is submitted via general tab, so the data is in $_POST.
        if ( null === $feed_data ) {
            $data = $_POST;
        } elseif ( is_array( $feed_data ) ) {
            // For new feed, the data is in the feed array of temp product feed.
            $data = $feed_data;
        }

        $props_to_update['custom_refresh_interval'] = $this->process_custom_refresh_interval_data( $data );

        return $props_to_update;
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Save temp product feed general tab props.
     *
     * @since 5.0.3
     * @access public
     *
     * @param array $project_temp The project temp.
     * @param array $form_data The form data.
     * @return array
     */
    public function save_temp_product_feed_general_tab_props( $project_temp, $form_data ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $project_temp['custom_refresh_interval'] = $this->process_custom_refresh_interval_data( $_POST );

        // Merge the form data with the project temp values.
        $project_temp = array_merge( $project_temp, $form_data );

        // Update the temp product feed option.
        update_option( ADT_OPTION_TEMP_PRODUCT_FEED, $project_temp, false );

        return $project_temp;
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Schedule custom refresh interval action.
     *
     * @since 5.0.3
     * @access public
     *
     * @param Product_Feed $feed The product feed instance.
     * @param array        $props_to_update The properties to update.
     * @param Product_Feed $feed_before The feed object before the update.
     * @return void
     */
    public function schedule_custom_refresh_interval( $feed, $props_to_update, $feed_before ) {
        if ( 'custom' !== $feed->refresh_interval ) {
            return;
        }

        $custom_interval_before = $feed_before->get_custom_refresh_interval();
        $custom_interval        = $feed->get_custom_refresh_interval();

        // If the custom interval array is the same as the previous custom interval array, do nothing.
        if ( $custom_interval_before === $custom_interval ) {
            return;
        }

        $feed->register_action();
    }

    /**
     * Calculate the custom timestamp for the first execution.
     *
     * @since 5.0.3
     * @access private
     *
     * @param string $schedule The schedule type ('hourly', 'hours', 'daily', 'twicedaily', 'weekly', 'monthly', 'yearly').
     * @param int    $schedule_hours The interval in hours (only used for 'hours' schedule type).
     * @param string $commence The commence type ('now' or 'date').
     * @param string $commence_date The commence date.
     * @return int The timestamp for the first execution.
     */
    public function calculate_custom_timestamp( $schedule, $schedule_hours, $commence, $commence_date ) {
        $current_time = time();

        // If commence is 'date', parse the commence date.
        if ( 'date' === $commence && ! empty( $commence_date ) ) {
            // The commence_date is stored as UTC, so we can use it directly.
            $commence_timestamp = strtotime( $commence_date . ' UTC' );
            if ( false === $commence_timestamp ) {
                // Invalid date, fallback to 'now'.
                return $this->get_next_valid_timestamp( $current_time, $schedule, $schedule_hours );
            }

            // If the commence date is in the past, calculate next occurrence at that same time.
            if ( $commence_timestamp <= $current_time ) {
                // Find how much time to add to get to the next occurrence.
                $interval_seconds = $this->calculate_custom_interval( $schedule, $schedule_hours );

                // Keep adding intervals until we get a future timestamp.
                while ( $commence_timestamp <= $current_time ) {
                    $commence_timestamp += $interval_seconds;
                }

                return $commence_timestamp;
            }

            // If the commence date is in the future, use that exact timestamp.
            // Don't modify it - the user wants it to start at that exact date/time.
            return $commence_timestamp;
        } else { // Fallback to 'now'.
            return $this->get_next_valid_timestamp( $current_time, $schedule, $schedule_hours );
        }
    }

    /**
     * Calculate the custom interval in seconds.
     *
     * @since 5.0.3
     * @access private
     *
     * @param string $schedule The schedule type ('hourly', 'hours', 'daily', 'twicedaily', 'weekly', 'monthly', 'yearly').
     * @param int    $schedule_hours The interval in hours (only used for 'hours' schedule type).
     * @return int The interval in seconds.
     */
    public function calculate_custom_interval( $schedule, $schedule_hours ) {
        switch ( $schedule ) {
            case 'hourly':
                return HOUR_IN_SECONDS;

            case 'hours':
                return max( 1, $schedule_hours ) * HOUR_IN_SECONDS;

            case 'daily':
                return DAY_IN_SECONDS;

            case 'twicedaily':
                return DAY_IN_SECONDS / 2; // 12 hours.

            case 'weekly':
                return WEEK_IN_SECONDS;

            case 'monthly':
                return MONTH_IN_SECONDS;

            case 'yearly':
                return YEAR_IN_SECONDS;

            default:
                return HOUR_IN_SECONDS; // Default to hourly.
        }
    }

    /**
     * Get the next valid timestamp based on the schedule.
     *
     * @since 5.0.3
     * @access private
     *
     * @param int    $timestamp The starting timestamp.
     * @param string $schedule The schedule type ('hourly', 'hours', 'daily', 'twicedaily', 'weekly', 'monthly', 'yearly').
     * @param int    $schedule_hours The interval in hours (only used for 'hours' schedule type).
     * @return int The next valid timestamp.
     */
    private function get_next_valid_timestamp( $timestamp, $schedule, $schedule_hours ) {
        switch ( $schedule ) {
            case 'hourly':
                // Next hour at the same time.
                return $timestamp + HOUR_IN_SECONDS;

            case 'hours':
                // Next X hours at the same time.
                $hours_to_add = max( 1, $schedule_hours );
                return $timestamp + ( $hours_to_add * HOUR_IN_SECONDS );

            case 'daily':
                // Align to the next day at the same time.
                return $timestamp + DAY_IN_SECONDS;

            case 'twicedaily':
                // Next 12 hours at the same time.
                return $timestamp + ( DAY_IN_SECONDS / 2 );

            case 'weekly':
                // Align to the next week at the same time.
                return $timestamp + WEEK_IN_SECONDS;

            case 'monthly':
                // Align to the next month at the same time.
                return $timestamp + MONTH_IN_SECONDS;

            case 'yearly':
                // Align to the next year at the same time.
                return $timestamp + YEAR_IN_SECONDS;

            default:
                return $timestamp + HOUR_IN_SECONDS;
        }
    }

    /**
     * Run the class.
     *
     * @since 5.0.3
     * @access public
     */
    public function run() {
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10 );

        add_filter( 'adt_format_refresh_interval_manage_feeds_table', array( $this, 'add_custom_refresh_interval_labels_manage_feeds_table' ), 10, 2 );

        add_filter( 'adt_product_feed_refresh_interval_options', array( $this, 'add_custom_refresh_interval_options' ) );
        add_filter( 'adt_product_feed_refresh_interval_labels', array( $this, 'add_custom_refresh_interval_labels' ) );
        add_action( 'adt_general_feed_settings_after_refresh_interval', array( $this, 'add_custom_refresh_interval' ) );

        // Save custom refresh interval.
        add_filter( 'adt_edit_feed_general_tab_props', array( $this, 'save_custom_refresh_interval' ), 10, 2 );

        // Add custom refresh interval data to product feed on creation.
        add_filter( 'adt_create_product_feed_props', array( $this, 'save_custom_refresh_interval' ), 10, 3 );

        // Save temp product feed general tab props on new feed creation.
        add_filter( 'adt_update_temp_product_feed', array( $this, 'save_temp_product_feed_general_tab_props' ), 10, 2 );

        // Schedule custom refresh interval.
        add_action( 'adt_after_process_general_tab_form', array( $this, 'schedule_custom_refresh_interval' ), 10, 3 );
    }
}
