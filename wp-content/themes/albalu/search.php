<?php
/**
 * Search results template for Albalu child theme
 * Displays product search results using the same grid layout as category archives
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 */
do_action( 'woocommerce_before_main_content' );
?>

<div class="container pt-4 pb-5">

<header class="woocommerce-products-header">
	<h1 class="woocommerce-products-header__title page-title">
		<?php
		printf(
			esc_html__( 'Risultati di ricerca per: %s', 'albalu' ),
			'<span>' . esc_html( get_search_query() ) . '</span>'
		);
		?>
	</h1>
	<?php
	$albalu_event       = isset( $_GET['event'] ) ? sanitize_text_field( wp_unslash( $_GET['event'] ) ) : '';
	$albalu_events_list = function_exists( 'albalu_get_search_events' ) ? albalu_get_search_events() : array();
	if ( ! empty( $albalu_event ) && isset( $albalu_events_list[ $albalu_event ] ) ) :
		$reset_url = remove_query_arg( 'event' );
		?>
		<p class="mb-0 mt-2">
			<span class="badge rounded-pill bg-primary text-white px-3 py-2">
				<i class="fas fa-filter me-1"></i>
				Filtro: <?php echo esc_html( $albalu_events_list[ $albalu_event ] ); ?>
				<a href="<?php echo esc_url( $reset_url ); ?>" class="text-white text-decoration-none ms-2" aria-label="Rimuovi filtro">
					<i class="fas fa-times"></i>
				</a>
			</span>
		</p>
	<?php endif; ?>
</header>

<?php
if ( woocommerce_product_loop() ) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action( 'woocommerce_before_shop_loop' );

	woocommerce_product_loop_start();

	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();

			/**
			 * Hook: woocommerce_shop_loop.
			 */
			do_action( 'woocommerce_shop_loop' );

			wc_get_template_part( 'content', 'product' );
		}
	}

	woocommerce_product_loop_end();

	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action( 'woocommerce_after_shop_loop' );

} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
}
?>

</div><!-- .container -->

<?php
/**
 * Hook: woocommerce_after_main_content.
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
