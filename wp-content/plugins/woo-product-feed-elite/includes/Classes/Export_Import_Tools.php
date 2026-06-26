<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Factories\Product_Feed;
use AdTribes\PFE\Helpers\Product_Feed_Helper;

/**
 * Filters class with backwards compatibility.
 *
 * @since 5.0.3
 */
class Export_Import_Tools extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Import feeds from uploaded JSON file.
     *
     * @since 5.0.3
     * @access public
     */
    public function import_feeds() {
        try {
            // Check if file was uploaded.
            if ( ! isset( $_FILES['import_file'] ) || empty( $_FILES['import_file']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
                wp_send_json_error( __( 'No file uploaded. Please select a file to import.', 'woo-product-feed-elite' ) );
            }

            // Get overwrite option.
            $overwrite_existing = isset( $_POST['overwrite_existing_feeds'] ) && '1' === $_POST['overwrite_existing_feeds']; // phpcs:ignore WordPress.Security.NonceVerification.Missing

            $uploaded_file = $_FILES['import_file']; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            // Validate file type.
            $file_extension = strtolower( pathinfo( $uploaded_file['name'], PATHINFO_EXTENSION ) );
            if ( 'json' !== $file_extension ) {
                wp_send_json_error( __( 'Invalid file type. Please upload a JSON file.', 'woo-product-feed-elite' ) );
            }

            // Check file size (limit to 10MB).
            $max_file_size = 10 * 1024 * 1024; // 10MB
            if ( $uploaded_file['size'] > $max_file_size ) {
                wp_send_json_error( __( 'File too large. Maximum file size is 10MB.', 'woo-product-feed-elite' ) );
            }

            // Read and decode JSON file using WordPress filesystem.
            global $wp_filesystem;
            if ( empty( $wp_filesystem ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }

            $file_content = $wp_filesystem->get_contents( $uploaded_file['tmp_name'] );
            if ( false === $file_content ) {
                wp_send_json_error( __( 'Failed to read the uploaded file.', 'woo-product-feed-elite' ) );
            }

            $import_data = json_decode( $file_content, true );
            if ( null === $import_data || JSON_ERROR_NONE !== json_last_error() ) {
                wp_send_json_error( __( 'Invalid JSON file. Please check the file format.', 'woo-product-feed-elite' ) );
            }

            // Validate import data structure.
            if ( ! isset( $import_data['feeds'] ) || ! is_array( $import_data['feeds'] ) ) {
                wp_send_json_error( __( 'Invalid export file format. Missing feeds data.', 'woo-product-feed-elite' ) );
            }

            $feeds_to_import = $import_data['feeds'];

            $imported_count = 0;
            $updated_count  = 0;
            $skipped_count  = 0;
            $error_count    = 0;
            $import_results = array();

            foreach ( $feeds_to_import as $feed_data ) {
                $result = $this->import_single_feed( $feed_data, $overwrite_existing );

                if ( $result['success'] ) {
                    if ( 'updated' === $result['status'] ) {
                        ++$updated_count;
                    } else {
                        ++$imported_count;
                    }
                } elseif ( 'skipped' === $result['status'] ) {
                    ++$skipped_count;
                } else {
                    ++$error_count;
                }

                $import_results[] = $result;
            }

            // Prepare response message.
            $message_parts = array();
            if ( $imported_count > 0 ) {
                $message_parts[] = sprintf(
                    /* translators: %d: number of feeds imported */
                    _n( '%d feed imported successfully', '%d feeds imported successfully', $imported_count, 'woo-product-feed-elite' ),
                    $imported_count
                );
            }
            if ( $updated_count > 0 ) {
                $message_parts[] = sprintf(
                    /* translators: %d: number of feeds updated */
                    _n( '%d feed updated successfully', '%d feeds updated successfully', $updated_count, 'woo-product-feed-elite' ),
                    $updated_count
                );
            }
            if ( $skipped_count > 0 ) {
                $message_parts[] = sprintf(
                    /* translators: %d: number of feeds skipped */
                    _n( '%d feed skipped (already exists)', '%d feeds skipped (already exist)', $skipped_count, 'woo-product-feed-elite' ),
                    $skipped_count
                );
            }
            if ( $error_count > 0 ) {
                $message_parts[] = sprintf(
                    /* translators: %d: number of feeds with errors */
                    _n( '%d feed had errors', '%d feeds had errors', $error_count, 'woo-product-feed-elite' ),
                    $error_count
                );
            }

            $response_message = ! empty( $message_parts ) ? implode( ', ', $message_parts ) . '.' : __( 'Import completed.', 'woo-product-feed-elite' );

            wp_send_json_success(
                array(
                    'message'        => $response_message,
                    'imported_count' => $imported_count,
                    'updated_count'  => $updated_count,
                    'skipped_count'  => $skipped_count,
                    'error_count'    => $error_count,
                    'results'        => $import_results,
                )
            );
        } catch ( \Exception $e ) {
            wp_send_json_error(
                array(
                    'message' => sprintf(
                        /* translators: %s: error message */
                        __( 'Import failed: %s', 'woo-product-feed-elite' ),
                        $e->getMessage()
                    ),
                )
            );
        }
    }


    /**
     * Import a single feed from feed data.
     *
     * @since 5.0.3
     * @access private
     *
     * @param array $feed_data The feed data to import.
     * @param bool  $overwrite_existing Whether to overwrite existing feeds.
     * @return array Result array with success status and message.
     */
    private function import_single_feed( $feed_data, $overwrite_existing = false ) {
        try {
            // Validate required fields.
            if ( empty( $feed_data['title'] ) ) {
                return array(
                    'success' => false,
                    'status'  => 'error',
                    'message' => __( 'Feed title is missing.', 'woo-product-feed-elite' ),
                );
            }

            $existing_feed_id = 0;
            $is_update        = false;

            // Check if feed with same legacy_project_hash already exists.
            if ( ! empty( $feed_data['legacy_project_hash'] ) ) {
                $existing_feed_id = $this->get_feed_by_legacy_hash( $feed_data['legacy_project_hash'] );
                if ( $existing_feed_id > 0 ) {
                    if ( ! $overwrite_existing ) {
                        return array(
                            'success' => false,
                            'status'  => 'skipped',
                            'message' => sprintf(
                                /* translators: %s: feed title */
                                __( 'Feed "%s" already exists (same project hash).', 'woo-product-feed-elite' ),
                                $feed_data['title']
                            ),
                        );
                    } else {
                        $is_update = true;
                    }
                }
            }

            // Create new or load existing Product_Feed instance.
            $product_feed = $is_update ? Product_Feed_Helper::get_product_feed( $existing_feed_id ) : Product_Feed_Helper::get_product_feed();

            // Generate new legacy project hash if missing.
            if ( empty( $feed_data['legacy_project_hash'] ) ) {
                $feed_data['legacy_project_hash'] = \AdTribes\PFP\Helpers\Product_Feed_Helper::generate_legacy_project_hash();
            }

            // Prepare properties for the new feed.
            // except status and post_status.
            $props_to_update = array(
                'title'                                  => $feed_data['title'] ?? '',
                'country'                                => $feed_data['country'] ?? '',
                'channel_hash'                           => $feed_data['channel_hash'] ?? '',
                'file_name'                              => $feed_data['legacy_project_hash'] ?? '',
                'file_format'                            => $feed_data['file_format'] ?? 'xml',
                'delimiter'                              => $feed_data['delimiter'] ?? '',
                'refresh_interval'                       => $feed_data['refresh_interval'] ?? '',
                'custom_refresh_interval'                => $feed_data['custom_refresh_interval'] ?? array(),
                'refresh_only_when_product_changed'      => $feed_data['refresh_only_when_product_changed'] ?? false,
                'create_preview'                         => $feed_data['create_preview'] ?? false,
                'include_product_variations'             => $feed_data['include_product_variations'] ?? false,
                'only_include_default_product_variation' => $feed_data['only_include_default_product_variation'] ?? false,
                'only_include_lowest_product_variation'  => $feed_data['only_include_lowest_product_variation'] ?? false,
                'include_all_shipping_countries'         => $feed_data['include_all_shipping_countries'] ?? false,
                'utm_enabled'                            => $feed_data['utm_enabled'] ?? true,
                'utm_source'                             => $feed_data['utm_source'] ?? '',
                'utm_medium'                             => $feed_data['utm_medium'] ?? '',
                'utm_campaign'                           => $feed_data['utm_campaign'] ?? '',
                'utm_term'                               => $feed_data['utm_term'] ?? '',
                'utm_content'                            => $feed_data['utm_content'] ?? '',
                'utm_total_product_orders_lookback'      => $feed_data['utm_total_product_orders_lookback'] ?? 0,
                'attributes'                             => $feed_data['attributes'] ?? array(),
                'mappings'                               => $feed_data['mappings'] ?? array(),
                'rules'                                  => $feed_data['rules'] ?? array(),
                'filters'                                => $feed_data['filters'] ?? array(),
                'feed_filters'                           => $feed_data['feed_filters'] ?? array(),
                'feed_rules'                             => $feed_data['feed_rules'] ?? array(),
                'products_count'                         => 0, // Reset product count.
                'total_products_processed'               => 0, // Reset processed count.
                'last_updated'                           => $feed_data['last_updated'] ?? '',
                'legacy_project_hash'                    => $feed_data['legacy_project_hash'] ?? '',
                'data_version'                           => $feed_data['data_version'] ?? array(),
                'field_manipulation'                     => $feed_data['field_manipulation'] ?? array(),
                'wpml'                                   => $feed_data['wpml'] ?? '',
                'wcml'                                   => $feed_data['wcml'] ?? '',
                'aelia'                                  => $feed_data['aelia'] ?? '',
                'curcy'                                  => $feed_data['curcy'] ?? '',
                'translatepress'                         => $feed_data['translatepress'] ?? '',
                'polylang'                               => $feed_data['polylang'] ?? '',
            );

            // For new feeds, set default post_status, status, and last_updated.
            if ( ! $is_update ) {
                $props_to_update['post_status']  = 'draft'; // Import new feeds as draft initially.
                $props_to_update['status']       = 'ready'; // Set as ready, not processing.
                $props_to_update['last_updated'] = '';      // Reset last updated for new feeds.
            }
            // For updates, preserve existing post_status, status, and last_updated.

            // Set the properties.
            $product_feed->set_props( $props_to_update );

            // Set data versions if available.
            if ( ! empty( $feed_data['data_version'] ) && is_array( $feed_data['data_version'] ) ) {
                foreach ( $feed_data['data_version'] as $key => $version ) {
                    $product_feed->set_data_version( $key, $version );
                }
            }

            // Save the feed.
            $feed_id = $product_feed->save();

            if ( is_wp_error( $feed_id ) ) {
                return array(
                    'success' => false,
                    'status'  => 'error',
                    'message' => sprintf(
                        /* translators: %s: error message */
                        __( 'Failed to save feed: %s', 'woo-product-feed-elite' ),
                        $feed_id->get_error_message()
                    ),
                );
            }

            // Register action scheduler (but don't generate initially since we set refresh_interval to empty).
            // User can manually enable scheduling later.
            $product_feed->register_action();

            $message_template = $is_update
                /* translators: %s: feed title */
                ? __( 'Feed "%s" updated successfully.', 'woo-product-feed-elite' )
                /* translators: %s: feed title */
                : __( 'Feed "%s" imported successfully.', 'woo-product-feed-elite' );

            return array(
                'success' => true,
                'status'  => $is_update ? 'updated' : 'imported',
                'message' => sprintf( $message_template, $feed_data['title'] ),
                'feed_id' => $feed_id,
            );
        } catch ( \Exception $e ) {
            return array(
                'success' => false,
                'status'  => 'error',
                'message' => sprintf(
                    /* translators: %s: error message */
                    __( 'Failed to import feed: %s', 'woo-product-feed-elite' ),
                    $e->getMessage()
                ),
            );
        }
    }

    /**
     * Get feed ID by legacy project hash.
     *
     * @since 5.0.3
     * @access private
     *
     * @param string $legacy_hash The legacy project hash.
     * @return int Feed ID if found, 0 otherwise.
     */
    private function get_feed_by_legacy_hash( $legacy_hash ) {
        $product_feed = new Product_Feed();
        return $product_feed->get_feed_id_by_project_hash( $legacy_hash );
    }

    /**
     * Run the class
     *
     * @since 5.0.3
     * @access public
     */
    public function run() {
        add_action( 'adt_tools_action_import_feeds', array( $this, 'import_feeds' ) );
    }
}
