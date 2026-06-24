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
 * Addon Manager
 *
 * Implements the client side of the three-step SLMW addon flow:
 *   1. Scheduled addon/check (every 14 days via Action Scheduler)
 *   2. AJAX addon/download -- mints a one-time token on the SLMW server
 *   3. Plugin_Upgrader install from the addon/serve token URL
 *
 * @since 5.0.7
 */
class Addon_Manager extends Abstract_Class {

    use Singleton_Trait;

    /**
     * SLMW addon/check endpoint URL.
     *
     * @since 5.0.7
     * @access private
     * @var string
     */
    private $_addon_check_endpoint;

    /**
     * SLMW addon/download endpoint URL.
     *
     * @since 5.0.7
     * @access private
     * @var string
     */
    private $_addon_download_endpoint;

    /**
     * Constructor.
     *
     * @since 5.0.7
     * @access public
     */
    public function __construct() {
        $server_url                     = constant( 'ADT_PFE_SLMW_SERVER_URL' );
        $this->_addon_check_endpoint    = $server_url . '/wp-json/slmw/v1/addon/check';
        $this->_addon_download_endpoint = $server_url . '/wp-json/slmw/v1/addon/download';
    }

    /**
     * Call the SLMW addon/check endpoint and cache the result.
     *
     * Triggered by the `adt_pfe_addon_check` Action Scheduler recurring action
     * every 14 days. Bails early when the parent license is not active.
     *
     * @since 5.0.7
     * @access public
     */
    public function check_addons() {
        if ( ! License_Manager::is_license_activated() ) {
            return;
        }

        $license_key      = get_site_option( ADT_PFE_LICENSE_KEY );
        $activation_email = get_site_option( ADT_PFE_ACTIVATION_EMAIL );

        $response = wp_remote_post(
            $this->_addon_check_endpoint,
            array(
                'timeout' => 15,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ),
                'body'    => wp_json_encode(
                    array(
                        'license_key'      => $license_key,
                        'activation_email' => $activation_email,
                        'software_key'     => ADT_PFE_SLMW_SOFTWARE_KEY,
                        'site_url'         => home_url(),
                    )
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['success'] ) || empty( $body['addons'] ) ) {
            delete_site_option( ADT_PFE_ADDON_CHECK_DATA );
            return;
        }

        update_site_option( ADT_PFE_ADDON_CHECK_DATA, $body['addons'] );

        /**
         * Fires after the SLMW addon/check result is stored.
         *
         * @since 5.0.8
         * @param array $addons The addons array returned by the SLMW server.
         */
        do_action( 'adt_pfe_addon_check_complete', $body['addons'] );
    }

    /**
     * Return addons from the last check that are not yet installed or dismissed.
     *
     * Also self-heals the installed-addons record when a plugin file has been
     * removed from disk outside of this manager.
     *
     * @since 5.0.7
     * @access private
     *
     * @return array
     */
    private function _get_pending_addons() {
        $addons           = (array) get_site_option( ADT_PFE_ADDON_CHECK_DATA, array() );
        $installed_addons = (array) get_site_option( ADT_PFE_INSTALLED_ADDONS, array() );
        $dismissed        = (array) get_option( 'adt_pfe_dismissed_addons', array() );
        $pending          = array();

        foreach ( $addons as $addon ) {
            $software_key = $addon['software_key'] ?? '';
            if ( empty( $software_key ) ) {
                continue;
            }

            // Skip if already installed (with self-healing for deleted plugin files).
            if ( isset( $installed_addons[ $software_key ] ) ) {
                $plugin_file = WP_PLUGIN_DIR . '/' . $installed_addons[ $software_key ];
                if ( file_exists( $plugin_file ) ) {
                    continue;
                }
                // Plugin file gone -- clear the stale record so the notice reappears.
                unset( $installed_addons[ $software_key ] );
                update_site_option( ADT_PFE_INSTALLED_ADDONS, $installed_addons );

                // Reset the per-addon notice option so PRO shows it again.
                $option_key = 'adt_show_addon_' . strtolower( sanitize_key( $software_key ) ) . '_notice';
                update_option( $option_key, 'yes', false );

                // Remove from dismissed list.
                $dismissed = array_diff( $dismissed, array( strtolower( $software_key ) ) );
                update_option( 'adt_pfe_dismissed_addons', array_values( $dismissed ), false );
            }

            // Skip if user has dismissed this addon's notice.
            if ( in_array( strtolower( $software_key ), $dismissed, true ) ) {
                continue;
            }

            $pending[] = $addon;
        }

        return $pending;
    }

    /**
     * Add addon notices to the PRO plugin's notice system.
     *
     * Hooks into `adt_pfp_cron_notices_data` so that notices are injected
     * into the processing pipeline and get nonces, metadata, and timestamps
     * applied automatically by the PRO notice system.
     *
     * @since 5.0.7
     * @access public
     *
     * @param array $notices Existing notice definitions.
     * @return array Modified notice definitions.
     */
    public function add_addon_notices( $notices ) {
        $pending = $this->_get_pending_addons();
        if ( empty( $pending ) ) {
            return $notices;
        }

        foreach ( $pending as $addon ) {
            $software_key = $addon['software_key'] ?? '';
            $product_name = $addon['product_name'] ?? '';
            $version      = $addon['version'] ?? '';
            $image_url    = $addon['product_image_url'] ?? '';

            if ( empty( $software_key ) ) {
                continue;
            }

            /**
             * Filter to allow specific addon software keys to be excluded from
             * the generic addon install notice. Use this when another notice
             * already handles a particular addon's install flow.
             *
             * @since 5.0.8
             * @param string[] $skip_keys Lowercase software keys to skip.
             */
            $skip_keys = apply_filters( 'adt_pfe_addon_notices_skip_keys', array() );
            if ( in_array( strtolower( $software_key ), $skip_keys, true ) ) {
                continue;
            }

            $notice_key = 'addon_install_' . sanitize_key( $software_key );
            $option_key = 'adt_show_addon_' . strtolower( sanitize_key( $software_key ) ) . '_notice';

            // Ensure the option is set to 'yes' so PRO's get_all_admin_notices() includes it.
            if ( '' === get_option( $option_key, '' ) ) {
                update_option( $option_key, 'yes', false );
            }

            $notices[ $notice_key ] = array(
                'notice_type'     => 'addon_install',
                'option'          => $option_key,
                'skip_cron'       => true,
                'is_dismissible'  => true,
                'show_admin_wide' => true,
                'type'            => 'info',
                'icon'            => 'package',
                'image_url'       => $image_url,
                'message'         => array(
                    '<h3>' . sprintf(
                        /* translators: %s: addon product name */
                        __( '%s Available', 'woo-product-feed-elite' ),
                        esc_html( $product_name )
                    ) . '</h3>',
                    '<p>' . sprintf(
                        /* translators: 1: addon name, 2: version */
                        __( 'Your license includes %1$s (v%2$s). Install it now to unlock additional features.', 'woo-product-feed-elite' ),
                        '<strong>' . esc_html( $product_name ) . '</strong>',
                        esc_html( $version )
                    ) . '</p>',
                ),
                'is_html'         => true,
                'actions'         => array(
                    array(
                        'type'         => 'primary',
                        'class'        => 'adt-pfe-install-addon',
                        'nonce_action' => 'adt_pfe_install_addon_nonce',
                        'data'         => array( 'addon_software_key' => $software_key ),
                        'text'         => __( 'Install Now', 'woo-product-feed-elite' ),
                    ),
                ),
            );
        }

        return $notices;
    }

    /**
     * Handle addon notice dismissal from the PRO notice system.
     *
     * Listens to PRO's `adt_pfp_before_dismiss_admin_notice` to persistently
     * track which addons have been dismissed so they don't reappear after
     * the next addon check.
     *
     * @since 5.0.7
     * @access public
     *
     * @param string $notice_key The notice key being dismissed.
     * @param string $response   The response ('dismissed' or 'snooze').
     */
    public function handle_addon_dismiss( $notice_key, $response ) {
        // Only handle addon notices.
        if ( ! str_starts_with( $notice_key, 'addon_install_' ) ) {
            return;
        }

        if ( 'dismissed' !== $response ) {
            return;
        }

        $software_key = strtolower( str_replace( 'addon_install_', '', $notice_key ) );
        $dismissed    = (array) get_option( 'adt_pfe_dismissed_addons', array() );

        if ( ! in_array( $software_key, $dismissed, true ) ) {
            $dismissed[] = $software_key;
            update_option( 'adt_pfe_dismissed_addons', $dismissed, false );
        }
    }

    /**
     * Enqueue the addon install handler script.
     *
     * @since 5.0.7
     * @access public
     */
    public function enqueue_addon_scripts() {
        if ( ! current_user_can( 'install_plugins' ) ) {
            return;
        }

        // Only load on screens where addon notices are displayed.
        $screen = get_current_screen();
        if ( $screen && ! Helper::is_plugin_page() && ! in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) ) {
            return;
        }

        $pending = $this->_get_pending_addons();

        /**
         * Filter to force-enqueue the addon install JS even when there are no
         * pending addons from the SLMW check. Use this when a custom notice
         * shows an install button for a specific addon.
         *
         * @since 5.0.8
         * @param bool $force Whether to force-enqueue the scripts.
         */
        $force_enqueue = apply_filters( 'adt_pfe_force_enqueue_addon_scripts', false );

        if ( empty( $pending ) && ! $force_enqueue ) {
            return;
        }

        $vite = new \AdTribes\PFE\Factories\Vite_App(
            'adt-pfe-addon-notices',
            'src/vanilla/addon-notices/index.ts',
            array( 'jquery', 'wp-i18n', 'adt-pfp-notices' ),
        );
        $vite->enqueue();
    }

    /**
     * AJAX handler: silently install and activate an addon.
     *
     * Executes steps 2 (addon/download) and 3 (Plugin_Upgrader from addon/serve
     * token URL) of the three-step flow described in the SLMW Addon API skill.
     *
     * @since 5.0.7
     * @access public
     */
    public function ajax_install_addon() {
        if ( ! check_ajax_referer( 'adt_pfe_install_addon_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'woo-product-feed-elite' ) ) );
        }

        if ( ! current_user_can( 'install_plugins' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'woo-product-feed-elite' ) ) );
        }

        $software_key = sanitize_text_field( wp_unslash( $_POST['software_key'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( empty( $software_key ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing addon identifier.', 'woo-product-feed-elite' ) ) );
        }

        $activation_email = get_site_option( ADT_PFE_ACTIVATION_EMAIL );

        $addon_data = get_site_option( ADT_PFE_ADDON_CHECK_DATA, array() );
        foreach ( $addon_data as $addon ) {
            if ( $addon['software_key'] === $software_key ) {
                $license_key = $addon['license_key'];
                break;
            }
        }

        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing license key.', 'woo-product-feed-elite' ) ) );
        }

        // Step 2 -- obtain a one-time download token from the SLMW server.
        $response = wp_remote_post(
            $this->_addon_download_endpoint,
            array(
                'timeout' => 15,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ),
                'body'    => wp_json_encode(
                    array(
                        'license_key'      => $license_key,
                        'activation_email' => $activation_email,
                        'software_key'     => $software_key,
                        'site_url'         => home_url(),
                    )
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['success'] ) || empty( $body['download_url'] ) ) {
            $message = $body['message'] ?? __( 'Failed to retrieve addon download URL.', 'woo-product-feed-elite' );
            wp_send_json_error( array( 'message' => $message ) );
        }

        // Step 3 -- install from the addon/serve token URL (single-use, 5 min TTL).
        $result = $this->_install_plugin_from_url( $body['download_url'] );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        $installed_addons                  = (array) get_site_option( ADT_PFE_INSTALLED_ADDONS, array() );
        $installed_addons[ $software_key ] = $result;
        update_site_option( ADT_PFE_INSTALLED_ADDONS, $installed_addons );

        // Auto-fill license credentials and run the full activation flow so the
        // addon's license is live immediately without the user entering anything.
        $this->_activate_addon_license( $software_key, $license_key, $activation_email );

        // Mark the per-addon notice as dismissed after successful install.
        $option_key = 'adt_show_addon_' . strtolower( sanitize_key( $software_key ) ) . '_notice';
        update_option( $option_key, 'dismissed', false );

        wp_send_json_success(
            array( 'message' => __( 'Addon installed and activated successfully.', 'woo-product-feed-elite' ) )
        );
    }

    /**
     * Download and silently install + activate a plugin from a ZIP URL.
     *
     * We download the package ourselves with _download_to_temp() rather than
     * relying on Plugin_Upgrader::install( $url ), which internally calls
     * download_url() -> wp_safe_remote_get() with reject_unsafe_urls => true.
     * That triggers wp_http_validate_url(), which resolves the hostname and
     * returns false for any private/loopback IP (e.g. *.local dev domains),
     * causing WP_Upgrader::run() to exit before setting $this->result, so
     * Plugin_Upgrader::install() returns null.
     *
     * Passing a local temp file path to install() bypasses that check entirely.
     *
     * @since 5.0.7
     * @access private
     *
     * @param string $url The token-protected ZIP URL (addon/serve endpoint).
     * @return string|\WP_Error Plugin basename on success, WP_Error on failure.
     */
    private function _install_plugin_from_url( $url ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        WP_Filesystem();

        $tmp_file = $this->_download_to_temp( $url );
        if ( is_wp_error( $tmp_file ) ) {
            return $tmp_file;
        }

        $skin     = new \WP_Ajax_Upgrader_Skin();
        $upgrader = new \Plugin_Upgrader( $skin );
        $result   = $upgrader->install( $tmp_file );

        // WP passes a local file to unpack_package() with $delete_package = false,
        // so we are responsible for cleaning up the temp file.
        wp_delete_file( $tmp_file );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // install() returns $this->result when falsy -- which can be null/false if
        // run() exited early (filesystem error, unpack failure, etc.).
        // Prefer the skin error message when one is available.
        if ( ! $result ) {
            $skin_errors = $skin->get_errors();
            if ( $skin_errors->has_errors() ) {
                return $skin_errors;
            }
            return new \WP_Error( 'install_failed', __( 'Plugin installation failed.', 'woo-product-feed-elite' ) );
        }

        $plugin_basename = $upgrader->plugin_info();
        if ( ! $plugin_basename ) {
            return new \WP_Error( 'plugin_info_failed', __( 'Could not determine installed plugin file.', 'woo-product-feed-elite' ) );
        }

        $activation = activate_plugin( $plugin_basename );
        if ( is_wp_error( $activation ) ) {
            return $activation;
        }

        return $plugin_basename;
    }

    /**
     * Stream a remote ZIP to a local temp file using wp_remote_get().
     *
     * Why not download_url()? download_url() calls wp_safe_remote_get(), which
     * forces reject_unsafe_urls => true. That triggers wp_http_validate_url(),
     * which resolves the hostname and returns false for any private/loopback IP
     * (including *.local dev domains). The empty URL then causes WP_Upgrader::run()
     * to exit before setting $this->result, so Plugin_Upgrader::install() returns null.
     *
     * wp_remote_get() defaults to reject_unsafe_urls => false, so the same hostname
     * that works for our wp_remote_post() addon/check and addon/download calls also
     * works here.
     *
     * We create the temp file first and pass it via stream + filename so the response
     * body is written directly to disk -- no full-ZIP memory spike.
     *
     * @since 5.0.7
     * @access private
     *
     * @param string $url The URL to download.
     * @return string|\WP_Error Absolute temp file path on success, WP_Error on failure.
     */
    private function _download_to_temp( $url ) {
        $tmpfname = wp_tempnam( 'adt-pfe-addon' );
        if ( ! $tmpfname ) {
            return new \WP_Error( 'tmp_file', __( 'Could not create temporary file.', 'woo-product-feed-elite' ) );
        }

        $host     = (string) wp_parse_url( $url, PHP_URL_HOST );
        $is_local = (bool) preg_match( '/\.(local|test)$|^localhost$/i', $host );

        $response = wp_remote_get(
            $url,
            array(
                'timeout'   => 60,
                'stream'    => true,
                'filename'  => $tmpfname,
                'sslverify' => ! $is_local,
            )
        );

        if ( is_wp_error( $response ) ) {
            wp_delete_file( $tmpfname );
            return $response;
        }

        $http_code = (int) wp_remote_retrieve_response_code( $response );
        if ( 200 !== $http_code ) {
            wp_delete_file( $tmpfname );
            return new \WP_Error(
                'download_failed',
                sprintf(
                    /* translators: %d: HTTP status code */
                    __( 'Addon download failed (HTTP %d).', 'woo-product-feed-elite' ),
                    $http_code
                )
            );
        }

        return $tmpfname;
    }

    /**
     * Save the addon's license credentials and activate them against the SLMW
     * server, mirroring the flow in License_Manager::activate_license() and
     * License_Manager::process_license_response().
     *
     * @since 5.0.7
     * @access private
     *
     * @param string $software_key     The addon software key.
     * @param string $license_key      The addon license key.
     * @param string $activation_email The activation email address.
     */
    private function _activate_addon_license( $software_key, $license_key, $activation_email ) {
        $addon_option_prefix = 'adt_' . strtolower( sanitize_key( $software_key ) );

        // Store credentials so the addon's license page shows them pre-filled.
        update_site_option( $addon_option_prefix . '_license_key', $license_key );
        update_site_option( $addon_option_prefix . '_activation_email', $activation_email );

        // Call the SLMW activation endpoint for this addon's software key.
        $activation_url = add_query_arg(
            array(
                'activation_email' => rawurlencode( $activation_email ),
                'license_key'      => $license_key,
                'site_url'         => home_url(),
                'software_key'     => $software_key,
                'multisite'        => is_multisite() ? 1 : 0,
            ),
            constant( 'ADT_PFE_SLMW_SERVER_URL' ) . '/wp-json/slmw/v1/license/activate'
        );

        $response = json_decode(
            wp_remote_retrieve_body(
                wp_remote_get(
                    $activation_url,
                    array(
                        'timeout' => 10,
                        'headers' => array( 'Accept' => 'application/json' ),
                    )
                )
            )
        );

        if ( empty( $response ) || ! property_exists( $response, 'status' ) ) {
            return;
        }

        // Update last license check timestamp on successful activation.
        if ( 'success' === $response->status ) {
            update_site_option( $addon_option_prefix . '_last_license_check', time() );
        }

        // Store response in the shared ADT_LICENSE_DATA option (keyed by software key),
        // mirroring process_license_response() in License_Manager.
        if (
            property_exists( $response, 'license_status' ) &&
            in_array( $response->license_status, array( 'active', 'disabled', 'expired' ), true )
        ) {
            $license_data_arr = array_map(
                function ( $value ) {
                    if ( is_object( $value ) ) {
                        return $value;
                    }
                    return sanitize_text_field( $value );
                },
                (array) $response
            );

            $adt_license_data                  = (array) get_site_option( ADT_LICENSE_DATA, array() );
            $adt_license_data[ $software_key ] = $license_data_arr;
            update_site_option( ADT_LICENSE_DATA, $adt_license_data );
        }

        // Fire the same post-activation hook that License_Manager fires.
        do_action( 'adt_' . strtolower( $software_key ) . '_activate_license', (array) $response, $activation_email, $license_key );
    }

    /**
     * Register the `adt_pfe_addon_check` Action Scheduler recurring action.
     *
     * Schedules every 14 days with a random offset on first registration so
     * sites do not all hammer the SLMW server at the same moment.
     *
     * @since 5.0.7
     * @access public
     */
    public function register_action_scheduler() {
        if ( ! function_exists( 'as_next_scheduled_action' ) || ! function_exists( 'as_schedule_recurring_action' ) ) {
            return;
        }

        if ( ! as_next_scheduled_action( 'adt_pfe_addon_check' ) ) {
            as_schedule_recurring_action(
                time() + wp_rand( 0, DAY_IN_SECONDS ),
                14 * DAY_IN_SECONDS,
                'adt_pfe_addon_check',
                array(),
                'adt_pfe',
                true,
                10
            );
        }
    }

    /**
     * Restrict addon notices to AdTribes pages, the Plugins screen, and the Dashboard.
     *
     * Hooked to `adt_pfp_show_notice`. When the notice is displayed in the
     * `admin_wide` context (i.e. outside AdTribes pages), only allow it on
     * the Plugins and Dashboard screens.
     *
     * @since 5.0.7
     * @access public
     *
     * @param bool   $show       Whether to show the notice.
     * @param string $notice_key The notice key.
     * @param string $context    Either 'plugin_page' or 'admin_wide'.
     * @param array  $notice_data The notice definition.
     * @return bool
     */
    public function restrict_addon_notice_screens( $show, $notice_key, $context, $notice_data ) {
        // Only target addon install notices.
        if ( empty( $notice_data['notice_type'] ) || 'addon_install' !== $notice_data['notice_type'] ) {
            return $show;
        }

        // Always show on AdTribes plugin pages.
        if ( 'plugin_page' === $context ) {
            return $show;
        }

        // In admin_wide context, only show on Dashboard and Plugins screens.
        $screen = get_current_screen();
        if ( $screen && in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) ) {
            return $show;
        }

        return false;
    }

    /**
     * Execute model -- register all hooks.
     *
     * @since 5.0.7
     * @access public
     */
    public function run() {
        add_action( 'adt_pfe_addon_check', array( $this, 'check_addons' ), 10 );

        // Inject addon notices into PRO's cron notices pipeline.
        add_filter( 'adt_pfp_cron_notices_data', array( $this, 'add_addon_notices' ) );

        // Restrict addon notices to AdTribes, plugins, and dashboard screens.
        add_filter( 'adt_pfp_show_notice', array( $this, 'restrict_addon_notice_screens' ), 10, 4 );

        // Track per-addon dismissals when PRO dismisses a notice.
        add_action( 'adt_pfp_before_dismiss_admin_notice', array( $this, 'handle_addon_dismiss' ), 10, 2 );

        // Enqueue addon install handler JS.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_addon_scripts' ) );

        add_action( 'wp_ajax_adt_pfe_install_addon', array( $this, 'ajax_install_addon' ) );
    }
}
