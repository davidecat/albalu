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
 * @since 4.9.5
 */
class Helper {

    /**
     * Get plugin data.
     *
     * @since 4.9.5
     * @access public
     *
     * @param string|null $key       The plugin data key.
     * @param bool        $markup    If the returned data should have HTML markup applied. Default false.
     * @param bool        $translate If the returned data should be translated. Default false.
     * @return string[]|string
     */
    public static function get_plugin_data( $key = null, $markup = false, $translate = false ) {

        $plugin_data = get_plugin_data( ADT_PFE_PLUGIN_FILE, $markup, $translate );

        if ( null !== $key ) {
            return $plugin_data[ $key ] ?? '';
        }

        return $plugin_data;
    }

    /**
     * Get the current plugin version.
     *
     * @since 4.9.5
     * @access public
     *
     * @param bool $markup        Optional. If the returned data should have HTML markup applied.
     *                            Default true.
     * @param bool $translate     Optional. If the returned data should be translated. Default true.
     * @return string
     */
    public static function get_plugin_version( $markup = true, $translate = true ) {

        return self::get_plugin_data( 'Version', $markup, $translate );
    }

    /**
     * Utility function that determines if a plugin is installed or not.
     *
     * @since 5.0.3
     * @access public
     *
     * @param string $plugin_basename Plugin base name. Ex. woocommerce/woocommerce.php.
     * @return boolean True if installed, false otherwise.
     */
    public static function is_plugin_installed( $plugin_basename ) {
        $plugin_file_path = trailingslashit( WP_PLUGIN_DIR ) . plugin_basename( $plugin_basename );
        return file_exists( $plugin_file_path );
    }

    /**
     * Loads admin template.
     *
     * @since 4.9.5
     * @access public
     *
     * @param string $name Template name relative to `templates` directory.
     * @param bool   $load Whether to load the template or not.
     * @param bool   $once Whether to use require_once or require.
     * @return string
     */
    public static function load_template( $name, $load = false, $once = true ) {

        //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        $template = ADT_PFE_PLUGIN_DIR_PATH . 'templates/' . rtrim( $name, '.php' ) . '.php';
        if ( ! file_exists( $template ) ) {
            return '';
        }

        if ( $load ) {
            if ( $once ) {
                require_once $template;
            } else {
                require $template;
            }
        }

        //phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
        return $template;
    }

    /**
     * Utility function that determines if the current page is a Product Feed Pro or Elite page or not.
     *
     * @since 4.9.7
     * @access public
     *
     * @return boolean True if Product Feed Pro or Elite, false otherwise.
     */
    public static function is_plugin_page() {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        $plugin_page_slugs = array(
            '_page_woo-product-feed',
            '_page_adt-edit-feed',
            '_page_woosea_manage_settings',
            '_page_adt_license_settings_page',
            '_page_pfp-help-page',
            '_page_pfp-about-page',
            '_page_upgrade-to-elite',
            '_page_adt_feed_statistics',
        );

        $is_plugin_page = false;
        foreach ( $plugin_page_slugs as $slug ) {
            if ( strpos( $screen->id, $slug ) !== false ) {
                $is_plugin_page = true;
                break;
            }
        }

        return apply_filters( 'adt_is_plugin_page', $is_plugin_page );
    }

    /**
     * Check if the user is allowed to manage product feed.
     *
     * @since 4.9.9
     * @access private
     *
     * @return bool
     */
    public static function is_current_user_allowed() {
        $user          = wp_get_current_user();
        $allowed_roles = apply_filters( 'adt_manage_product_feed_allowed_roles', array() );
        if ( current_user_can( 'manage_adtribes_product_feeds' ) || array_intersect( $allowed_roles, $user->roles ) ) {
            return true;
        }
        return false;
    }

    /**
     * Get the URL with UTM parameters.
     *
     * @param string $url_path     URL path from main.
     * @param string $utm_source   UTM source.
     * @param string $utm_medium   UTM medium.
     * @param string $utm_campaign UTM campaign.
     * @param string $site_url     URL - defaults to `https://adtribes.io/`.
     *
     * @since 4.9.9
     * @return string
     */
    public static function get_utm_url( $url_path = '', $utm_source = 'pfp', $utm_medium = 'action', $utm_campaign = 'default', $site_url = 'https://adtribes.io/' ) {

        $utm_content = get_option( 'pfp_installed_by', false );
        $url         = trailingslashit( $site_url ) . $url_path;

        return add_query_arg(
            array(
                'utm_source'   => $utm_source,
                'utm_medium'   => $utm_medium,
                'utm_campaign' => $utm_campaign,
                'utm_content'  => $utm_content,
            ),
            trailingslashit( $url )
        );
    }

    /**
     * Loads admin template.
     *
     * @since 5.0.1
     *
     * @param string $name Template name relative to `templates/admin` directory.
     * @param bool   $load Whether to load the template or not.
     * @param bool   $once Whether to use require_once or require.
     * @param array  $vars Variables to pass to the template.
     * @return string
     */
    public static function locate_admin_template( $name, $load = false, $once = true, $vars = array() ) {

        //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        $template = ADT_PFE_PLUGIN_DIR_PATH . 'templates/' . $name;
        if ( ! file_exists( $template ) ) {
            return '';
        }

        if ( ! empty( $vars ) ) {
            extract( $vars ); // phpcs:ignore WordPress.PHP.DontExtract
        }

        if ( $load ) {
            if ( $once ) {
                require_once $template;
            } else {
                require $template;
            }
        }

        //phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
        return $template;
    }

    /**
     * Sanitize an array recursively.
     *
     * @since 5.0.1
     * @param array $array_data The array to sanitize.
     * @return array
     */
    public static function sanitize_array( $array_data ) {
        if ( ! is_array( $array_data ) ) {
            return sanitize_text_field( $array_data );
        }

        foreach ( $array_data as $key => $value ) {
            if ( is_array( $value ) ) {
                $array_data[ $key ] = self::sanitize_array( $value );
            } else {
                $array_data[ $key ] = sanitize_text_field( $value );
            }
        }

        return $array_data;
    }

    /**
     * Convert local datetime to UTC for storage.
     *
     * @since 5.0.3
     * @access private
     *
     * @param string $local_datetime The local datetime string.
     * @return string The UTC datetime string, or empty string if invalid.
     */
    public static function convert_local_datetime_to_utc( $local_datetime ) {
        if ( empty( $local_datetime ) ) {
            return '';
        }

        // Get WordPress timezone.
        $wp_timezone = wp_timezone();

        try {
            // Create DateTime object in site timezone.
            $local_dt = new \DateTime( $local_datetime, $wp_timezone );

            // Convert to UTC.
            $local_dt->setTimezone( new \DateTimeZone( 'UTC' ) );

            // Return in Y-m-d H:i:s format.
            return $local_dt->format( 'Y-m-d H:i:s' );
        } catch ( \Exception $e ) {
            // Invalid datetime format.
            return '';
        }
    }

    /**
     * Convert UTC datetime to local datetime for display.
     *
     * @since 5.0.3
     * @access public
     *
     * @param string $utc_datetime The UTC datetime string.
     * @return string The local datetime string, or empty string if invalid.
     */
    public static function convert_utc_datetime_to_local( $utc_datetime ) {
        if ( empty( $utc_datetime ) ) {
            return '';
        }

        // Get WordPress timezone.
        $wp_timezone = wp_timezone();

        try {
            // Create DateTime object in UTC.
            $utc_dt = new \DateTime( $utc_datetime, new \DateTimeZone( 'UTC' ) );

            // Convert to site timezone.
            $utc_dt->setTimezone( $wp_timezone );

            // Return in Y-m-d H:i:s format.
            return $utc_dt->format( 'Y-m-d H:i:s' );
        } catch ( \Exception $e ) {
            // Invalid datetime format.
            return '';
        }
    }
}
