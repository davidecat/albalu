<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE
 */

namespace AdTribes\PFE;

use AdTribes\PFE\Actions\Activation;
use AdTribes\PFE\Actions\Deactivation;
use AdTribes\PFE\Factories\Admin_Notice;
use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Traits\Singleton_Trait;

defined( 'ABSPATH' ) || exit;

require_once ADT_PFE_PLUGIN_DIR_PATH . 'includes/autoload.php';

/**
 * Class App
 */
class App {

    use Singleton_Trait;

    /**
     * Holds the class object instances.
     *
     * @since 4.9.5
     * @access protected
     *
     * @var array An array of object class instance.
     */
    protected $objects;

    /**
     * Holds the failed plugin dependencies.
     *
     * @since 4.9.7
     * @access private
     *
     * @var array An array of failed plugin dependencies.
     */
    private $_failed_dependencies;

    /**
     * App constructor.
     *
     * @since 4.9.5
     * @access public
     */
    public function __construct() {

        $this->objects = array();
    }

    /**
     * Called at the end of file to initialize autoloader.
     *
     * @since 4.9.5
     * @access public
     */
    public function boot() {
        // Added support for WooCommerce HPOS (High-Performnce Order Storage).
        add_action( 'before_woocommerce_init', array( $this, 'hpos_compatibility' ) );

        register_deactivation_hook( ADT_PFE_PLUGIN_FILE, array( $this, 'deactivation_actions' ) );

        // Check plugin dependencies early (before classes are loaded).
        if ( ! $this->_check_dependencies() ) {
            // Show admin notices on admin_notices hook (after textdomain is loaded).
            add_action( 'admin_notices', array( $this, 'display_dependency_notices' ) );
            return;
        }

        register_activation_hook( ADT_PFE_PLUGIN_FILE, array( $this, 'activation_actions' ) );

        // Execute codes that need to run on 'init' hook.
        add_action( 'init', array( $this, 'initialize' ) );

        /***************************************************************************
         * Run the plugin
         ***************************************************************************
         *
         * Run the plugin classes on `setup_theme` hook with priority 100 as
         * it depends on WooCommerce plugin to be loaded first and we need to make
         * sure that WP_Rewrite global object is already available.
         */
        add_action( 'setup_theme', array( $this, 'run' ), 101 );
    }

    /**
     * Register classes to run.
     *
     * @since 4.9.5
     * @access public
     *
     * @param array $objects Array of class instances.
     */
    public function register_objects( $objects ) {

        $this->objects = array_merge( $this->objects, $objects );
    }

    /**
     * Plugin activation actions
     *
     * @since 4.9.5
     * @access public
     *
     * @param bool $sitewide Whether the plugin is being activated network-wide.
     */
    public function activation_actions( $sitewide ) {

        // Run the plugin actions here when it's activated.
        ( new Activation( $sitewide ) )->run();

        flush_rewrite_rules();
    }

    /**
     * Display dependency failure notices with translations.
     *
     * @since 4.9.7
     * @access public
     */
    public function display_dependency_notices() {
        if ( ! empty( $this->_failed_dependencies['missing_plugins'] ) ) {
            $admin_notice = new Admin_Notice(
                sprintf(
                    /* translators: %1$s = opening <strong> tag; %2$s = closing </strong> tag; %3$s = opening <p> tag; %4$s = closing </p> tag */
                    esc_html__(
                        '%3$s%1$sProduct Feed Elite for WooCommerce %2$splugin missing dependency.%4$s',
                        'woo-product-feed-elite'
                    ),
                    '<strong>',
                    '</strong>',
                    '<p>',
                    '</p>'
                ),
                'failed_dependency',
                true,
                true,
                array(
                    'failed_dependencies' => $this->_failed_dependencies,
                ),
            );
            $admin_notice->run();
        } elseif ( ! empty( $this->_failed_dependencies['failed_version_requirements'] ) ) {
            $admin_notice = new Admin_Notice(
                sprintf(
                    /* translators: %1$s = required version of the plugin; %2$s = current version of the plugin */
                    esc_html__(
                        '%1$sPlease update the following plugin(s) to the required version:%2$s',
                        'woo-product-feed-elite'
                    ),
                    '<p>',
                    '</p>'
                ),
                'failed_dependency',
                true,
                true,
                array(
                    'failed_dependencies' => $this->_failed_dependencies,
                )
            );
            $admin_notice->run();
        }
    }

    /**
     * Method that houses codes to be executed on init hook.
     *
     * @since 4.9.7
     * @access public
     */
    public function initialize() {
        // Execute activation codebase if not yet executed on plugin activation ( Mostly due to plugin dependencies ).
        $installed_version = get_site_option( ADT_PFE_OPTION_INSTALLED_VERSION, false );

        if ( version_compare( $installed_version, Helper::get_plugin_version(), '!=' ) || get_option( 'adt_pfe_activation_code_triggered', false ) !== 'yes' ) {
            if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
                require_once ABSPATH . '/wp-admin/includes/plugin.php';
            }

            $sitewide = is_plugin_active_for_network( 'woo-product-feed-elite/woocommerce-sea.php' );
            $this->activation_actions( $sitewide );
        }
    }

    /**
     * Run the plugin classes.
     *
     * @since 4.9.5
     * @access public
     */
    public function run() {
        /***************************************************************************
         * Instantiate classes
         ***************************************************************************
        *
        * Instantiate classes to be registered and run.
        */
        $this->register_objects(
            array_merge(
                require_once ADT_PFE_PLUGIN_DIR_PATH . 'bootstrap/class-objects.php',
                require_once ADT_PFE_PLUGIN_DIR_PATH . 'bootstrap/integration-objects.php',
                require_once ADT_PFE_PLUGIN_DIR_PATH . 'bootstrap/rest-objects.php',
                require_once ADT_PFE_PLUGIN_DIR_PATH . 'bootstrap/feeds-objects.php',
            )
        );

        /***************************************************************************
         * Run the plugin classes
         ***************************************************************************
         *
         * Make sure that the classes to be run extends the abstract class or has
         * implemented a `run` method.
         */
        foreach ( $this->objects as $object ) {
            if ( ! method_exists( $object, 'run' ) ) {
                _doing_it_wrong(
                    __METHOD__,
                    esc_html__(
                        'The class does not have a run method. Please make sure to extend the Abstract_Class class.',
                        'woo-product-feed-elite'
                    ),
                    esc_html( Helper::get_plugin_data( 'Version' ) )
                );
                continue;
            }
            $class_object = strtolower( wp_basename( get_class( $object ) ) );

            $this->objects[ $class_object ] = apply_filters(
                'woo_sea_class_object',
                $object,
                $class_object,
                $this
            );
            $this->objects[ $class_object ]->run();
        }

        /***************************************************************************
         * Run the plugin legacy classes
         ***************************************************************************
         *
         * We have some legacy classes that are not yet refactored to the new structure.
         * So we run them here to make sure that they are still working.
         *
         * @todo Remove this when all classes are refactored.
         */
        $this->run_legacy_classes();
    }

    /**
     * Run the legacy classes.
     *
     * @since 4.9.5
     * @access public
     */
    public function run_legacy_classes() {
        // Old bootstrap.
        require_once ADT_PFE_PLUGIN_DIR_PATH . '/bootstrap-old.php';
    }

    /**
     * Plugin deactivation actions
     *
     * @since 4.9.5
     * @access public
     *
     * @param bool $sitewide Whether the plugin is being deactivated network-wide.
     */
    public function deactivation_actions( $sitewide ) {

        // Run the plugin deactivation actions.
        ( new Deactivation( $sitewide ) )->run();

        flush_rewrite_rules();
    }

    /**
     * Check plugin dependencies.
     *
     * @since 4.9.7
     * @access private
     *
     * @return bool True if all dependencies are met, false otherwise.
     */
    private function _check_dependencies() {
        /***************************************************************************
         * Check required plugins
         ***************************************************************************
         *
         * We check if the required plugins are active.
         */

        $this->_failed_dependencies['missing_plugins']             = $this->_check_missing_required_plugins();
        $this->_failed_dependencies['failed_version_requirements'] = $this->_check_plugin_dependency_version_requirements();

        // Return false if there are any failed dependencies.
        if ( ! empty( $this->_failed_dependencies['missing_plugins'] ) || ! empty( $this->_failed_dependencies['failed_version_requirements'] ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check plugin dependency version requirements.
     *
     * @since 4.9.7
     * @access private
     *
     * @return mixed Array if there are invalid versioned plugin dependencies, True if all plugin dependencies have valid version.
     */
    private function _check_plugin_dependency_version_requirements() {
        $plugins = array();

        $required_plugin_versions = array(
            'woo-product-feed-pro/woocommerce-sea.php' => WOOCOMMERCESEA_ELITE_REQUIRED_PFP_PLUGIN_VERSION,
        );

        foreach ( $required_plugin_versions as $plugin => $required_version ) {
            if ( is_plugin_active( $plugin ) ) {
                $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false );
                if ( ! version_compare( $plugin_data['Version'], $required_version, '>=' ) ) {
                    $plugins[] = array(
                        'plugin-key'       => $plugin_data['Name'],
                        'plugin-base'      => $plugin,
                        'plugin-name'      => str_replace(
                            'Woocommerce',
                            'WooCommerce',
                            ucwords( str_replace( '-', ' ', $plugin_data['Name'] ) )
                        ),
                        'required-version' => $required_version,
                    );
                }
            }
        }

        return $plugins;
    }

    /**
     * Checks required plugins if they are active.
     *
     * @since 4.9.5
     * @access public
     *
     * @return array List of plugins that are not active.
     */
    private static function _check_missing_required_plugins() {

        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $i       = 0;
        $plugins = array();

        $required_plugins = array(
            'woocommerce/woocommerce.php',
            'woo-product-feed-pro/woocommerce-sea.php',
        );

        foreach ( $required_plugins as $plugin ) {
            if ( ! is_plugin_active( $plugin ) ) {
                $plugin_name                  = explode( '/', $plugin );
                $plugins[ $i ]['plugin-key']  = $plugin_name[0];
                $plugins[ $i ]['plugin-base'] = $plugin;
                $plugins[ $i ]['plugin-name'] = str_replace(
                    'Woocommerce',
                    'WooCommerce',
                    ucwords( str_replace( '-', ' ', $plugin_name[0] ) )
                );
            }

            ++$i;
        }

        return $plugins;
    }

    /**
     * HPOS compatibility
     *
     * @since 4.9.5
     * @access public
     */
    public function hpos_compatibility() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', ADT_PFE_PLUGIN_FILE, true );
        }
    }
}

return App::instance();
