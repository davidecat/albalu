<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/title.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://woocommerce.com/document/template-structure/
 * @package    WooCommerce\Templates
 * @version    1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

the_title( '<h1 class="product_title entry-title">', '</h1>' );

global $product;
if ( $product && wc_product_sku_enabled() && $product->get_sku() ) : ?>
	<p class="product-sku my-4 pb-4 border-bottom"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <?php echo esc_html( $product->get_sku() ); ?></p>
<?php endif;
