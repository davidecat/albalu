<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Updates
 */

namespace AdTribes\PFE\Updates;

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Factories\Product_Feed;

/**
 * Class Version_13_3_5_Update
 *
 * @since 4.9.7
 */
class Version_4_9_7_Update extends Abstract_Class {

    /**
     * Holds the version number.
     *
     * @since 4.9.7
     * @access protected
     *
     * @var string
     */
    protected $version = '4.9.7';

    /**
     * Whether to force update the options.
     *
     * @since 4.9.7
     * @access protected
     *
     * @var bool
     */
    protected $force_update = false;

    /**
     * Constructor.
     *
     * @since 4.9.7
     * @access public
     *
     * @param bool $force_update Whether to force update the options.
     */
    public function __construct( $force_update = false ) {
        $this->force_update = $force_update;
    }

    /**
     * Migrate the options to new CPT.
     *
     * @since 4.9.7
     */
    public function update() {
        $cron_projects = maybe_unserialize( get_option( 'cron_projects' ), array() );
        if ( ! empty( $cron_projects ) ) {
            foreach ( $cron_projects as $key => $project_data ) {

                // Skip if the project hash is not set.
                if ( empty( $project_data['project_hash'] ) ) {
                    unset( $cron_projects[ $key ] );
                    continue;
                }

                $feed = new Product_Feed( $project_data['project_hash'] ?? 0 );
                $feed->set_props(
                    array(
                        'field_manipulation' => $project_data['field_manipulation'] ?? array(),
                        'wpml'               => $project_data['WPML'] ?? '',
                        'wcml'               => $project_data['WCML'] ?? '',
                        'aelia'              => $project_data['AELIA'] ?? '',
                        'curcy'              => $project_data['CURCY'] ?? '',
                        'translatepress'     => $project_data['TRANSLATEPRESS'] ?? '',
                        'polylang'           => $project_data['polylang'] ?? '',
                    )
                );
                $feed = apply_filters( 'adt_pfe_update_version_4_9_7_before_save_feed', $feed, $project_data );
                $feed->save();
            }

            // Save the cron projects.
            update_option( 'cron_projects', $cron_projects, false );
        }
    }

    /**
     * Run the class.
     *
     * @since 4.9.7
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
