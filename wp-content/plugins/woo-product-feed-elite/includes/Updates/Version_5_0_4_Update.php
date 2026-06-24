<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Updates
 */

namespace AdTribes\PFE\Updates;

use AdTribes\PFE\Abstracts\Abstract_Class;

/**
 * Class Version_5_0_4_Update
 *
 * @since 5.0.4
 */
class Version_5_0_4_Update extends Abstract_Class {

    /**
     * Holds the version number.
     *
     * @since 5.0.4
     * @access protected
     *
     * @var string
     */
    protected $version = '5.0.4';

    /**
     * Whether to force update the options.
     *
     * @since 5.0.4
     * @access protected
     *
     * @var bool
     */
    protected $force_update = false;

    /**
     * Constructor.
     *
     * @since 5.0.4
     * @access public
     *
     * @param bool $force_update Whether to force update the options.
     */
    public function __construct( $force_update = false ) {
        $this->force_update = $force_update;
    }

    /**
     * Migrate legacy options to new prefixed versions.
     *
     * @since 5.0.4
     */
    public function update() {
        $options_to_migrate = array(
            'structured_data_fix'        => array(
                'value'    => 'adt_structured_data_fix',
                'autoload' => true,
            ),
            'structured_vat'             => array(
                'value'    => 'adt_structured_vat',
                'autoload' => true,
            ),
            'add_manipulation_support'   => array(
                'value'    => 'adt_enable_data_manipulation_support',
                'autoload' => true,
            ),
            'add_wpml_support'           => array(
                'value'    => 'adt_enable_wpml_support',
                'autoload' => true,
            ),
            'add_aelia_support'          => array(
                'value'    => 'adt_enable_aelia_support',
                'autoload' => true,
            ),
            'add_curcy_support'          => array(
                'value'    => 'adt_enable_curcy_support',
                'autoload' => true,
            ),
            'add_polylang_support'       => array(
                'value'    => 'adt_enable_polylang_support',
                'autoload' => true,
            ),
            'add_translatepress_support' => array(
                'value'    => 'adt_enable_translatepress_support',
                'autoload' => true,
            ),
            'add_facebook_capi'          => array(
                'value'    => 'adt_enable_facebook_capi',
                'autoload' => true,
            ),
            'woosea_facebook_capi_token' => array(
                'value'    => 'adt_facebook_capi_token',
                'autoload' => true,
            ),
            'woosea_extra_attributes'    => array(
                'value'    => 'adt_extra_attributes',
                'autoload' => false,
            ),
        );

        foreach ( $options_to_migrate as $old_option => $new_option_config ) {
            $old_value = get_option( $old_option );

            // Handle both array and string formats for backward compatibility.
            $new_option = is_array( $new_option_config ) ? $new_option_config['value'] : $new_option_config;
            $autoload   = is_array( $new_option_config ) ? $new_option_config['autoload'] : true;

            // If the old option exists and the new one doesn't, migrate the value.
            if ( false !== $old_value && false === get_option( $new_option ) ) {
                update_option( $new_option, $old_value, $autoload );
            }
        }
    }

    /**
     * Run the class.
     *
     * @since 5.0.4
     */
    public function run() {
        if (
            (
                version_compare( get_site_option( ADT_PFE_OPTION_INSTALLED_VERSION ), $this->version, '<=' ) ||
                ! get_site_option( ADT_PFE_OPTION_INSTALLED_VERSION )
            ) || $this->force_update
        ) {
            $this->update();
        }
    }
}
