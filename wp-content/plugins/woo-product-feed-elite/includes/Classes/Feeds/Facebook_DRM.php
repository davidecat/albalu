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
 * Facebook DRM Product Feed class.
 *
 * @since 5.0.5
 */
class Facebook_DRM extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Feed type.
     *
     * @since 5.0.5
     *
     * @var string
     */
    protected $feed_type = 'facebook_drm';

    /**
     * Add extra attributes to the product feed XML.
     * Video attributes are handled differently.
     * We need to add multiple video attributes to the product feed XML.
     *
     * @since 5.0.5
     * @access public
     *
     * @param bool   $handled            If returned true, skip all default processing for this key.
     * @param object $product            The XML element object.
     * @param string $k                  The attribute key/name.
     * @param string $v                  The attribute value.
     * @param array  $feed_config        The feed configuration array.
     * @param array  $channel_attributes The channel attributes array.
     * @param object $feed               The feed object.
     * @return bool If returned true, skip all default processing for this key.
     */
    public function add_extra_attributes_to_product_feed_xml( $handled, $product, $k, $v, $feed_config, $channel_attributes, $feed ) {
        if ( $this->feed_type !== $feed_config['fields'] ) {
            return $handled;
        }

        if ( 'video_url' === $k ) {
            $extra_attributes = get_option( 'adt_extra_attributes', array() );
            foreach ( $extra_attributes as $field_key => $field_label ) {
                $meta_key = str_replace( 'custom_attributes_', '', $field_key );
                if ( '_woosea_video_url' === $meta_key ) {
                    $video_urls = explode( "\n", $v );
                    $video_urls = array_filter( $video_urls, 'trim' );

                    // Add each video URL as a separate <video> element.
                    foreach ( $video_urls as $video_url ) {
                        $video_url = trim( $video_url );
                        if ( ! empty( $video_url ) ) {
                            $product->addChild( 'video', htmlspecialchars( $video_url, ENT_XML1, 'UTF-8' ) );
                        }
                    }
                    $handled = true;
                }
            }
        }
        return $handled;
    }

    /**
     * Handle video attributes CSV header for Facebook DRM.
     * Expand video_url into video[0].url, video[1].url, ..., video[19].url columns.
     *
     * @since 5.0.5
     * @access public
     *
     * @param string $attr             The CSV header row string.
     * @param array  $feed_attributes  The feed attributes configuration.
     * @param object $feed             The feed object.
     * @return string Modified CSV header row.
     */
    public function handle_csv_video_header( $attr, $feed_attributes, $feed ) {
        // Only process if this is a Facebook DRM feed.
        $feed_config = $feed->get_channel();
        if ( empty( $feed_config ) || $this->feed_type !== $feed_config['fields'] ) {
            return $attr;
        }

        // Check if video_url attribute exists in the feed.
        $video_attribute_name = '';
        foreach ( $feed_attributes as $feed_attribute ) {
            if ( isset( $feed_attribute['attribute'] ) && 'video_url' === $feed_attribute['attribute'] ) {
                $video_attribute_name = $feed_attribute['attribute'];
                break;
            }
        }

        // If no video_url attribute found, return unchanged.
        if ( empty( $video_attribute_name ) ) {
            return $attr;
        }

        // Replace the video attribute with video[0].url through video[19].url (20 videos max per Facebook spec).
        // Facebook supports up to 20 video URLs using flattened JSON-path syntax.
        $video_columns = array();
        for ( $i = 0; $i < 20; $i++ ) {
            $video_columns[] = "'video[{$i}].url'";
        }
        $video_columns_str = implode( ',', $video_columns );

        // Replace the video attribute name (could be 'video_url', 'video', or custom name) with the expanded columns.
        $attr = str_replace( "'{$video_attribute_name}'", $video_columns_str, $attr );

        return $attr;
    }

    /**
     * Handle video attributes in CSV data rows for Facebook DRM.
     * Split newline-separated video URLs into separate video[N].url columns.
     *
     * @since 5.0.5
     * @access public
     *
     * @param array  $pieces_row            The CSV row data array.
     * @param array  $old_attributes_config The feed attributes configuration.
     * @param array  $product_data          The product data array.
     * @param object $feed                  The feed object.
     * @return array Modified CSV row data.
     */
    public function handle_csv_video_attributes( $pieces_row, $old_attributes_config, $product_data, $feed ) {
        // Only process if this is a Facebook DRM feed.
        $feed_config = $feed->get_channel();
        if ( empty( $feed_config ) || $this->feed_type !== $feed_config['fields'] ) {
            return $pieces_row;
        }

        // Find the video_url attribute position in the feed configuration.
        $video_position = -1;
        foreach ( $old_attributes_config as $index => $attr_config ) {
            if ( isset( $attr_config['attribute'] ) && 'video_url' === $attr_config['attribute'] ) {
                $video_position = $index;
                break;
            }
        }

        // If video_url attribute found, process it.
        if ( $video_position >= 0 && isset( $pieces_row[ $video_position ] ) ) {
            $video_value = $pieces_row[ $video_position ];

            // Split by newlines and filter out empty values.
            $video_urls = array();
            if ( ! empty( $video_value ) ) {
                $video_urls = explode( "\n", $video_value );
                $video_urls = array_map( 'trim', $video_urls );
                $video_urls = array_filter( $video_urls );
            }

            // Create array of 20 video columns (Facebook supports up to 20 videos).
            $video_columns = array();
            for ( $i = 0; $i < 20; $i++ ) {
                $video_columns[] = isset( $video_urls[ $i ] ) ? $video_urls[ $i ] : '';
            }

            // Remove the original video_url column and insert the 20 video[N].url columns.
            array_splice( $pieces_row, $video_position, 1, $video_columns );
        }

        return $pieces_row;
    }


    /**
     * Run the class.
     *
     * @since 5.0.5
     */
    public function run() {
        add_filter( 'adt_product_feed_xml_attribute_handling', array( $this, 'add_extra_attributes_to_product_feed_xml' ), 10, 7 );
        add_filter( 'adt_product_feed_csv_header', array( $this, 'handle_csv_video_header' ), 10, 3 );
        add_filter( 'adt_product_feed_csv_row_data', array( $this, 'handle_csv_video_attributes' ), 10, 4 );
    }
}
