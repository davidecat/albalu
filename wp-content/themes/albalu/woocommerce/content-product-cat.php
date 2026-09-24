<?php
/**
 * Product category thumbnail in loops — Albalù (circle + label below).
 *
 * @package WooCommerce\Templates
 * @version 4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term_link = get_term_link( $category, 'product_cat' );
if ( is_wp_error( $term_link ) ) {
	return;
}

$label = $category->name;
if ( function_exists( 'get_field' ) ) {
	$custom_name = get_field( 'nome_categoria_visualizzato', $category );
	if ( is_string( $custom_name ) && trim( $custom_name ) !== '' ) {
		$label = $custom_name;
	}
}

$thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
?>
<li <?php wc_product_cat_class( 'albalu-subcats__item', $category ); ?>>
	<a href="<?php echo esc_url( $term_link ); ?>" class="albalu-subcats__link">
		<span class="albalu-subcats__thumb" aria-hidden="true">
			<?php
			if ( $thumbnail_id ) {
				echo wp_get_attachment_image(
					(int) $thumbnail_id,
					'woocommerce_thumbnail',
					false,
					array(
						'class'   => 'albalu-subcats__img',
						'alt'     => '',
						'loading' => 'lazy',
					)
				);
			} else {
				echo wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'albalu-subcats__img' ) );
			}
			?>
		</span>
		<span class="albalu-subcats__label"><?php echo esc_html( $label ); ?></span>
	</a>
</li>
