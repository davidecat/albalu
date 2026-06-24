<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFE\Updates
 */

namespace AdTribes\PFE\Updates;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AdTribes\PFE\Abstracts\Abstract_Class;
use AdTribes\PFE\Classes\License_Manager;
use AdTribes\PFE\Factories\Product_Feed;

/**
 * Class Version_5_0_8_Update
 *
 * Runs the 5.0.8 upgrade migrations:
 *
 * - Heals stale Elite-group Action Scheduler entries left behind by the
 *   pre-5.0.8 Custom -> standard refresh-interval switch bug (issue #377).
 * - Audits the `autoload` flag on plugin-owned options (issue #378):
 *     - Flips admin/cron/one-shot options to `autoload = 'no'` so they don't
 *       bloat the alloptions blob on front-end / REST / cart requests.
 *     - Flips the license-gated feature toggles to `autoload = 'yes'`. These
 *       were previously seeded with `autoload = 'no'` by `License_Manager`
 *       but are read by Integration `is_active()` checks on every request,
 *       so they qualify for autoload to skip per-request SELECTs.
 *
 * WordPress's `update_option()` only sets the autoload flag the first
 * time an option is created, so existing installs need this one-shot
 * migration to correct stale flags.
 *
 * @since 5.0.8
 */
class Version_5_0_8_Update extends Abstract_Class {

    /**
     * Holds the version number.
     *
     * @since 5.0.8
     * @access protected
     *
     * @var string
     */
    protected $version = '5.0.8';

    /**
     * Whether to force update the options.
     *
     * @since 5.0.8
     * @access protected
     *
     * @var bool
     */
    protected $force_update = false;

    /**
     * Constructor.
     *
     * @since 5.0.8
     * @access public
     *
     * @param bool $force_update Whether to force update the options.
     */
    public function __construct( $force_update = false ) {
        $this->force_update = $force_update;
    }

    /**
     * Heal stale Elite-group Action Scheduler entries.
     *
     * Before 5.0.8, switching a feed from Custom back to a standard interval
     * (Hourly / Twice Daily / Daily) left a stale entry in the Elite custom-
     * interval AS group because the Elite Product_Feed subclass inherited
     * Pro's unregister_action(), which only cleared the Pro group. The 5.0.8
     * fix overrides unregister_action() to clear both groups.
     *
     * Healing covers two pre-5.0.8 states:
     *
     * - Published feeds switched from Custom → standard: `register_action()`
     *   re-schedules in the Pro group and (via the new override) clears any
     *   stale Elite-group entry first.
     * - Deactivated feeds (`post_status = 'draft'`) that were switched while
     *   in the bug state: `unregister_action()` clears any stale Elite-group
     *   entry without re-scheduling, since `Cron::as_generate_product_feed_callback()`
     *   does not gate on `post_status` and would otherwise fire indefinitely
     *   against a dormant feed.
     *
     * @since 5.0.8
     * @access private
     */
    private function heal_stale_action_scheduler_entries() {
        if ( ! class_exists( 'AdTribes\PFP\Factories\Product_Feed_Query' ) ) {
            return;
        }

        $product_feeds_query = new \AdTribes\PFP\Factories\Product_Feed_Query(
            array(
                'post_status'    => array( 'publish', 'draft' ),
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'     => 'adt_refresh_interval',
                        'value'   => 'custom',
                        'compare' => '!=',
                    ),
                ),
            ),
            'edit'
        );

        if ( ! $product_feeds_query->have_posts() ) {
            return;
        }

        foreach ( $product_feeds_query->get_posts() as $product_feed ) {
            if ( ! $product_feed instanceof Product_Feed ) {
                continue;
            }

            if ( 'publish' === $product_feed->post_status ) {
                $product_feed->register_action();
            } else {
                $product_feed->unregister_action();
            }
        }
    }

    /**
     * Plugin-owned options that should NOT be autoloaded.
     *
     * Admin-only, cron-only, or one-shot options that previously had
     * `autoload = 'yes'` either via an omitted `$autoload` argument or
     * an incorrect `true` in a migration config.
     *
     * NOTE: `adt_pfe_activation_code_triggered` is intentionally NOT in
     * this list — it is read by `App::initialize()` on every `init`
     * hook, so it must remain autoloaded.
     *
     * @since 5.0.8
     * @access private
     *
     * @return array List of option names to flip to autoload=no.
     */
    private function get_options_to_disable_autoload() {
        return array(
            // Previously omitted autoload arg (audited in issue #378).
            'woosea_allow_update',
            'adt_pfe_dismissed_addons',
            ADT_PFE_SHOW_PRODUCT_FEED_TRANSLATION_ADDON_NOTICE,

            // Admin/feed-only options that already pass `false` at their call
            // sites but may have been seeded as autoloaded on older installs.
            'selected_values',
            'last_order_id',
            'adt_extra_attributes',
            'product_changes',
            'cron_projects',
            'woosea_getelite_notification',
            ADT_OPTION_TEMP_PRODUCT_FEED,
        );
    }

    /**
     * Plugin-owned options that SHOULD be autoloaded.
     *
     * Delegates to `License_Manager::AUTOLOADED_FEATURE_OPTIONS` — the same
     * canonical list used by the runtime filter so the migration and the
     * filter cannot drift.
     *
     * @since 5.0.8
     * @access private
     *
     * @return array List of option names to flip to autoload=yes.
     */
    private function get_options_to_enable_autoload() {
        return License_Manager::AUTOLOADED_FEATURE_OPTIONS;
    }

    /**
     * Flip the `autoload` flag on `wp_options` rows.
     *
     * Uses `wp_set_options_autoload()` when available (WP 6.4+); falls
     * back to a direct `UPDATE` on `wp_options` and an `alloptions`
     * cache flush for older WP (plugin min is 5.9).
     *
     * @since 5.0.8
     * @access private
     *
     * @param array  $option_names List of option names.
     * @param string $autoload     Target autoload value ('yes' or 'no').
     */
    private function set_autoload( $option_names, $autoload ) {
        if ( empty( $option_names ) ) {
            return;
        }

        if ( function_exists( 'wp_set_options_autoload' ) ) {
            wp_set_options_autoload( $option_names, $autoload );
            return;
        }

        // Fallback for WP < 6.4.
        global $wpdb;
        $placeholders    = implode( ',', array_fill( 0, count( $option_names ), '%s' ) );
        $existing_filter = 'no' === $autoload ? "AND autoload IN ('yes', 'on', 'auto-on')" : "AND autoload IN ('no', 'off', 'auto-off')";
        $wpdb->query(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders generated above; filter is a literal.
                "UPDATE {$wpdb->options} SET autoload = %s WHERE option_name IN ({$placeholders}) {$existing_filter}",
                array_merge( array( $autoload ), $option_names )
            )
        );

        // Bust the alloptions cache so subsequent reads see the new flag.
        wp_cache_delete( 'alloptions', 'options' );
    }

    /**
     * Resolve dynamic addon-notice option names and flip them to autoload=no.
     *
     * `Addon_Manager` writes per-addon notice options whose names are computed
     * at runtime (`adt_show_addon_<key>_notice`). On 5.0.8+ these are created
     * with `autoload=false`, but rows seeded by older Elite versions remain
     * autoloaded — the static disable list can't enumerate dynamic names.
     *
     * The pattern is a hardcoded literal, so no injection surface.
     *
     * @since 5.0.8
     * @access private
     */
    private function set_autoload_for_dynamic_addon_notices() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-shot migration; alloptions cache is flushed by set_autoload().
        $dynamic = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'adt_show_addon_%_notice'"
        );

        if ( ! empty( $dynamic ) ) {
            $this->set_autoload( $dynamic, 'no' );
        }
    }

    /**
     * Run the 5.0.8 migrations.
     *
     * @since 5.0.8
     */
    public function update() {
        $this->heal_stale_action_scheduler_entries();
        $this->set_autoload( $this->get_options_to_disable_autoload(), 'no' );
        $this->set_autoload( $this->get_options_to_enable_autoload(), 'yes' );
        $this->set_autoload_for_dynamic_addon_notices();
    }

    /**
     * Run the migration.
     *
     * Runs against the CURRENT blog only. `Activation::run()` already
     * loops `$blog_ids` and calls `_activate_plugin()` (which invokes
     * this method) per blog, so this method must not loop blogs itself.
     *
     * @since 5.0.8
     */
    public function run() {
        // Note: when ADT_PFE_OPTION_INSTALLED_VERSION is missing, get_site_option()
        // returns false, which version_compare() normalizes to '' and treats as
        // less than any version — so a separate `! get_site_option(...)` check
        // is not needed.
        if (
            version_compare( get_site_option( ADT_PFE_OPTION_INSTALLED_VERSION ), $this->version, '<' ) ||
            $this->force_update
        ) {
            $this->update();
        }
    }
}
