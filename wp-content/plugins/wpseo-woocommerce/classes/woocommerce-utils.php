<?php
/**
 * WooCommerce Yoast SEO plugin file.
 *
 * @package WPSEO/WooCommerce
 */

/**
 * Class WPSEO_WooCommerce_Utils
 */
class WPSEO_WooCommerce_Utils {

	/**
	 * Searches for the primary terms for given taxonomies and returns the first found primary term.
	 *
	 * @param array      $brand_taxonomies The taxonomies to find the primary term for.
	 * @param WC_Product $product          The WooCommerce Product.
	 *
	 * @return string The term's name (if found). Otherwise an empty string.
	 */
	public static function search_primary_term( array $brand_taxonomies, $product ) {
		foreach ( $brand_taxonomies as $taxonomy ) {
			$primary_term       = new WPSEO_Primary_Term( $taxonomy, $product->get_id() );
			$found_primary_term = $primary_term->get_primary_term();

			if ( $found_primary_term ) {
				$term = get_term_by( 'id', $found_primary_term, $taxonomy );

				return $term->name;
			}
		}

		return '';
	}

	/**
	 * Get the product display price, using the correct decimals, and tax setting.
	 *
	 * @param WC_Product $product The product we're retrieving the price for.
	 *
	 * @return string Price ready for display.
	 */
	public static function get_product_display_price( WC_Product $product ) {
		return self::get_display_price( $product, $product->get_price(), $product->get_min_purchase_quantity() );
	}

	/**
	 * Converts a raw, as stored in WooCommerce, price into the price as it is displayed in the shop.
	 *
	 * The shop can be configured to store prices with or without tax and to display them with or without tax,
	 * independently of each other. Whenever those two settings disagree, the tax needs to be added to or
	 * subtracted from the stored price to get to the price the customer actually sees.
	 *
	 * @param WC_Product       $product  The product (or variation) the price belongs to, used to resolve its tax class.
	 * @param string|float|int $price    The raw price as stored in WooCommerce.
	 * @param int              $quantity The quantity the price applies to.
	 *
	 * @return string Price ready for display.
	 */
	public static function get_display_price( $product, $price, $quantity = 1 ) {
		$decimals      = wc_get_price_decimals();
		$display_price = $price;

		if ( $price !== '' && $price !== null && wc_tax_enabled() ) {
			// Taxes should be calculated.
			if ( self::prices_should_include_tax() ) {
				// Prices are stored **without** tax, add tax.
				$display_price = wc_get_price_including_tax(
					$product,
					[
						'qty'   => $quantity,
						'price' => $price,
					],
				);
			}
			elseif ( self::prices_should_exclude_tax() ) {
				// Prices are stored **with** tax, subtract tax.
				$display_price = wc_get_price_excluding_tax(
					$product,
					[
						'qty'   => $quantity,
						'price' => $price,
					],
				);
			}
		}

		return wc_format_decimal( $display_price, $decimals );
	}

	/**
	 * Determines if tax should be added to the price stored in WooCommerce.
	 *
	 * @return bool True if prices should be displayed with tax added, false if not.
	 */
	public static function prices_should_include_tax() {
		return (
			! wc_prices_include_tax()
			&& get_option( 'woocommerce_tax_display_shop' ) === 'incl'
		);
	}

	/**
	 * Determines if tax should be subtracted from the price as stored in WooCommerce.
	 *
	 * @return bool True if prices should be displayed with tax subtracted, false if not.
	 */
	public static function prices_should_exclude_tax() {
		return (
			wc_prices_include_tax()
			&& get_option( 'woocommerce_tax_display_shop' ) === 'excl'
		);
	}

	/**
	 * Determines if prices have tax included or not.
	 *
	 * @codeCoverageIgnore Wrapper method.
	 *
	 * @return bool True if prices have tax included, false if not.
	 */
	public static function prices_have_tax_included() {
		return get_option( 'woocommerce_tax_display_shop' ) === 'incl';
	}

	/**
	 * Determines the product type.
	 *
	 * @param WC_Product $product The WooCommerce Product.
	 *
	 * @return string The product type. Fallbacks to 'simple'.
	 */
	public static function get_product_type( $product ) {
		if ( method_exists( $product, 'get_type' ) ) {
			return $product->get_type();
		}

		return 'simple';
	}
}
