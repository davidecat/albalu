<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Classes
 */

namespace AdTribes\PFE\Classes;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Traits\Singleton_Trait;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Translation Migration
 *
 * Displays a notice informing users that translation features (WPML, Polylang,
 * TranslatePress) are moving to a separate Feed Translations Addon.
 *
 * Only shown to stores that have at least one translation integration enabled.
 *
 * @since 5.0.7
 */
class Translation_Migration extends Abstract_Class {

    use Singleton_Trait;

    /**
     * Check whether the user has any translation integration actively enabled.
     *
     * @since 5.0.7
     * @access private
     *
     * @return bool
     */
    private function _has_translation_feature_enabled() {
        return 'yes' === get_option( 'adt_enable_wpml_support' )
            || 'yes' === get_option( 'adt_enable_polylang_support' )
            || 'yes' === get_option( 'adt_enable_translatepress_support' );
    }

    /**
     * Inject the translation migration notice into PRO's cron notice pipeline.
     *
     * Hooked to `adt_pfp_cron_notices_data`.
     *
     * @since 5.0.7
     * @access public
     *
     * @param array $notices Existing notice definitions.
     * @return array Modified notice definitions.
     */
    public function add_notice( $notices ) {
        if ( ! $this->_has_translation_feature_enabled() ) {
            return $notices;
        }

        $option_key = ADT_PFE_SHOW_PRODUCT_FEED_TRANSLATION_ADDON_NOTICE;

        // Initialize the option so PRO's pipeline picks it up.
        if ( '' === get_option( $option_key, '' ) ) {
            update_option( $option_key, 'yes', false );
        }

        $notices['translation_migration'] = array(
            'notice_type'     => 'translation_migration',
            'option'          => $option_key,
            'skip_cron'       => true,
            'is_dismissible'  => true,
            'show_admin_wide' => false,
            'type'            => 'info',
            'is_html'         => true,
            'message'         => array(
                '<h3>' . esc_html__( 'Translation features are moving to a separate addon!', 'woo-product-feed-elite' ) . '</h3>',
                '<p>' . esc_html__( 'Translation support (WPML, Polylang, TranslatePress) will be moved to the Feed Translations Addon in an upcoming release.', 'woo-product-feed-elite' ) . '</p>',
            ),
            'actions'         => array(
                array(
                    'type'        => 'secondary',
                    'link'        => Helper::get_utm_url( 'knowledge-base/getting-started-with-the-product-feed-translation-addon/', 'pfe', 'translation-migration-notice', 'learnmore' ),
                    'is_external' => true,
                    'text'        => esc_html__( 'Learn More', 'woo-product-feed-elite' ),
                ),
            ),
        );

        return $notices;
    }

    /**
     * Execute model -- register all hooks.
     *
     * @since 5.0.7
     * @access public
     */
    public function run() {
        add_filter( 'adt_pfp_cron_notices_data', array( $this, 'add_notice' ) );
    }
}
