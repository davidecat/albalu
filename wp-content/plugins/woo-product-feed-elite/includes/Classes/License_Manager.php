<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Helpers\Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * License Manager
 *
 * @since 4.9.5
 */
class License_Manager extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Property that holds the slug of the license settings page.
     *
     * @since 4.9.5
     * @access private
     * @var string
     */
    private $_settings_page_slug;

    /**
     * Property that holds the software key of this plugin.
     *
     * @since 4.9.5
     * @access private
     * @var string $_software_key
     */
    private $_software_key;

    /**
     * Property that holds the activation endpoint.
     *
     * @since 4.9.5
     * @access private
     * @var string
     */
    private $_activation_endpoint;

    /**
     * Property that holds the activation endpoint.
     *
     * @since 4.9.5
     * @access private
     * @var string
     */
    private $_check_endpoint;

    /**
     * License tab ID constant.
     *
     * @since 5.0.7
     */
    const LICENSE_TAB_ID = 'woo-product-feed-elite';

    /**
     * Elite-owned options that should be autoloaded.
     *
     * Each option here is read on every front-end request — by an Integration
     * `is_active()` check, by `Classes/Product_Data_Manipulation::__construct()`,
     * by `Classes/Facebook_CAPI::__construct()`, or at top-level in
     * `bootstrap-old.php` (structured-data fix / VAT). Listed as a single
     * canonical source so the runtime filter (`register_autoloaded_settings`)
     * and the one-shot migration (`Version_5_0_8_Update`) cannot drift.
     *
     * @since 5.0.8
     */
    const AUTOLOADED_FEATURE_OPTIONS = array(
        'adt_structured_data_fix',
        'adt_structured_vat',
        'adt_enable_wpml_support',
        'adt_enable_data_manipulation_support',
        'adt_enable_aelia_support',
        'adt_enable_curcy_support',
        'adt_enable_polylang_support',
        'adt_enable_translatepress_support',
        'adt_enable_facebook_capi',
        'adt_facebook_capi_token',
    );

    /**
     * Subset of AUTOLOADED_FEATURE_OPTIONS that are managed by license
     * activation/deactivation — created on activate, deleted on deactivate
     * by toggle_elite_options(). The remaining options in
     * AUTOLOADED_FEATURE_OPTIONS are user-managed via the settings UI.
     *
     * @since 5.0.8
     */
    const LICENSE_GATED_FEATURE_OPTIONS = array(
        'adt_structured_data_fix',
        'adt_enable_wpml_support',
        'adt_enable_data_manipulation_support',
        'adt_enable_aelia_support',
        'adt_enable_curcy_support',
        'adt_enable_polylang_support',
        'adt_enable_translatepress_support',
    );

    /**
     * Class Methods
     */

    /**
     * License_Manager constructor.
     *
     * @access public
     * @since 4.9.5
     */
    public function __construct() {
        $this->_software_key        = constant( 'ADT_PFE_SLMW_SOFTWARE_KEY' );
        $this->_activation_endpoint = constant( 'ADT_PFE_SLMW_SERVER_URL' ) . '/wp-json/slmw/v1/license/activate';
        $this->_check_endpoint      = constant( 'ADT_PFE_SLMW_SERVER_URL' ) . '/wp-json/slmw/v1/license/check';
        $this->_settings_page_slug  = 'adt_license_settings_page';
    }

    /**
     * Register Visser Labs license settings menu.
     *
     * @access public
     * @since 4.9.5
     */
    public function register_license_settings_menu() {
        if ( ! defined( 'ADT_LICENSE_SETTINGS_PAGE' ) ) {
            if ( ! defined( 'ADT_LICENSE_SETTINGS_DEFAULT_PLUGIN' ) ) {
                define( 'ADT_LICENSE_SETTINGS_DEFAULT_PLUGIN', 'woo-product-feed-elite' );
            }

            $args = array(
                'page_title' => __( 'License', 'woo-product-feed-elite' ),
                'menu_title' => is_multisite() ? __( 'AdTribes License', 'woo-product-feed-elite' ) : __( 'License', 'woo-product-feed-elite' ),
                'capability' => is_multisite() ? 'manage_sites' : 'manage_woocommerce',
                'menu_slug'  => $this->_settings_page_slug,
                'callback'   => array( $this, 'adt_license_settings_page' ),
            );

            if ( is_multisite() ) {
                add_menu_page(
                    $args['page_title'],
                    $args['menu_title'],
                    $args['capability'],
                    $args['menu_slug'],
                    $args['callback']
                );
            } else {
                add_submenu_page(
                    'woo-product-feed',
                    $args['page_title'],
                    $args['menu_title'],
                    $args['capability'],
                    $args['menu_slug'],
                    $args['callback'],
                    40
                );
            }

            define( 'ADT_LICENSE_SETTINGS_PAGE', 'woo-product-feed-elite' );
        }
    }

    /**
     * Load the license settings page.
     *
     * @access public
     * @since 4.9.5
     */
    public function adt_license_settings_page() {
        $tab = filter_input( INPUT_GET, 'tab', FILTER_UNSAFE_RAW ) ?? '';
        $tab = htmlspecialchars( $tab, ENT_QUOTES, 'UTF-8' );
        $tab = empty( $tab ) ? self::LICENSE_TAB_ID : $tab;

        $plugins_tabs = array(
            self::LICENSE_TAB_ID => array(
                'id'     => self::LICENSE_TAB_ID,
                'title'  => __( 'Product Feed Elite', 'woo-product-feed-elite' ),
                'url'    =>
                    is_multisite() ?
                        network_admin_url( 'admin.php?page=' . $this->_settings_page_slug . '&tab=' . self::LICENSE_TAB_ID ) :
                        admin_url( 'options-general.php?page=' . $this->_settings_page_slug . '&tab=' . self::LICENSE_TAB_ID ),
                'active' => self::LICENSE_TAB_ID === $tab ?? true,
            ),
        );

        Helper::locate_admin_template(
            'license-page.php',
            true,
            true,
            array(
                'plugins_tabs' => $plugins_tabs,
            )
        );
    }

    /**
     * Enqueue license settings page scripts and styles.
     *
     * @access public
     * @since 4.9.5
     */
    public function enqueue_scripts() {
        $page = filter_input( INPUT_GET, 'page', FILTER_UNSAFE_RAW ) ?? '';
        $page = htmlspecialchars( $page, ENT_QUOTES, 'UTF-8' );

        if (
            $this->_settings_page_slug === $page ||
            $this->is_show_activate_license_notice()
        ) {
            wp_register_style( 'adt_license_settings-css', ADT_PFE_CSS_URL . 'license-settings.css', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION );
            wp_enqueue_style( 'adt_license_settings-css' );

            wp_enqueue_script( 'adt-license-settings-script', ADT_PFE_JS_URL . 'license-settings.js', array( 'jquery', 'jquery-ui-tabs' ), WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
            wp_localize_script(
                'adt-license-settings-script',
                'adt_license_settings_args',
                array(
                    'i18n' => array(
                        'inactive' => __( 'Inactive', 'woo-product-feed-elite' ),
                        'active'   => __( 'Active', 'woo-product-feed-elite' ),
                        'expired'  => __( 'Expired', 'woo-product-feed-elite' ),
                    ),
                )
            );
        }
    }

    /**
     * Load the license settings page content.
     *
     * @access public
     * @since 4.9.5
     *
     * @param array $plugin_tabs The plugin tabs.
     */
    public function load_page_content( $plugin_tabs ) {
        $tab = filter_input( INPUT_GET, 'tab', FILTER_UNSAFE_RAW ) ?? '';
        $tab = htmlspecialchars( $tab, ENT_QUOTES, 'UTF-8' );

        if ( empty( $tab ) || self::LICENSE_TAB_ID === $tab ) {
            $adt_activation_email          = get_site_option( ADT_PFE_ACTIVATION_EMAIL );
            $adt_license_key               = get_site_option( ADT_PFE_LICENSE_KEY );
            $adt_license_status_value      = isset( get_site_option( ADT_LICENSE_DATA )[ $this->_software_key ]['license_status'] ) ? get_site_option( ADT_LICENSE_DATA )[ $this->_software_key ]['license_status'] : '';
            $adt_license_expired_timestamp = isset( get_site_option( ADT_LICENSE_DATA )[ $this->_software_key ]['expiration_timestamp'] ) ? get_site_option( ADT_LICENSE_DATA )[ $this->_software_key ]['expiration_timestamp'] : '';
            $adt_license_expired_date      = ! empty( $adt_license_expired_timestamp ) ? $this->convert_datetime_to_site_standard_format( $adt_license_expired_timestamp, wc_date_format() ) : '';
            $adt_license_management_url    = isset( get_site_option( ADT_LICENSE_DATA )[ $this->_software_key ]['management_url'] ) ? get_site_option( ADT_LICENSE_DATA )[ $this->_software_key ]['management_url'] : '';
            $adt_license_upgrade_url       = isset( get_site_option( ADT_LICENSE_DATA )[ $this->_software_key ]['upgrade_url'] ) ? get_site_option( ADT_LICENSE_DATA )[ $this->_software_key ]['upgrade_url'] : '';

            $adt_license_status = array(
                'inactive' => array(
                    'text'  => __( 'Inactive', 'woo-product-feed-elite' ),
                    'class' => 'text-color-red',
                ),
                'active'   => array(
                    'text'  => __( 'Active', 'woo-product-feed-elite' ),
                    'class' => 'text-color-green',
                ),
                'expired'  => array(
                    'text'  => __( 'Expired', 'woo-product-feed-elite' ) . ' (' . $adt_license_expired_date . ')',
                    'class' => 'text-color-red',
                ),
            );

            $adt_license_status_i18n       = isset( $adt_license_status[ $adt_license_status_value ] ) ? $adt_license_status[ $adt_license_status_value ]['text'] : $adt_license_status['inactive']['text'];
            $adt_license_status_class_name = isset( $adt_license_status[ $adt_license_status_value ] ) ? $adt_license_status[ $adt_license_status_value ]['class'] : $adt_license_status['inactive']['class'];

            Helper::locate_admin_template(
                'license-settings.php',
                true,
                true,
                array(
                    'plugin_tabs'                   => $plugin_tabs,
                    'adt_license_status_i18n'       => $adt_license_status_i18n,
                    'adt_license_status_class_name' => $adt_license_status_class_name,
                    'adt_activation_email'          => $adt_activation_email,
                    'adt_license_key'               => $adt_license_key,
                    'adt_license_status_value'      => $adt_license_status_value,
                    'adt_license_expired_timestamp' => $adt_license_expired_timestamp,
                    'adt_license_expired_date'      => $adt_license_expired_date,
                    'adt_license_management_url'    => $adt_license_management_url,
                    'adt_license_upgrade_url'       => $adt_license_upgrade_url,
                )
            );
        }
    }

    /**
     * Maybe print debug content.
     *
     * @access public
     * @since 4.9.5
     *
     * @param array $plugin_tabs The plugin tabs.
     */
    public function maybe_print_debug_content( $plugin_tabs ) {
        // Check if debug=1 on the query string.
        if ( isset( $_GET['debug'] ) && '1' === $_GET['debug'] ) { // phpcs:ignore
            // Get license data from DB and print.
            echo '<pre>';
            echo 'Activation Email: ' . print_r( get_site_option( ADT_PFE_ACTIVATION_EMAIL ), true ) . '<br>'; // phpcs:ignore
            echo 'License Key: ' . print_r( get_site_option( ADT_PFE_LICENSE_KEY ), true ) . '<br>'; // phpcs:ignore
            echo 'License Data: ' . print_r( get_site_option( ADT_LICENSE_DATA ), true ) . '<br>'; // phpcs:ignore
            echo '</pre>';
        }
    }

    /**
     * Activate license.
     *
     * @access public
     * @since 4.9.5
     */
    public function activate_license() {
        if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
            $response = array(
                'status'    => 'fail',
                'error_msg' => __( 'Invalid AJAX Operation', 'woo-product-feed-elite' ),
            );
        } elseif ( ! isset( $_POST['activation_email'] ) || ! isset( $_POST['license_key'] ) || ! isset( $_POST['ajax_nonce'] ) ) {
            $response = array(
                'status'    => 'fail',
                'error_msg' => __( 'Required parameters not supplied', 'woo-product-feed-elite' ),
            );
        } elseif ( ! check_ajax_referer( 'adt_pfe_activate_license_nonce', 'ajax_nonce', false ) ) {
            $response = array(
                'status'    => 'fail',
                'error_msg' => __( 'Security check failed', 'woo-product-feed-elite' ),
            );
        } else {
            $activation_email = trim( sanitize_text_field( wp_unslash( $_POST['activation_email'] ?? '' ) ) );
            $license_key      = trim( sanitize_text_field( wp_unslash( $_POST['license_key'] ?? '' ) ) );

            $activation_url = add_query_arg(
                array(
                    'activation_email' => rawurlencode( $activation_email ),
                    'license_key'      => $license_key,
                    'site_url'         => home_url(),
                    'software_key'     => $this->_software_key,
                    'multisite'        => is_multisite() ? 1 : 0,
                ),
                apply_filters( 'adt_license_activation_url', $this->_activation_endpoint )
            );

            // Store data even if not valid license.
            update_site_option( ADT_PFE_LICENSE_KEY, $license_key );
            update_site_option( ADT_PFE_ACTIVATION_EMAIL, $activation_email );

            $args = apply_filters(
                'adt_' . strtolower( $this->_software_key ) . '_license_check_option',
                array(
                    'timeout' => 10, // Seconds.
                    'headers' => array( 'Accept' => 'application/json' ),
                )
            );

            $response = json_decode( wp_remote_retrieve_body( wp_remote_get( $activation_url, $args ) ) );

            // Check for valid response.
            if ( empty( $response ) || ! property_exists( $response, 'status' ) ) {
                $response = array(
                    'status'    => 'fail',
                    'error_msg' => __( 'Failed to connect to license server. Please contact plugin support.', 'woo-product-feed-elite' ),
                );

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
                echo wp_json_encode( $response );
                wp_die();
            }

            // Store data if license email and key are valid.
            if ( 'success' === $response->status ||
                ( 'fail' === $response->status && ( property_exists( $response, 'license_status' ) && 'invalid' !== $response->license_status ) )
            ) {
                update_site_option( constant( 'ADT_' . $this->_software_key . '_ACTIVATION_EMAIL' ), $activation_email );
                update_site_option( constant( 'ADT_' . $this->_software_key . '_LICENSE_KEY' ), $license_key );
            }

            // Update last license check timestamp if response is success.
            if ( property_exists( $response, 'license_status' ) && 'success' === $response->status ) {
                update_site_option( constant( 'ADT_' . $this->_software_key . '_LAST_LICENSE_CHECK' ), time() );
            }

            // Process license response on activation.
            $response = $this->process_license_response( $response, 'activation' );

            // Add expiration timestamp in site standard format.
            if ( is_array( $response ) && ! empty( $response ) && array_key_exists( 'expiration_timestamp', $response ) ) {
                $response['expiration_timestamp_i18n'] = $this->convert_datetime_to_site_standard_format( $response['expiration_timestamp'], wc_date_format() );
            }

            // Fire post activation attempt hook.
            do_action( strtolower( 'adt_' . $this->_software_key ) . '_activate_license', $response, $activation_email, $license_key );

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                // Return AJAX response.
                wp_send_json( $response );
            } else {
                return $response;
            }
        }

        // if license status is not set, then we set it as empty value.
        if ( ! isset( $response['license_status'] ) ) {
            $response['license_status'] = '';
        }

        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
        echo wp_json_encode( $response );
        wp_die();
    }

    /**
     * Check license status and record response.
     *
     * @since 4.9.5
     * @access public
     *
     * @return array The license data.
     */
    public function check_license() {
        // Get license activation email and key.
        $activation_email = get_site_option( constant( 'ADT_' . $this->_software_key . '_ACTIVATION_EMAIL' ) );
        $license_key      = get_site_option( constant( 'ADT_' . $this->_software_key . '_LICENSE_KEY' ) );

        $check_url = add_query_arg(
            array(
                'activation_email' => rawurlencode( $activation_email ),
                'license_key'      => $license_key,
                'site_url'         => home_url(),
                'software_key'     => $this->_software_key,
                'multisite'        => is_multisite() ? 1 : 0,
            ),
            apply_filters( strtolower( $this->_software_key ) . '_license_check_url', $this->_check_endpoint )
        );

        $args = apply_filters(
            'adt_' . strtolower( $this->_software_key ) . '_license_check_option',
            array(
                'timeout' => 10, // Seconds.
                'headers' => array( 'Accept' => 'application/json' ),
            )
        );

        $response = json_decode( wp_remote_retrieve_body( wp_remote_get( $check_url, $args ) ) );

        // Update last license check timestamp if response is success.
        if ( property_exists( $response, 'license_status' ) && 'success' === $response->status ) {
            update_site_option( constant( 'ADT_' . $this->_software_key . '_LAST_LICENSE_CHECK' ), time() );
        }

        // Process license response on check.
        $license_data[ $this->_software_key ] = $this->process_license_response( $response, 'check' );

        // Fire post licence check hook.
        do_action( strtolower( 'ADT_' . $this->_software_key ) . '_after_check_license', $license_data, $activation_email, $license_key );

        return $license_data;
    }

    /**
     * Process license options after activation, check license or update data.
     *
     * @since 4.9.5
     * @access public
     *
     * @param string $response The API response from the activation/check endpoint.
     * @param string $context  The context of the process. Available values are 'activation', 'check' and 'update_data'.
     * @return array The processed license response.
     */
    public function process_license_response( $response, $context = 'check' ) {
        // Activation point failed to send a response.
        if ( empty( $response ) ) {
            $response = array(
                'status'    => 'fail',
                'error_msg' => __( 'Failed to activate license. Failed to connect to license server. Please contact plugin support.', 'woo-product-feed-elite' ),
            );
        } else {
            // Get ADT license data.
            $adt_license_data = (array) get_site_option( ADT_LICENSE_DATA, array() );

            // Store update data if the license status is active.
            if ( 'success' === $response->status && property_exists( $response, 'license_status' ) && 'active' === $response->license_status ) {
                if ( 'update_data' === $context && ! empty( $response->software_update_data ) ) {
                    update_site_option( constant( 'ADT_' . $this->_software_key . '_UPDATE_DATA' ), $response->software_update_data );
                }

                // Disable elite notification.
                $this->toggle_elite_notification( false );

                // Enable Elite options.
                $this->toggle_elite_options( true );
            } else {
                // Remove locally stored update data if the license status is not active.
                if ( 'update_data' === $context && ! empty( $response->software_update_data ) ) {
                    delete_site_option( constant( 'ADT_' . $this->_software_key . '_UPDATE_DATA' ) );
                }

                // Remove any locally stored update data if there are any.
                if ( 'update_data' !== $context ) {
                    $this->_maybe_remove_stored_update_data();
                }

                // Disable elite notification.
                $this->toggle_elite_notification( true );

                // Disable Elite options.
                $this->toggle_elite_options( false );
            }

            /**
             * Store response data to license data if the license status is active, disabled or expired.
             * Otherwise, remove plugin key of license data.
             */
            if ( property_exists( $response, 'license_status' ) && in_array( $response->license_status, array( 'active', 'disabled', 'expired' ), true ) ) {
                /**
                 * Sanitize result before storing the data to the database.
                 * The value of the software_update_data property is an object, therefore skip sanitizing it.
                 */
                $response = array_map(
                    function ( $value ) {
                        if ( is_object( $value ) ) {
                            return $value;
                        }
                        return sanitize_text_field( $value );
                    },
                    (array) $response
                );

                // Update license data.
                $adt_license_data = array_merge(
                    $adt_license_data,
                    array( $this->_software_key => $response )
                );
            } else {
                // Remove plugin key of ADT license data.
                $adt_license_data = array_diff_key( $adt_license_data, array( $this->_software_key => '' ) );

                // Remove license_key and activation_email.
                delete_site_option( constant( 'ADT_' . $this->_software_key . '_LICENSE_KEY' ) );
                delete_site_option( constant( 'ADT_' . $this->_software_key . '_ACTIVATION_EMAIL' ) );

                // Disable Elite options.
                $this->toggle_elite_options( false );
            }

            update_site_option( ADT_LICENSE_DATA, $adt_license_data );
        }

        return $response;
    }

    /**
     * Toggle Elite options.
     *
     * @since 4.9.5
     * @access public
     *
     * @param bool $is_active True if the license is active, false otherwise.
     */
    public function toggle_elite_options( $is_active = false ) {
        // Check if multisite environment.
        $multisite = is_multisite();

        // Autoload=true on activation: each of these flags is read on every front-end
        // request by an Integration class's `is_active()` (Integrations/WPML, Polylang,
        // Translate_Press, Aelia_Currency, Curcy) or by
        // `Classes/Product_Data_Manipulation::__construct()`, and `adt_structured_data_fix`
        // is read at module load in `bootstrap-old.php`. Small scalars + every-request
        // reads → autoload to skip per-request SELECTs.
        foreach ( self::LICENSE_GATED_FEATURE_OPTIONS as $option ) {
            if ( $is_active && $multisite ) {
                update_site_option( $option, 'yes' );
            } elseif ( $is_active ) {
                update_option( $option, 'yes', true );
            } elseif ( $multisite ) {
                delete_site_option( $option );
            } else {
                delete_option( $option );
            }
        }
    }

    /**
     * Toggle Elite notification.
     *
     * This function is used to enable or disable the Elite notification.
     * This is a legacy option and should be rewrite in the future.
     * The option is not supporting multisite environment.
     *
     * @since 4.9.5
     * @access public
     *
     * @param bool $toggle True if the notification is active, false otherwise.
     */
    public function toggle_elite_notification( $toggle = false ) {
        $notification_data = array(
            'show' => $toggle ? 'yes' : 'no',
        );

        update_option( 'woosea_getelite_notification', $notification_data, false );
    }

    /**
     * Remove any locally stored update data if there are any.
     *
     * @since 1.30.4
     * @access private
     */
    private function _maybe_remove_stored_update_data() {
        $wp_site_transient = get_site_transient( 'update_plugins' );

        if ( $wp_site_transient ) {
            $plugin_basename = ADT_PFE_BASENAME;

            if ( isset( $wp_site_transient->checked ) && is_array( $wp_site_transient->checked ) && array_key_exists( $plugin_basename, $wp_site_transient->checked ) ) {
                unset( $wp_site_transient->checked[ $plugin_basename ] );
            }

            if ( isset( $wp_site_transient->response ) && is_array( $wp_site_transient->response ) && array_key_exists( $plugin_basename, $wp_site_transient->response ) ) {
                unset( $wp_site_transient->response[ $plugin_basename ] );
            }

            set_site_transient( 'update_plugins', $wp_site_transient );
            wp_update_plugins();
        }
    }

    /**
     * Get license data.
     *
     * @since 4.9.5
     * @access public
     *
     * @param string $software_key The software key to get license data.
     * @return array The license data.
     */
    public static function get_license_data( $software_key = '' ) {
        $license_data = (array) get_site_option( ADT_LICENSE_DATA, array() );

        if ( ! empty( $software_key && array_key_exists( $software_key, $license_data ) ) ) {
            $license_data = ! empty( $license_data[ $software_key ] ) ? $license_data[ $software_key ] : array();
        }

        return $license_data;
    }

    /**
     * Check if license is activated.
     *
     * @since 4.9.5
     * @access public
     *
     * @return bool True if license is activated, false otherwise.
     */
    public static function is_license_activated() {
        $license_data = self::get_license_data( ADT_PFE_SLMW_SOFTWARE_KEY );

        // Check the license data for license active status.
        return ! empty( $license_data ) && array_key_exists( 'license_status', $license_data ) && 'active' === $license_data['license_status'];
    }

    /**
     * Get last license check.
     *
     * @since 4.9.5
     * @access public
     *
     * @return string The last license check timestamp.
     */
    public function get_last_license_check() {
        return get_site_option( constant( 'ADT_' . $this->_software_key . '_LAST_LICENSE_CHECK' ) );
    }

    /**
     * Check license status.
     * If $interval is set, then check if license is expired and within 14 days or more than 14 days.
     *
     * @since 4.9.5
     * @access public
     *
     * @param string $status              The status to check if license is expired. Available values are 'expired', 'nolicense', 'disabled'. Default is empty string.
     * @param string $interval            The interval to check if license is expired and within 14 days or more than 14 days.
     *                                    Available values are 'within 14 days' and 'more than 14 days'. Default is empty string.
     * @param string $subscription_status The returns to return. Available values are 'boolean' and 'array'. Default is 'boolean'.
     * @return bool True if license status is expired, false otherwise.
     */
    public function is_license_status( $status = '', $interval = '', $subscription_status = '' ) {
        $license_status = false;
        $license_data   = $this->get_license_data( $this->_software_key );

        switch ( $status ) {
            case 'pre_expiry':
                if ( ! empty( $license_data ) && array_key_exists( 'subscription_status', $license_data ) && 'pending-cancel' === $license_data['subscription_status'] ) {
                    // Get expiration timestamp from license data.
                    $expiration_timestamp = $license_data['expiration_timestamp'];
                    $expiration_datetime  = new \WC_DateTime( $expiration_timestamp, new \DateTimeZone( 'UTC' ) );
                    $now                  = new \WC_DateTime( 'now', new \DateTimeZone( 'UTC' ) );
                    $days_before_expiry   = $expiration_datetime->diff( $now )->days;
                    $license_status       = $days_before_expiry < 14;
                }
                break;
            case 'expired':
                if ( ! empty( $license_data ) && array_key_exists( 'license_status', $license_data ) && 'expired' === $license_data['license_status'] ) {
                    // Get expiration timestamp from license data.
                    $expiration_timestamp = $license_data['expiration_timestamp'];
                    $expiration_datetime  = new \WC_DateTime( $expiration_timestamp, new \DateTimeZone( 'UTC' ) );
                    $now                  = new \WC_DateTime( 'now', new \DateTimeZone( 'UTC' ) );

                    // If expiration timestamp is past the current time, then return true.
                    $license_status = $expiration_datetime < $now;

                    if ( ! empty( $interval ) && $license_status ) {
                        $days_after_expiry = $expiration_datetime->diff( $now )->days;

                        if ( 'within 14 days' === $interval ) {
                            // If license is expired and days after expiry is less than or equal to 14 days, then return true.
                            $license_status = $days_after_expiry < 14;
                        } elseif ( 'more than 14 days' === $interval ) {
                            // If license is expired and days after expiry is more than 14 days, then return true.
                            $license_status = $days_after_expiry >= 14;
                        }
                    }
                }
                break;
            case 'nolicense':
                $license_email  = get_site_option( constant( 'ADT_' . $this->_software_key . '_ACTIVATION_EMAIL' ) );
                $license_key    = get_site_option( constant( 'ADT_' . $this->_software_key . '_LICENSE_KEY' ) );
                $license_status = empty( $license_email ) || empty( $license_key ) ?? true;

                if ( ! empty( $interval ) && $license_status ) {
                    // Get plugin installed date.
                    $last_check_datestring = gmdate( 'Y-m-d H:i:s', $this->get_last_license_check() );

                    if ( ! empty( $last_check_datestring ) ) {
                        $last_check_datetime = new \WC_DateTime( $last_check_datestring, new \DateTimeZone( 'UTC' ) );
                        $now                 = new \WC_DateTime( 'now', new \DateTimeZone( 'UTC' ) );
                        $days_after_check    = $last_check_datetime->diff( $now )->days;

                        if ( 'more than 3 days' === $interval ) {
                            // If license is no license and days after expiry is more than 3 days, then return true.
                            $license_status = $days_after_check >= 3;
                        } elseif ( 'within 7 days' === $interval ) {
                            // If license is no license and days after expiry is less than or equal to 7 days, then return true.
                            $license_status = $days_after_check < 7;
                        } elseif ( 'within 7-14 days' === $interval ) {
                            // If license is no license and days after expiry is more than 7 days and less than or equal to 14 days, then return true.
                            $license_status = $days_after_check >= 7 && $days_after_check < 14;
                        } elseif ( 'more than 14 days' === $interval ) {
                            // If license is no license and days after expiry is more than 14 days, then return true.
                            $license_status = $days_after_check >= 14;
                        }
                    }
                }
                break;
            case 'disabled':
                if ( ! empty( $license_data ) && array_key_exists( 'license_status', $license_data ) && 'disabled' === $license_data['license_status'] ) {
                    $license_status = true;

                    if ( '' !== $subscription_status && $license_data['subscription_status'] !== $subscription_status ) {
                        $license_status = false;
                    }

                    if ( ! empty( $interval ) && $license_status ) {
                        // Get plugin installed date.
                        $last_check_datestring = gmdate( 'Y-m-d H:i:s', $this->get_last_license_check() );

                        if ( ! empty( $last_check_datestring ) ) {
                            $last_check_datetime = new \WC_DateTime( $last_check_datestring, new \DateTimeZone( 'UTC' ) );
                            $now                 = new \WC_DateTime( 'now', new \DateTimeZone( 'UTC' ) );
                            $days_after_check    = $last_check_datetime->diff( $now )->days;

                            if ( 'within 7 days' === $interval ) {
                                // If license is disabled and days after expiry is less than or equal to 7 days, then return true.
                                $license_status = $days_after_check < 7;
                            } elseif ( 'more than 7 days' === $interval ) {
                                // If license is disabled and days after expiry is more than 7 days, then return true.
                                $license_status = $days_after_check >= 7;
                            }
                        }
                    }
                }
                break;
        }

        return $license_status;
    }

    /**
     * Activate license notice.
     *
     * @since 4.9.5
     * @access public
     */
    public function activate_license_notice() {
        if ( $this->is_show_activate_license_notice() ) {
            // Use PRO's unified license page if available, otherwise fall back to standalone.
            $page_slug = $this->_settings_page_slug;

            if ( is_multisite() ) {
                $adt_slmw_settings_url = network_admin_url( 'admin.php?page=' . $page_slug . '&tab=' . self::LICENSE_TAB_ID );
            } else {
                $adt_slmw_settings_url = admin_url( 'admin.php?page=' . $page_slug . '&tab=' . self::LICENSE_TAB_ID );
            }

            Helper::locate_admin_template(
                'notices/activate-license-notice.php',
                true,
                false,
                array(
                    'adt_slmw_settings_url' => $adt_slmw_settings_url,
                )
            );
        }
    }

    /**
     * Check if activate license notice should be shown.
     *
     * @since 4.9.5
     * @access private
     *
     * @return bool True if activate license notice should be shown, false otherwise.
     */
    private function is_show_activate_license_notice() {
        $notice_dismissed    = get_site_option( ADT_PFE_SLMW_ACTIVATE_NOTICE_DISMISSED );
        $license_activated   = $this->is_license_activated();
        $has_license_data    = ! empty( $this->get_license_data( $this->_software_key ) );
        $is_pfe_page         = isset( $_GET['page'] ) && // phpcs:ignore
                                ( str_contains( $_GET['page'], 'woosea_elite' ) || // phpcs:ignore
                                str_contains( $_GET['page'], 'woo-product-feed-elite' ) ); // phpcs:ignore
        $is_adt_license_page = isset( $_GET['page'] ) && str_contains( $_GET['page'], 'adt_license_settings_page' ); // phpcs:ignore

        return current_user_can( 'manage_woocommerce' ) &&
            ! $is_adt_license_page &&
            ! $has_license_data &&
            (
                ( $is_pfe_page && ! $license_activated ) ||
                ( ! $license_activated && 'yes' !== $notice_dismissed )
            );
    }

    /**
     * AJAX dismiss activate notice.
     *
     * @since 4.9.5
     * @access public
     */
    public function ajax_dismiss_activate_notice() {
        if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
            $response = array(
                'status'    => 'fail',
                'error_msg' => __( 'Invalid AJAX Operation', 'woo-product-feed-elite' ),
            );
        } else {
            update_site_option( ADT_PFE_SLMW_ACTIVATE_NOTICE_DISMISSED, 'yes' );
            $response = array( 'status' => 'success' );
        }

        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) ); // phpcs:ignore
        echo wp_json_encode( $response );
        wp_die();
    }

    /**
     * Reset license activation notice to show again if not done yet.
     */
    public function reset_license_activate_notice() {
        if ( ! $this->is_license_activated() ) {
            update_site_option( ADT_PFE_SLMW_ACTIVATE_NOTICE_DISMISSED, 'no' );
        }
    }

    /**
     * Get datetime with site timezone.
     *
     * @since 1.30.4
     * @access public
     *
     * @param string $datetime Datetime string.
     * @param string $interval Datetime interval.
     *
     * @return \WC_DateTime Datetime object.
     */
    public function get_datetime_with_site_timezone( $datetime, $interval = '' ) {
        $datetime = new \WC_DateTime( $datetime, new \DateTimeZone( 'UTC' ) );
        $datetime->setTimezone( new \DateTimeZone( wc_timezone_string() ) );
        if ( ! empty( $interval ) ) {
            $interval = \DateInterval::createFromDateString( $interval );
            $datetime->add( $interval ); // Add interval if needed.
        }

        return $datetime;
    }

    /**
     * Convert datetime to site standard format.
     * 1. Datetime must use site timezone.
     * 2. Datetime must use site default datetime format.
     * 3. Datetime must be localize using i18n.
     *
     * @since 1.30.4
     *
     * @param string $datetime Datetime string.
     * @param string $format   Datetime format.
     * @param string $interval Datetime interval.
     *
     * @return string Datetime in site standard format.
     */
    public function convert_datetime_to_site_standard_format( $datetime, $format = '', $interval = '' ) {
        $standard = self::get_datetime_with_site_timezone( $datetime, $interval ); // Convert to site timezone.
        $format   = ! empty( $format ) ? $format : self::get_default_datetime_format();
        return $standard->date_i18n( $format ); // Convert to site default datetime format and localize.
    }

    /**
     * Register license action scheduler.
     *
     * @since 4.9.5
     * @access public
     */
    public function register_action_scheduler() {
        // Register action scheduler on a random timing to handle license check every 24 hours.
        if ( ! as_next_scheduled_action( 'adt_pfe_license_check' ) ) {
            as_schedule_recurring_action( time() + wp_rand( 0, 86400 ), 86400, 'adt_pfe_license_check', array(), 'adt_pfe', true, 10 );
        }

        // Register action scheduler nightly to re-show activate license notice if not done yet.
        if ( ! as_next_scheduled_action( 'adt_pfe_reset_license_activate_notice' ) ) {
            if ( ! $this->is_license_activated() ) {
                as_schedule_single_action( strtotime( 'tomorrow midnight' ), 'adt_pfe_reset_license_activate_notice', array(), 'adt_pfe' );
            } else {
                as_unschedule_all_actions( 'adt_pfe_reset_license_activate_notice' );
            }
        }
    }

    /**
     * Register the Elite license tab into PRO's extensible license page.
     *
     * @since 5.0.0
     *
     * @param array  $tabs     The registered tabs array.
     * @param string $base_url The base URL for building tab URLs.
     * @return array
     */
    public function register_license_tab( $tabs, $base_url ) {
        $tabs[ self::LICENSE_TAB_ID ] = array(
            'id'       => self::LICENSE_TAB_ID,
            'title'    => __( 'Product Feed Elite', 'woo-product-feed-elite' ),
            'url'      => add_query_arg( 'tab', self::LICENSE_TAB_ID, $base_url ),
            'priority' => 10,
        );

        return $tabs;
    }

    /**
     * Render the Elite license tab content on PRO's license page.
     *
     * @since 5.0.0
     *
     * @param string $current_tab The currently active tab ID.
     * @param array  $tabs        All registered tabs.
     */
    public function render_license_content( $current_tab, $tabs ) {
        if ( self::LICENSE_TAB_ID !== $current_tab ) {
            return;
        }

        $this->load_page_content( $tabs );
    }

    /**
     * Execute model.
     *
     * @since 4.9.5
     * @access public
     */
    public function run() {
        // Register into PRO's extensible license page.
        add_filter( 'adt_license_page_tabs', array( $this, 'register_license_tab' ), 10, 2 );
        add_action( 'adt_license_page_content', array( $this, 'render_license_content' ), 10, 2 );

        // License activation notice (always active regardless of path).
        if ( is_multisite() && get_current_blog_id() === 1 ) {
            add_action( 'network_admin_notices', array( $this, 'activate_license_notice' ) );
        } elseif ( ! is_multisite() ) {
            add_action( 'admin_notices', array( $this, 'activate_license_notice' ) );
        }

        // Activate license.
        add_action( 'wp_ajax_adt_activate_license', array( $this, 'activate_license' ) );

        // Dismiss activate license notice.
        add_action( 'wp_ajax_adt_slmw_dismiss_activate_notice', array( $this, 'ajax_dismiss_activate_notice' ) );

        // Recurring event to check license status.
        add_action( 'adt_pfe_license_check', array( $this, 'check_license' ), 10 );

        // Recurring event to reset license activate notice.
        add_action( 'adt_pfe_reset_license_activate_notice', array( $this, 'reset_license_activate_notice' ), 10 );

        // Tell PRO's AJAX settings updater which Elite-owned options should be
        // autoloaded so the admin "save setting" path produces the right flag.
        add_filter( 'adt_pfp_autoloaded_ajax_settings', array( $this, 'register_autoloaded_settings' ) );
    }

    /**
     * Register Elite-owned autoloaded settings into PRO's allowlist.
     *
     * Hooks into PRO's `adt_pfp_autoloaded_ajax_settings` filter so the
     * AJAX settings updater (which is owned by PRO) saves Elite's
     * license-gated feature toggles with `autoload = true`. These flags
     * are read on every front-end request by Integration `is_active()`
     * checks and other per-request consumers, so autoload avoids per-
     * request `wp_options` SELECTs.
     *
     * @since 5.0.8
     * @access public
     *
     * @param array $autoloaded Existing list of autoloaded option names.
     * @return array Modified list.
     */
    public function register_autoloaded_settings( $autoloaded ) {
        if ( ! is_array( $autoloaded ) ) {
            $autoloaded = array();
        }

        return array_merge( $autoloaded, self::AUTOLOADED_FEATURE_OPTIONS );
    }
}
