<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Abstracts\Abstract_Update;
use AdTribes\PFE\Traits\Singleton_Trait;
use AdTribes\PFE\Classes\License_Manager;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of updating the plugin.
 *
 * @since 4.9.5
 */
class Update_Manager extends Abstract_Update {

    use Singleton_Trait;

    /**
     * Property that holds the software key of this plugin.
     *
     * @since 4.9.5
     * @access private
     * @var string $_software_key
     */
    private $_software_key;

    /**
     * Property that holds the slmw server url of this plugin.
     *
     * @since 4.9.5
     * @access private
     * @var string $_activation_endpoint
     */
    private $_slmw_server_url;

    /**
     * Property that holds the update data endpoint of this plugin.
     *
     * @since 4.9.5
     * @access private
     * @var string $_update_data_endpoint
     */
    private $_update_data_endpoint;

    /**
     * Property that holds the license manager instance.
     *
     * @since 4.9.5
     * @access private
     * @var License_Manager $license_manager
     */
    private $license_manager;

    /**
     * Class Methods
     */

    /**
     * Class constructor.
     *
     * @since 4.9.5
     * @access public
     */
    public function __construct() {
        $this->_software_key         = constant( 'ADT_PFE_SLMW_SOFTWARE_KEY' );
        $this->_update_data_endpoint = constant( 'ADT_PFE_SLMW_SERVER_URL' ) . '/wp-json/slmw/v1/license/update';
        $this->license_manager       = License_Manager::instance();
    }

    /**
     * Hijack the WordPress 'set_site_transient' function for 'update_plugins' transient.
     * So now we don't have our own cron to check for updates, we just rely on when WordPress check updates for plugins and themes,
     * and if WordPress does then sets the 'update_plugins' transient, then we hijack it and check for our own plugin update.
     *
     * @since 4.9.5
     * @access public
     *
     * @param object $update_plugins Update plugins data.
     */
    public function update_check( $update_plugins ) {

        /**
         * Function wp_update_plugins calls set_site_transient( 'update_plugins' , ... ) twice, yes twice
         * so we need to make sure we are on the last call before checking plugin updates
         * the last call will have the checked object parameter
         */
        if ( isset( $update_plugins->checked ) ) {
            $this->ping_for_new_version( false );
        }

        /**
         * We try to inject plugin update data if it has any
         * This is to fix the issue about plugin info appearing/disappearing
         * when update page in WordPress is refreshed
         */
        $result = $this->inject_plugin_update(); // Inject new update data if there are any.

        if (
            $result && isset( $update_plugins->response ) &&
            is_array( $update_plugins->response ) &&
            ! array_key_exists( $result['key'], $update_plugins->response )
        ) {
            $update_plugins->response[ $result['key'] ] = $result['value'];
        } elseif ( ! empty( $update_plugins->no_update ) && is_array( $update_plugins->no_update ) ) {
            // Adding the plugin to the no_update property of the update plugin's list enables "enable auto updates" toggle in the plugins list.
            $update_plugins->no_update[ ADT_PFE_BASENAME ] = (object) array(
                'id'            => ADT_PFE_BASENAME,
                'plugin'        => ADT_PFE_BASENAME,
                'name'          => 'Product Feed ELITE for WooCommerce',
                'new_version'   => WOOCOMMERCESEA_ELITE_PLUGIN_VERSION,
                'url'           => 'https://adtribes.io/',
                'homepage'      => 'https://adtribes.io/',
                'package'       => '',
                'icons'         => array(),
                'banners'       => array(),
                'banners_rtl'   => array(),
                'tested'        => '',
                'requires_php'  => '',
                'compatibility' => new \stdClass(),
            );
        }

        $this->_validate_update_data_version( $update_plugins );

        return $update_plugins;
    }

    /**
     * Validate the plugin's update data to make sure that the version is higher than the current one installed.
     *
     * @since 4.9.7
     * @access private
     *
     * @param object $update_plugins Update plugins data.
     */
    private function _validate_update_data_version( &$update_plugins ) {

        // Skip if plugin is not present in the list of updates.
        if ( ! isset( $update_plugins->response[ ADT_PFE_BASENAME ] ) ) {
            return;
        }

        $new_version = $update_plugins->response[ ADT_PFE_BASENAME ]->new_version;

        // Unset the plugin from the list of updates when the version is lower or equal to the current one installed.
        if ( version_compare( $new_version, WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, '<=' ) ) {
            unset( $update_plugins->response[ ADT_PFE_BASENAME ] );
        }
    }

    /**
     * Ping plugin for new version. Ping static file first, if indeed new version is available, trigger update data request.
     *
     * @since 4.9.5
     *
     * @param bool $force Flag to determine whether to "forcefully" fetch the latest update data from the server.
     * @access public
     */
    public function ping_for_new_version( $force = false ) {

        $license_activated = $this->license_manager->is_license_activated();

        if ( ! $license_activated ) {
            delete_site_option( ADT_PFE_UPDATE_DATA );
            return;
        }

        $retrieving_update_data = get_site_option( ADT_PFE_RETRIEVING_UPDATE_DATA );
        if ( 'yes' === $retrieving_update_data ) {
            return;
        }

        /**
         * Only attempt to get the existing saved update data when the operation is not forced.
         * Else, if it is forced, we ignore the existing update data if any
         * and forcefully fetch the latest update data from our server.
         */
        $update_data = ! $force ? get_site_option( ADT_PFE_UPDATE_DATA ) : null;

        /**
         * Even if the update data is still valid, we still go ahead and do static json file ping.
         * The reason is on WooCommerce 3.3.x , it seems WooCommerce do not regenerate the download url every time you change the downloadable zip file on WooCommerce store.
         * The side effect is, the download url is still valid, points to the latest zip file, but the update info could be outdated coz we check that if the download url
         * is still valid, we don't check for update info, and since the download url will always be valid even after subsequent release of the plugin coz WooCommerce is reusing the url now
         * then there will be a case our update info is outdated. So that is why we still need to check the static json file, even if update info is still valid.
         */

        $args = apply_filters(
            'adt_pfe_plugin_new_version_ping_options',
            array(
                'timeout' => 10, // Seconds.
                'headers' => array( 'Accept' => 'application/json' ),
            )
        );

        $response = wp_remote_retrieve_body( wp_remote_get( apply_filters( 'adt_pfe_plugin_new_version_ping_url', ADT_PFE_STATIC_PING_FILE ), $args ) );
        $response = ! empty( $response ) ? json_decode( $response ) : null;

        // Skip if the response is empty.
        if ( ! is_object( $response ) || ! property_exists( $response, 'version' ) ) {
            return;
        }

        $installed_version = get_site_option( ADT_PFE_OPTION_INSTALLED_VERSION );

        if ( ( ! $update_data && version_compare( $response->version, $installed_version, '>' ) ) ||
            ( $update_data && version_compare( $response->version, $update_data->latest_version, '>' ) ) ) {

            update_site_option( ADT_PFE_RETRIEVING_UPDATE_DATA, 'yes' );

            $activation_email = get_site_option( ADT_PFE_ACTIVATION_EMAIL );
            $license_key      = get_site_option( ADT_PFE_LICENSE_KEY );

            // Fetch software product update data.
            $this->_fetch_software_product_update_data( $activation_email, $license_key, home_url() );

            delete_site_option( ADT_PFE_RETRIEVING_UPDATE_DATA );

        } elseif (
            ( $update_data && version_compare( $update_data->latest_version, $installed_version, '<=' ) ) ||
            ( version_compare( $response->version, $installed_version, '<=' ) )
        ) {
            /**
             * We delete the option data if update is already installed
             * We encountered a bug when updating the plugin via the dashboard updates page
             * The update is successful but the update notice does not disappear
             */
            delete_site_option( ADT_PFE_UPDATE_DATA );
        }
    }

    /**
     * Fetch software product update data.
     *
     * @since 4.9.5
     * @access public
     *
     * @param string $activation_email Activation email.
     * @param string $license_key      License key.
     * @param string $site_url         Site url.
     */
    private function _fetch_software_product_update_data( $activation_email, $license_key, $site_url ) {

        $update_check_url = add_query_arg(
            array(
                'activation_email' => rawurlencode( $activation_email ),
                'license_key'      => $license_key,
                'site_url'         => $site_url,
                'software_key'     => $this->_software_key,
                'multisite'        => is_multisite() ? 1 : 0,
            ),
            apply_filters( 'adt_pfe_software_product_update_data_url', $this->_update_data_endpoint )
        );

        $args = apply_filters(
            'adt_pfe_software_product_update_data_options',
            array(
                'timeout' => 30, // Seconds.
                'headers' => array( 'Accept' => 'application/json' ),
            )
        );

        $response = json_decode( wp_remote_retrieve_body( wp_remote_get( $update_check_url, $args ) ) );

        // Skip if the result is empty.
        if ( empty( $response ) ) {
            return;
        }

        // Process license options data.
        $response = $this->license_manager->process_license_response( $response, 'update_data' );

        // Fire post product update data hook.
        do_action( 'adt_pfe_software_product_update_data', $response, $activation_email, $license_key );
    }

    /**
     * Inject plugin update info to plugin update details page.
     * Note, this is only triggered when there is a new update and the "View version <new version here> details" link is clicked.
     * In short, the pure purpose for this is to provide details and info the update info popup.
     *
     * @since 4.9.5
     * @access public
     *
     * @param false|object|array $result The result object or array. Default false.
     * @param string             $action The type of information being requested from the Plugin Install API.
     * @param object             $args   Plugin API arguments.
     * @return array Plugin update info.
     */
    public function inject_plugin_update_info( $result, $action, $args ) {
        if ( 'plugin_information' === $action && isset( $args->slug ) && 'woo-product-feed-elite' === $args->slug ) {
            $software_update_data = get_site_option( ADT_PFE_UPDATE_DATA );

            if ( $software_update_data ) {

                // Create update info object.
                $update_info                       = new \StdClass();
                $update_info->name                 = 'Product Feed ELITE for WooCommerce';
                $update_info->slug                 = 'woo-product-feed-elite';
                $update_info->version              = $software_update_data->latest_version ?? '';
                $update_info->tested               = $software_update_data->tested_up_to ?? '';
                $update_info->last_updated         = $software_update_data->last_updated ?? '';
                $update_info->homepage             = $software_update_data->home_page ?? '';
                $update_info->download_link        = $software_update_data->download_url ?? '';
                $update_info->{'update-supported'} = true;
                $update_info->author               = $software_update_data->author_url && $software_update_data->author
                    ? sprintf( '<a href="%s" target="_blank">%s</a>', $software_update_data->author_url, $software_update_data->author )
                    : '';
                $update_info->sections             = array(
                    'description'  => $software_update_data->description ?? '',
                    'installation' => $software_update_data->installation ?? '',
                    'changelog'    => $software_update_data->changelog ?? '',
                    'support'      => $software_update_data->support ?? '',
                );

                $update_info->icons = array(
                    '1x'      => 'https://ps.w.org/woo-product-feed-pro/assets/icon-128x128.png',
                    '2x'      => 'https://ps.w.org/woo-product-feed-pro/assets/icon-256x256.png',
                    'default' => 'https://ps.w.org/woo-product-feed-pro/assets/icon-256x256.png',
                );

                $update_info->banners = array(
                    'low'  => 'https://ps.w.org/woo-product-feed-pro/assets/banner-772x250.jpg',
                    'high' => 'https://ps.w.org/woo-product-feed-pro/assets/banner-1544x500.jpg',
                );

                return $update_info;
            }
        }

        return $result;
    }

    /**
     * When WordPress fetches 'update_plugins' transient (which holds data regarding plugins that have updates), we
     * inject our plugin update data in, if we have any. This data is saved in the ADT_PFE_UPDATE_DATA option. It is
     * important we don't delete this option until the plugin has successfully updated. We do this because we are
     * hooking on transient read. So if the option gets deleted on first transient read, then subsequent reads will not
     * include our plugin update data.
     *
     * This function also checks the validity of the update url to cover the edge case where we may have stored the
     * update data locally as an option, then later on the update data became invalid. So as a safety mechanism we check
     * if the update url is still valid and if not we remove the locally stored update data.
     *
     * @since 4.9.5
     *
     * @access public
     * @return array Filtered plugin updates data.
     */
    public function inject_plugin_update() {
        $software_update_data = get_site_option( ADT_PFE_UPDATE_DATA );
        if ( $software_update_data && \version_compare( $software_update_data->latest_version, WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, '>' ) ) {

            // Create update info object.
            $update                       = new \stdClass();
            $update->name                 = 'Product Feed ELITE for WooCommerce';
            $update->slug                 = 'woo-product-feed-elite';
            $update->id                   = $software_update_data->download_id ?? '';
            $update->plugin               = ADT_PFE_BASENAME;
            $update->new_version          = $software_update_data->latest_version ?? '';
            $update->url                  = $this->_slmw_server_url;
            $update->package              = $software_update_data->download_url ?? '';
            $update->tested               = $software_update_data->tested_up_to ?? '';
            $update->{'update-supported'} = true;
            $update->update               = false;
            $update->icons                = array(
                '1x'      => 'https://ps.w.org/woo-product-feed-pro/assets/icon-128x128.png',
                '2x'      => 'https://ps.w.org/woo-product-feed-pro/assets/icon-256x256.png',
                'default' => 'https://ps.w.org/woo-product-feed-pro/assets/icon-256x256.png',
            );

            $update->banners = array(
                '1x'      => 'https://ps.w.org/woo-product-feed-pro/assets/banner-772x250.png',
                '2x'      => 'https://ps.w.org/woo-product-feed-pro/assets/banner-1544x500.png',
                'default' => 'https://ps.w.org/woo-product-feed-pro/assets/banner-1544x500.png',
            );

            return array(
                'key'   => ADT_PFE_BASENAME,
                'value' => $update,
            );
        }

        return false;
    }

    /**
     * Register actions.
     *
     * @since 4.9.5
     * @access public
     */
    public function actions() {
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );
        add_filter( 'plugins_api', array( $this, 'inject_plugin_update_info' ), 10, 3 );
    }
}
