<?php
/**
 * Product loop item template override for Albalu child theme
 * Adapted for Bootscore (Bootstrap 5) structure
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check visibility.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

// Calculate Bootstrap column class based on loop columns
$columns = wc_get_loop_prop( 'columns' );
if ( empty( $columns ) ) {
    $columns = 4; // Default to 4 if not set
}

// Map columns to Bootstrap classes — 2 per row on mobile (col-6)
$col_class = 'col-6 col-lg-3'; // Default for 4 desktop columns
if ( $columns == 1 ) {
	$col_class = 'col-12';
} elseif ( $columns == 2 ) {
	$col_class = 'col-6';
} elseif ( $columns == 3 ) {
	$col_class = 'col-6 col-lg-4';
} elseif ( $columns == 4 ) {
	$col_class = 'col-6 col-lg-3';
} elseif ( $columns == 5 ) {
	$col_class = 'col-6 col-lg-2';
} elseif ( $columns >= 6 ) {
	$col_class = 'col-6 col-lg-2';
}

$col_class .= ' mb-1 mb-md-2';
?>
<div <?php wc_product_class( $col_class, $product ); ?>>
	<div class="product-inner card h-100 border-1">
        <div class="card-body p-1 p-md-3 text-left">
            <?php
            /**
             * Hook: woocommerce_before_shop_loop_item.
             * Open Link
             */
            do_action( 'woocommerce_before_shop_loop_item' );

            /**
             * Hook: woocommerce_before_shop_loop_item_title.
             * Sale Flash, Thumbnail
             */
            do_action( 'woocommerce_before_shop_loop_item_title' );

            /**
             * Hook: woocommerce_shop_loop_item_title.
             * Title
             */
            do_action( 'woocommerce_shop_loop_item_title' );

            /**
             * Hook: woocommerce_after_shop_loop_item_title.
             * Rating, Price
             */
            do_action( 'woocommerce_after_shop_loop_item_title' );

            /**
             * Hook: woocommerce_after_shop_loop_item.
             * Close Link, Add to Cart
             */
            do_action( 'woocommerce_after_shop_loop_item' );
            ?>
        </div>
    </div>
</div>
