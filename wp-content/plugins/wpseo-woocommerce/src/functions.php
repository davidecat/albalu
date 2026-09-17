<?php
/**
 * Yoast SEO: WooCommerce plugin file.
 *
 * @package Yoast\WP\SEO\WooCommerce
 */

use Yoast\WP\SEO\WooCommerce\Main;

/**
 * Retrieves the main instance.
 *
 * @phpcs:disable WordPress.NamingConventions -- Consistent with the accessors in the other Yoast add-ons.
 *
 * @return Main|null The main instance.
 *
 * @throws Exception If loading fails and YOAST_ENVIRONMENT is development.
 */
function YoastSEOWooCommerce() {
	// phpcs:enable

	static $main;

	if ( did_action( 'wpseo_loaded' ) ) {
		if ( $main === null ) {
			// Ensure free is loaded as loading this plugin will fail without it.
			YoastSEO();
			$main = new Main();
			$main->load();
		}
	}
	else {
		add_action( 'wpseo_loaded', 'YoastSEOWooCommerce' );
	}

	return $main;
}
