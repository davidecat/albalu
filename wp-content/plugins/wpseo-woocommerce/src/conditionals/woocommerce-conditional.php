<?php

namespace Yoast\WP\SEO\WooCommerce\Conditionals;

use Yoast\WP\SEO\Conditionals\Conditional;

/**
 * Conditional that is only met when WooCommerce is active.
 */
class WooCommerce_Conditional implements Conditional {

	/**
	 * Returns whether or not this conditional is met.
	 *
	 * @return bool Whether or not the conditional is met.
	 */
	public function is_met() {
		return \class_exists( 'WooCommerce' );
	}
}
