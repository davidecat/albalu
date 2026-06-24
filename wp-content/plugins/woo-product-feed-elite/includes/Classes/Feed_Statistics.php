<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Traits\Singleton_Trait;

/**
 * Product Feed Admin class.
 *
 * @since 4.9.6
 */
class Feed_Statistics extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Holds the class instance object.
     *
     * @since 4.9.7
     * @access protected
     *
     * @var Product_Feed_Admin $instance The class instance object.
     */
    protected static $instance;

    /**
     * Add product feed actions.
     *
     * @since 4.9.7
     * @access public
     *
     * @param array  $actions The actions array.
     * @param object $feed The product feed object.
     * @return array
     */
    public function add_statistics_action( $actions, $feed ) {
        $statistics = array(
            'statistics' => array(
                'class' => 'adt-manage-feeds-action-statistics',
                'label' => __( 'View Statistics', 'woo-product-feed-elite' ),
                'icon'  => 'lucide--chart-area',
                'url'   => add_query_arg( 'id', $feed->id, admin_url( 'admin.php?page=adt_feed_statistics' ) ),
            ),
        );

        return array_slice( $actions, 0, 2, true ) +
                $statistics +
                array_slice( $actions, 2, null, true );
    }

    /**
     * Load statistics page template.
     *
     * @since 4.9.7
     * @access public
     */
    public function load_statistics_page() {
        $screen = get_current_screen();

        // Check if we're on the statistics page.
        if ( 'admin_page_adt_feed_statistics' === $screen->id ) {
            Helper::locate_admin_template(
                'feed-statistics-page.php',
                true,
                true,
                array()
            );
        }
    }

    /**
     * Register statistics admin page.
     *
     * @since 4.9.7
     * @access public
     */
    public function register_statistics_page() {
        add_submenu_page(
            'admin.php', // Use admin.php as parent slug for hidden pages.
            __( 'Feed Statistics', 'woo-product-feed-elite' ),
            __( 'Feed Statistics', 'woo-product-feed-elite' ),
            'manage_options',
            'adt_feed_statistics',
            array( $this, 'load_statistics_page' )
        );
    }

    /**
     * Enqueue statistics scripts.
     *
     * @since 4.9.7
     * @access public
     *
     * @param string $hook The hook.
     */
    public function enqueue_statistics_scripts( $hook ) {
        if ( 'admin_page_adt_feed_statistics' === $hook ) {
            // ChartJS.
            wp_enqueue_script( 'woosea_chart-bundle-min-js', ADT_PFE_JS_URL . 'Chart.bundle.min.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );

            // FLOT GRAPHS.
            wp_enqueue_script( 'woosea_flot_min-js', ADT_PFE_JS_URL . 'jquery.flot.min.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
            wp_enqueue_script( 'woosea_flot_pie_min-js', ADT_PFE_JS_URL . 'jquery.flot.pie.min.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
            wp_enqueue_script( 'woosea_flot_resize_min-js', ADT_PFE_JS_URL . 'jquery.flot.resize.min.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
            wp_enqueue_script( 'woosea_flot_stack_min-js', ADT_PFE_JS_URL . 'jquery.flot.stack.min.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
            wp_enqueue_script( 'woosea_flot_time_min-js', ADT_PFE_JS_URL . 'jquery.flot.time.min.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
            wp_enqueue_script( 'woosea_flot_categories-js', ADT_PFE_JS_URL . 'jquery.flot.categories.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
            wp_enqueue_script( 'woosea_flot_uiConstants-js', ADT_PFE_JS_URL . 'jquery.flot.drawSeries.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
            wp_enqueue_script( 'woosea_flot_browser-js', ADT_PFE_JS_URL . 'jquery.flot.browser.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );

            wp_enqueue_script( 'woosea_elite_feed_statistics-js', ADT_PFE_JS_URL . 'woosea_feed_statistics.js', '', WOOCOMMERCESEA_ELITE_PLUGIN_VERSION, true );
            wp_localize_script(
                'woosea_elite_feed_statistics-js',
                'woosea_feed_statistics_js_object',
                array(
                    'i18n' => array(
                        'legend_label' => __( 'Number of products in feed', 'woo-product-feed-elite' ),
                    ),
                )
            );
        }
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 4.9.7
     */
    public function run() {
        add_filter( 'adt_manage_feeds_row_actions', array( $this, 'add_statistics_action' ), 10, 2 );
        add_action( 'admin_menu', array( $this, 'register_statistics_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_statistics_scripts' ) );
    }
}
