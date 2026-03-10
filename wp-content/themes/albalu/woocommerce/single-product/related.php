<?php
/**
 * Related Products - Swiper layout (matching "Prodotti più richiesti")
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $related_products ) :
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );
		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}
	?>

	<section class="related products">

		<h2>Potrebbe interessarti anche: </h2>

		<!-- Swiper -->
		<div class="px-2 position-relative product-slider woocommerce">

			<div class="cards swiper-container swiper position-static">

				<div class="swiper-wrapper">

					<?php foreach ( $related_products as $related_product ) : ?>

						<?php
						$post_object = get_post( $related_product->get_id() );
						setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
						?>

						<div <?php wc_product_class( 'swiper-slide card h-auto mb-5 d-flex px-4 text-left product-card' ); ?>>
							<?php
							do_action( 'woocommerce_before_shop_loop_item' );
							do_action( 'woocommerce_before_shop_loop_item_title' );
							?>
							<div class="card-body d-flex flex-column">
								<?php
								do_action( 'woocommerce_shop_loop_item_title' );
								do_action( 'woocommerce_after_shop_loop_item_title' );
								do_action( 'woocommerce_after_shop_loop_item' );
								?>
							</div>
						</div>

					<?php endforeach; ?>

				</div> <!-- .swiper-wrapper -->

				<!-- Add Pagination -->
				<div class="swiper-pagination"></div>
				<!-- Add Arrows -->
				<div class="swiper-button-next fw-bold end-0" style="color: #F3915C"></div>
				<div class="swiper-button-prev fw-bold start-0" style="color: #F3915C"></div>

			</div><!-- swiper-container -->

		</div><!-- px-5 position-relative -->
		<!-- Swiper End -->

	</section>
	<?php
endif;

wp_reset_postdata();
