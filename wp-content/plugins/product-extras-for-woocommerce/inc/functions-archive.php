<?php
/**
 * Functions for the product archive
 * @since 1.0.0
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Does the product have product_extra groups?
 * @return Boolean
 */
function pewc_has_product_extra_groups( $product_id ) {

	// Request-level cache: avoids repeated lookups for the same product within a single page load
	static $cache = array();

	$bypass = apply_filters( 'pewc_bypass_extra_fields_transient', false );

	if( ! $bypass && isset( $cache[ $product_id ] ) ) {
		return $cache[ $product_id ];
	}

	if( $bypass ) {
		return false;
	}

	// Check post meta first. wc_get_product() loads all post meta into the WP object cache,
	// and WP_Query (e.g. related products) batch-loads meta for all returned IDs in one query.
	// Either way, this read is served from memory with no additional DB query.
	$has_fields = get_post_meta( $product_id, '_pewc_has_extra_fields', true );

	if( $has_fields ) {
		$cache[ $product_id ] = $has_fields;
		return $has_fields;
	}

	// Check transient as fallback for products not yet backfilled
	$has_fields = pewc_get_transient( 'pewc_has_extra_fields_' . $product_id );

	if( $has_fields ) {
		// Backfill post meta so future requests skip the transient read entirely
		update_post_meta( $product_id, '_pewc_has_extra_fields', $has_fields );
		$cache[ $product_id ] = $has_fields;
		return $has_fields;
	}

	// Cold path: calculate from scratch
	$has_fields = 'no';
	$product_extra_groups = pewc_get_extra_fields( $product_id );
	if( $product_extra_groups ) {
		$has_fields = 'yes';
	}

	update_post_meta( $product_id, '_pewc_has_extra_fields', $has_fields );
	pewc_set_transient( 'pewc_has_extra_fields_' . $product_id, $has_fields );
	$cache[ $product_id ] = $has_fields;

	return $has_fields;
}

/**
 * Products with product_extra groups can't be purchased from archive
 * @return Boolean
 */
function pewc_filter_is_purchasable( $is_purchasable, $product ) {
	if( is_archive() ) {
		$product_id = $product->get_id();
		$product_extra_groups = pewc_get_extra_fields( $product_id );
		if( $product_extra_groups ) {
			return false;
		}
	}
	return $is_purchasable;
}
// add_filter( 'woocommerce_is_purchasable', 'pewc_filter_is_purchasable', 10, 2 );

/**
 * Replace add to cart button in archive, shop, home and products shortcode
 * @return HTML
 */
function pewc_view_product_button( $button, $product, $args=array( 'class' => '' ) ) {

	$product_id = $product->get_id();
	if( pewc_has_product_extra_groups( $product_id ) != 'yes' ) {
		return $button;
	}
	return $button;

	$text = pewc_get_product_add_to_cart_text( $product );

  $button = sprintf(
		'<a class="%s" href="%s">%s</a>',
		apply_filters( 'pewc_add_to_cart_button_class', $args['class'], $product ),
		$product->get_permalink(),
		esc_html( $text )
	);

  return $button;

}
// add_filter( 'woocommerce_loop_add_to_cart_link', 'pewc_view_product_button', 10, 3 );

/**
 * Filter the add to cart URL in archives etc
 * @since 3.3.5
 */
function pewc_product_add_to_cart_url( $url, $product ) {

	$product_id = $product->get_id();
	if( pewc_has_product_extra_groups( $product_id ) != 'yes' ) {
		return $url;
	}

	// If the product has add-ons then link to the product page, rather than trying to add it to the cart
	return $product->get_permalink();

}
add_filter( 'woocommerce_product_add_to_cart_url', 'pewc_product_add_to_cart_url', 10, 2 );

/**
 * Filter the add to cart URL in archives etc
 * @since 3.3.5
 */
function pewc_product_add_to_cart_text( $text, $product ) {

	$product_id = $product->get_id();
	if( pewc_has_product_extra_groups( $product_id ) != 'yes' ) {
		return $text;
	}

	// If the product has add-ons then link to the product page, rather than trying to add it to the cart
	return pewc_get_product_add_to_cart_text( $product );

}
add_filter( 'woocommerce_product_add_to_cart_text', 'pewc_product_add_to_cart_text', 10, 2 );

/**
 * Get our alternative text for the add to cart button in loops
 * @since 3.3.5
 */
function pewc_get_product_add_to_cart_text( $product ) {
	return apply_filters(
		'pewc_filter_view_product_text',
		__( 'Select options', 'pewc' ),
		$product
	);
}

/**
 * Filter Gutenberg products archive HTML (e.g. removing AJAX Add to Cart)
 * @since 3.11.4
 */
function pewc_filter_blocks_product_grid_item_html( $block, $data, $product ) {
	if( pewc_has_product_extra_groups( $product->get_id() ) != 'yes' ) {
		return $block;
	}

	if ( isset( $data->button ) && false !== strpos( $data->button, ' ajax_add_to_cart' ) ) {
		// remove AJAX add to cart JS from Gutenberg
		$newbutton = str_replace( ' ajax_add_to_cart', '', $data->button );
		$block = str_replace( $data->button, $newbutton, $block );
	}

	return $block;
}
add_filter( 'woocommerce_blocks_product_grid_item_html', 'pewc_filter_blocks_product_grid_item_html', 10, 3 );

/**
 * Disable AJAX add to cart on archive pages for products with AOU fields
 * @since 3.15.1
 */
function pewc_product_supports_ajax_add_to_cart( $supports, $feature, $product ) {
	if ( 'ajax_add_to_cart' === $feature && false === is_product() && 'yes' == pewc_has_product_extra_groups( $product->get_id() ) ) {
		// disable add to cart on products with AOU fields
		$supports = false;
	}
	return $supports;
}
add_filter( 'woocommerce_product_supports', 'pewc_product_supports_ajax_add_to_cart', 10, 3 );
