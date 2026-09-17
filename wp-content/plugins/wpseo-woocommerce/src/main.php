<?php

namespace Yoast\WP\SEO\WooCommerce;

use Yoast\WP\Lib\Abstract_Main;
use Yoast\WP\SEO\Dependency_Injection\Container_Compiler;
use Yoast\WP\SEO\Surfaces\Classes_Surface;
use Yoast\WP\SEO\WooCommerce\Generated\Cached_Container;

if ( ! \defined( 'WPSEO_WOO_VERSION' ) ) {
	\header( 'Status: 403 Forbidden' );
	\header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Main plugin class for Yoast SEO: WooCommerce.
 *
 * @property Classes_Surface $classes The classes surface.
 */
class Main extends Abstract_Main {

	/**
	 * Retrieves the name this plugin's container is registered under.
	 *
	 * @return string The name of the plugin.
	 */
	protected function get_name() {
		return 'yoast-seo-woocommerce';
	}

	/**
	 * Retrieves the dependency injection container, recompiling it first in development.
	 *
	 * @return Cached_Container|null The container, or null when it has not been compiled.
	 */
	protected function get_container() {
		if (
			$this->is_development()
			&& \class_exists( '\Yoast\WP\SEO\Dependency_Injection\Container_Compiler' )
			&& \file_exists( __DIR__ . '/../config/dependency-injection/services.php' )
		) {
			// Exception here is unhandled as it will only occur in development.
			Container_Compiler::compile(
				$this->is_development(),
				__DIR__ . '/generated/container.php',
				__DIR__ . '/../config/dependency-injection/services.php',
				__DIR__ . '/../vendor/composer/autoload_classmap.php',
				'Yoast\WP\SEO\WooCommerce\Generated',
			);
		}

		if ( \file_exists( __DIR__ . '/generated/container.php' ) ) {
			require_once __DIR__ . '/generated/container.php';

			return new Cached_Container();
		}

		return null;
	}

	/**
	 * Retrieves the surfaces that can be accessed as a property on this class.
	 *
	 * @return string[] The surfaces, keyed by the property they are available under.
	 */
	protected function get_surfaces() {
		return [
			'classes' => Classes_Surface::class,
		];
	}
}
