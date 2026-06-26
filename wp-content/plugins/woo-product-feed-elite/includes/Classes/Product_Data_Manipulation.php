<?php

/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Factories\Product_Feed;
use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Helpers\Product_Feed_Helper;
use AdTribes\PFE\Classes\Product_Feed_Attributes;

/**
 * Product_Data_Manipulation class.
 *
 * @since 4.9.7
 */
class Product_Data_Manipulation extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Add tabs.
     *
     * @since 5.0.1
     * @param array $tabs The tabs.
     * @return array
     */
    public function add_tabs( $tabs ) {
        // Check if the field manipulation is enabled.
        $product_data_manipulation_setting = get_option( 'adt_enable_data_manipulation_support' );
        if ( 'yes' !== $product_data_manipulation_setting ) {
            return $tabs;
        }

        // Create a new array to hold the reordered tabs.
        $reordered_tabs = array();

        // Loop through the tabs and insert data_manipulation after filters_rules.
        foreach ( $tabs as $key => $value ) {
            $reordered_tabs[ $key ] = $value;

            // After adding filters_rules, add the data_manipulation tab.
            if ( 'filters_rules' === $key ) {
                $reordered_tabs['data_manipulation'] = __( 'Data Manipulation', 'woo-product-feed-elite' );
            }
        }

        return $reordered_tabs;
    }

    /**
     * Render the tab content.
     *
     * @since 5.0.1
     * @param string $tab The tab.
     */
    public function render_tab_content( $tab ) {
        // Check if the field manipulation is enabled.
        $product_data_manipulation_setting = get_option( 'adt_enable_data_manipulation_support' );
        if ( 'yes' !== $product_data_manipulation_setting ) {
            return;
        }

        switch ( $tab ) {
            case 'data_manipulation':
                Helper::locate_admin_template( 'edit-feed/tabs/data-manipulation-tab.php', true );
                break;
        }
    }

    /**
     * Clone product feed before save.
     *
     * @since 4.9.7
     * @access public
     *
     * @param Product_Feed $feed          The product feed.
     * @param Product_Feed $original_feed The original feed.
     */
    public function clone_product_feed_before_save( $feed, $original_feed ) {
        $feed->set_prop( 'field_manipulation', $original_feed->field_manipulation );
    }

    /**
     * Process the tab form.
     *
     * @since 4.9.7
     * @access public
     *
     * @param string       $active_tab The active tab.
     * @param Product_Feed $feed       The product feed.
     */
    public function process_tab_form( $active_tab, $feed ) {
        // phpcs:disable WordPress.Security.NonceVerification
        if ( 'data_manipulation' !== $active_tab ) {
            return;
        }

        $field_manipulation = isset( $_POST['field_manipulation'] ) ? Helper::sanitize_array( wp_unslash( $_POST['field_manipulation'] ) ) : array(); // phpcs:ignore

        $feed->set_prop( 'field_manipulation', $field_manipulation );
        $feed->save();
        // phpcs:enable WordPress.Security.NonceVerification
    }

    /**
     * Process product data manipulations.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array        $product_data The product data array.
     * @param Product_Feed $feed         The feed object.
     * @param WC_Product   $product      The product object.
     * @return array
     */
    public function process_product_data_manipulation( $product_data, $feed, $product ) {
        // Check if the field manipulation is enabled.
        $product_data_manipulation_setting = get_option( 'adt_enable_data_manipulation_support' );
        if ( 'yes' !== $product_data_manipulation_setting ) {
            return $product_data;
        }

        $field_manipulation = $feed->field_manipulation;
        if ( empty( $field_manipulation ) || empty( $product_data ) ) {
            return $product_data;
        }

        $product_type_data = $product->get_type();
        foreach ( $field_manipulation as $manipulation ) {
            $alter_field    = $manipulation['attribute'];
            $becomes        = $manipulation['becomes'];
            $product_type   = 'variable' === $manipulation['product_type'] ? 'variation' : $manipulation['product_type'];
            $altered_value  = '';
            $altered_values = array();

            // Field manipulation only for the product_types that were determined.
            if ( ( $product_type !== $product_type_data ) && 'all' !== $product_type ) {
                continue;
            }

            // Separator for the altered values. Default is a space.
            $separator = apply_filters( 'adt_field_manipulation_separator', ' ', $manipulation, $feed );

            // These are numeric values, user probably want to add those together. True by default.
            // e.g user wants to add price and shipping price together.
            $sum_prices    = apply_filters( 'adt_field_manipulation_sum_prices', true, $manipulation, $product_data, $product, $feed );
            $prices_to_sum = apply_filters( 'adt_field_manipulation_prices_to_sum', array( 'price', 'shipping_price', 'sale_price', 'regular_price' ), $manipulation, $product_data, $product, $feed );

            foreach ( $becomes as $become ) {
                $attribute = $become['attribute'] ?? '';
                if ( empty( $attribute ) ) {
                    continue;
                }

                $new_value = $product_data[ $attribute ] ?? '';
                if ( empty( $new_value ) ) {
                    continue;
                }

                // Exclude prices from the becomes array, we need to sum them separately.
                if ( $sum_prices && in_array( $attribute, $prices_to_sum, true ) ) {
                    // Convert formatted price to float to sum it.
                    $price = (float) wc_format_decimal( $new_value );

                    // Adds the price to the prices summary.
                    $prices_sum = isset( $altered_values['sum_prices'] ) ? $altered_values['sum_prices'] + $price : $price;

                    // Save the price to the altered values.
                    $altered_values['sum_prices'] = $prices_sum;
                } else {
                    // Check if the attribute is an array and if so, implode it.
                    if ( is_array( $new_value ) ) {
                        // Separator for the array values. Default is a space.
                        // e.g user wants to add tags together but separate them with a comma.
                        $array_separator = apply_filters( 'adt_field_manipulation_array_separator', ' ', $manipulation, $become, $feed );
                        $new_value       = implode( $array_separator, $new_value );
                    }

                    // Add the value of the attribute to the $altered_value.
                    $altered_values[] = apply_filters( 'adt_field_manipulation_altered_value', $new_value, $manipulation, $become, $feed );
                }
            }

            // Implode the altered values with the separator.
            foreach ( $altered_values as $key => $value ) {
                // Reformat the sum of prices to a localized price.
                if ( 'sum_prices' === $key ) {
                    $altered_value .= wc_format_localized_price( $value );
                } else {
                    $altered_value .= $value;
                }

                // Add a space between values if not the last value in the $becomes array.
                if ( end( $altered_values ) !== $value ) {
                    $altered_value .= $separator;
                }
            }

            $product_data[ $alter_field ] = apply_filters( 'adt_field_manipulation_value', $altered_value, $manipulation, $product_data, $product, $feed );
        }
        return $product_data;
    }

    /***************************************************************************
     * AJAX Actions
     * **************************************************************************
     */

    /**
     * Get the attributes for the product feed.
     *
     * This method is used to get the attributes for the product feed.
     * The attributes are used for Field Mapping and Feed Filters Rules.
     * The data returned is defined by the type parameter.
     *
     * @since 13.3.7
     * @access public
     */
    public function get_data_manipulation_attributes() {
        check_ajax_referer( 'woosea_ajax_nonce', 'security' );

        if ( ! Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-elite' ) );
        }

        $response   = array();
        $attributes = Product_Feed_Attributes::instance()->get_attributes();
        if ( empty( $attributes ) ) {
            wp_send_json_error( __( 'No attributes found.', 'woo-product-feed-elite' ) );
        }

        // Excluded attributes from the data manipulation.
        $excluded_attributes = array( 'static_value' );

        // The attribute we're targeting is inside an array, we need to remove the array from the attribute.
        foreach ( $attributes as $group_name => $group_attributes ) {
            if ( is_array( $group_attributes ) ) {
                $attributes[ $group_name ] = array_diff_key( $group_attributes, array_flip( $excluded_attributes ) );
            }
        }

        if ( isset( $_POST['channel_hash'] ) ) {
            $channel_hash = sanitize_text_field( wp_unslash( $_POST['channel_hash'] ) );
            $channel_data = Product_Feed_Helper::get_channel_from_legacy_channel_hash( $channel_hash );

            if ( empty( $channel_data ) ) {
                wp_send_json_error( __( 'No channel data found.', 'woo-product-feed-elite' ) );
            }

            // Check if file exists.
            $channel_file_path = ADT_PFP_CHANNEL_CLASS_ROOT_PATH . 'class-' . $channel_data['fields'] . '.php';
            if ( ! file_exists( $channel_file_path ) ) {
                wp_send_json_error( __( 'Channel file not found.', 'woo-product-feed-elite' ) );
            }

            // Include the channel file.
            require $channel_file_path;

            $channel_class_name = 'WooSEA_' . $channel_data['fields'];
            $channel_class      = new $channel_class_name();
            $channel_attributes = $channel_class->get_channel_attributes();

            $response = array(
                'field_options'     => $channel_attributes,
                'attribute_options' => $attributes,
            );
            if ( isset( $_REQUEST['type'] ) && 'html' === $_REQUEST['type'] ) {
                foreach ( $response as $key => $attributes ) {
                    ob_start();
                    \AdTribes\PFP\Helpers\Helper::locate_admin_template( 'components/attributes-dropdown.php', true, false, array( 'attributes' => $attributes ) );
                    $response[ $key ] = ob_get_clean();
                }
            }
        } else {
            $response = $attributes;

            if ( isset( $_REQUEST['type'] ) && 'html' === $_REQUEST['type'] ) {
                ob_start();
                \AdTribes\PFP\Helpers\Helper::locate_admin_template( 'components/attributes-dropdown.php', true, false, array( 'attributes' => $attributes ) );
                $response = ob_get_clean();
            }
        }

        wp_send_json_success( $response );
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 4.9.7
     */
    public function run() {
        add_filter( 'adt_edit_feed_tabs', array( $this, 'add_tabs' ), 10 );
        add_action( 'adt_edit_feed_tab_content', array( $this, 'render_tab_content' ), 10, 1 );
        add_action( 'adt_clone_product_feed_before_save', array( $this, 'clone_product_feed_before_save' ), 10, 2 );
        add_action( 'adt_process_tab_form', array( $this, 'process_tab_form' ), 10, 2 );

        add_filter( 'adt_get_product_data', array( $this, 'process_product_data_manipulation' ), 10, 3 );

        add_action( 'wp_ajax_woosea_ajax_get_data_manipulation_attributes', array( $this, 'get_data_manipulation_attributes' ) );
    }
}
