<?php
/**
 * @package Bootscore Child
 *
 * @version 6.0.0
 */


// Exit if accessed directly
defined('ABSPATH') || exit;

// Show 12 related products
add_filter( 'woocommerce_output_related_products_args', function( $args ) {
	$args['posts_per_page'] = 12;
	return $args;
} );

/**
 * Related products: same product category only (no tags / cross-category mix).
 */
add_filter( 'woocommerce_related_products', 'albalu_related_products_same_category', 20, 3 );
function albalu_related_products_same_category( $related_posts, $product_id, $args ) {
	$product_id = (int) $product_id;
	if ( ! $product_id ) {
		return $related_posts;
	}

	$term_ids = wc_get_product_term_ids( $product_id, 'product_cat' );
	$term_ids = array_values( array_filter( array_map( 'intval', (array) $term_ids ) ) );

	// Prefer the most specific (deepest) category.
	$best_term_id = 0;
	$best_depth   = -1;
	foreach ( $term_ids as $tid ) {
		if ( $tid === (int) get_option( 'default_product_cat' ) ) {
			continue;
		}
		$ancestors = get_ancestors( $tid, 'product_cat' );
		$depth     = is_array( $ancestors ) ? count( $ancestors ) : 0;
		if ( $depth > $best_depth ) {
			$best_depth   = $depth;
			$best_term_id = $tid;
		}
	}

	if ( ! $best_term_id && ! empty( $term_ids ) ) {
		$best_term_id = (int) $term_ids[0];
	}

	if ( ! $best_term_id ) {
		return array();
	}

	$limit = isset( $args['posts_per_page'] ) ? max( 1, (int) $args['posts_per_page'] ) : 12;

	$query = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'post__not_in'           => array( $product_id ),
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'rand',
			'tax_query'              => array(
				array(
					'taxonomy'         => 'product_cat',
					'field'            => 'term_id',
					'terms'            => array( $best_term_id ),
					'include_children' => false,
				),
			),
		)
	);

	return ! empty( $query->posts ) ? array_map( 'intval', $query->posts ) : array();
}

// Do not mix related products by tags when WC builds its default list (our filter replaces it).
add_filter( 'woocommerce_product_related_posts_relate_by_tag', '__return_false' );


/**
 * 1. Remove Description/Additional tabs & render description + reviews separately
 */

// Keep Reviews available as a native section (tabs UI removed for design).
add_filter( 'woocommerce_product_tabs', 'albalu_remove_product_tabs', 98 );
function albalu_remove_product_tabs( $tabs ) {
	unset( $tabs['description'], $tabs['additional_information'], $tabs['reviews'] );
	return $tabs;
}

/**
 * Trust strip + description (full width).
 */
add_action( 'woocommerce_after_single_product_summary', 'albalu_render_product_description', 5 );
function albalu_render_product_description() {
	global $post, $product;

	if ( ! $post ) {
		return;
	}

	$content = $post->post_content;

	$review_count = 0;
	$average      = 0;
	if ( $product instanceof WC_Product && wc_review_ratings_enabled() ) {
		$review_count = (int) $product->get_review_count();
		$average      = (float) $product->get_average_rating();
	}

	$stars_html = '';
	if ( $review_count > 0 && $average > 0 ) {
		$full  = (int) round( $average );
		$full  = max( 0, min( 5, $full ) );
		for ( $i = 0; $i < 5; $i++ ) {
			$stars_html .= $i < $full
				? '<i class="fas fa-star"></i>'
				: '<i class="far fa-star"></i>';
		}
		$reviews_label = esc_html(
			sprintf(
				/* translators: %s: number of reviews */
				_n( '%s recensione', '%s recensioni', $review_count, 'albalu' ),
				number_format_i18n( $review_count )
			)
		);
	} else {
		for ( $i = 0; $i < 5; $i++ ) {
			$stars_html .= '<i class="fas fa-star"></i>';
		}
		$reviews_label = esc_html__( 'Scrivi la prima recensione', 'albalu' );
	}

	echo <<<HTML
<section class="trust-strip py-4" style="background-color: #eae3e0; width: 100vw; max-width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); clear: both;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-2 mb-lg-0 text-center text-lg-start">
                <p class="mb-0" style="color: #3F494F; font-size: 1.05rem;">
                    Produciamo <strong>Bomboniere ed Articoli da regalo</strong> 100% artigianali e Made in Italy dal 1991
                </p>
            </div>
            <div class="col-lg-5 text-center text-lg-end">
                <div class="d-inline-flex align-items-center justify-content-lg-end justify-content-center">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 40px; height: 40px;">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48 48"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path></defs><clipPath id="b"><use xlink:href="#a"></use></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"></path><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"></path><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"></path><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"></path></svg>
                    </div>
                    <div class="text-start lh-1">
                        <div class="d-flex align-items-center mb-1">
                            <span class="fw-bold me-2" style="color: #3F494F;">Albalù Bomboniere</span>
                            <span class="text-warning small" style="font-size: 0.8rem;">{$stars_html}</span>
                        </div>
                        <a href="#reviews" class="small text-muted fw-bold text-decoration-none" style="font-size: 0.85rem;">{$reviews_label}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
HTML;

	if ( $content ) {
		echo <<<HTML
<section class="albalu-product-description-section py-4" style="width: 100vw; max-width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); clear: both;">
  <div class="container">
    <h2 class="h2 mb-4 border-bottom py-2">Descrizione</h2>
    <div class="albalu-product-description">
      {$GLOBALS['wp_embed']->autoembed( apply_filters( 'the_content', $content ) )}
    </div>
  </div>
</section>
HTML;
	}
}

/**
 * Native WooCommerce reviews section (replaces the removed Reviews tab).
 */
add_action( 'woocommerce_after_single_product_summary', 'albalu_render_product_reviews_section', 12 );
function albalu_render_product_reviews_section() {
	if ( ! comments_open() ) {
		return;
	}
	?>
	<section class="albalu-product-reviews-section py-4" style="width: 100vw; max-width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); clear: both;">
		<div class="container">
			<?php comments_template(); ?>
		</div>
	</section>
	<?php
}

function wrap_quantity_addtocart() {
	// Stampa diretta in wp_footer: con la strategy defer di WC 10.9+
	// wp_add_inline_script('wc-single-product') non viene più stampato.
	// Auto-riparante: PEWC (deferito) ricostruisce l'area add-to-cart dopo
	// il ready, quindi il wrap viene rifatto a window.load e su eventi PEWC.
	if ( ! is_product() ) return;
	?>
	<script>
	(function($) {
		function albaluWrapQty() {
			$('form.cart').each(function(){
				var $form = $(this);
				var $qty = $form.find('.quantity').first();
				var $btn = $form.find('.single_add_to_cart_button').first();
				if (!$qty.length || !$btn.length) return;
				var $wrap = $form.find('.quantity-addtocart-wrapper').first();
				if ($wrap.length && $wrap.has($qty[0]).length && $wrap.has($btn[0]).length) return;
				if ($wrap.length) {
					$wrap.children().first().unwrap();
				}
				$qty.add($btn).wrapAll('<div class="quantity-addtocart-wrapper"></div>');
			});
		}
		$(albaluWrapQty);
		$(window).on('load', albaluWrapQty);
		$('body').on('pewc_after_update_total_js pewc_conditions_checked', albaluWrapQty);
	})(jQuery);
	</script>
	<?php
}
add_action( 'wp_footer', 'wrap_quantity_addtocart', 60 );

function albalu_add_inline_styles_single_product() {
	if ( ! is_product() ) return;
	$css = "
	body.woocommerce.single-product form.cart { display: flex; flex-wrap: wrap; gap: 0; }
	body.woocommerce.single-product .quantity-addtocart-wrapper { width: 100%; display: flex; gap: 10px; align-items: stretch; }
	body.woocommerce.single-product .quantity-addtocart-wrapper .quantity { flex: 0 0 auto; display: flex; align-items: center; }
	body.woocommerce.single-product .quantity-addtocart-wrapper .single_add_to_cart_button { flex: 1 1 auto; height: 52px; }
	.albalu-purchase-benefits { margin-top: 10px; border-top: 1px solid rgba(0,0,0,0.1); }
	.albalu-purchase-benefits .item { gap: 12px; padding: 12px 0;  }
	.albalu-purchase-benefits .item img.benefit-icon { width: 80px; height: 50px; object-fit: contain; display: inline-block; }
	.albalu-purchase-benefits .item p { margin: 0; }
	.albalu-payment-trust-badges { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; margin: 12px 0 4px; padding: 10px 0; border-top: 1px solid rgba(0,0,0,0.08); border-bottom: 1px solid rgba(0,0,0,0.08); }
	.albalu-payment-trust-badges img { width: 56px; height: 36px; object-fit: contain; }
	.albalu-product-reviews-section .woocommerce-Reviews { max-width: 100%; }
	.albalu-product-reviews-section #reviews { scroll-margin-top: 80px; }
	";
	wp_add_inline_style('main', $css);
}
add_action('wp_enqueue_scripts', 'albalu_add_inline_styles_single_product', 30);

/**
 * Payment trust badges — moved above add-to-cart (after excerpt) for clearer trust signal.
 */
function albalu_payment_trust_badges_html() {
	$base = esc_url( get_stylesheet_directory_uri() . '/assets/img' );
	ob_start();
	?>
	<div class="albalu-payment-trust-badges" aria-label="<?php esc_attr_e( 'Metodi di pagamento', 'albalu' ); ?>">
		<img src="<?php echo $base; ?>/paypal.svg" alt="PayPal e Carte di Credito" width="56" height="36" loading="lazy">
		<img src="<?php echo $base; ?>/klarna.svg" alt="Klarna" width="56" height="36" loading="lazy">
		<img src="<?php echo $base; ?>/consegna.svg" alt="Contrassegno" width="56" height="36" loading="lazy">
		<img src="<?php echo $base; ?>/bancario.svg" alt="Bonifico bancario" width="56" height="36" loading="lazy">
	</div>
	<?php
	return ob_get_clean();
}

function albalu_render_payment_trust_badges_summary() {
	if ( ! is_product() ) {
		return;
	}
	echo albalu_payment_trust_badges_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'woocommerce_single_product_summary', 'albalu_render_payment_trust_badges_summary', 25 );

/**
 * Delivery time under add-to-cart (editable via Impostazioni Albalù).
 */
function albalu_static_benefits_below_addtocart() {
	if ( ! is_product() ) {
		return;
	}
	$base = esc_url( get_stylesheet_directory_uri() . '/assets/img' );
	$text = function_exists( 'albalu_get_delivery_time_text' )
		? albalu_get_delivery_time_text()
		: 'Realizziamo e spediamo il tuo ordine in <strong>7/13 giorni lavorativi</strong>.';
	$text = wp_kses_post( $text );

	echo '<div class="albalu-purchase-benefits">';
	echo '<div class="d-flex align-items-center item border-top border-bottom"><img class="benefit-icon" src="' . $base . '/truck.svg" alt="Spedizione"><p>' . $text . '</p></div>';
	echo '</div>';
}
add_action( 'woocommerce_after_add_to_cart_form', 'albalu_static_benefits_below_addtocart', 20 );

function albalu_faq_link_below_addtocart() {
	if ( ! is_product() ) return;
	echo '<p class="albalu-faq-link mt-3 text-end"><a href="/faq">Hai ancora dubbi? Vai alla sezione domande frequenti - FAQ!</a></p>';
}
add_action( 'woocommerce_after_add_to_cart_form', 'albalu_faq_link_below_addtocart', 19 );

function custom_add_to_cart_message() {
	$message = 'Il prodotto è stato aggiunto al carrello! <a href="'.esc_url(wc_get_page_permalink('cart')).'" tabindex="1" class="button button-gotocart wc-forward"><i aria-hidden="true" class="fas fa-shopping-cart"></i>&nbsp;Vai al carrello</a>';
	return $message;
}
add_filter('wc_add_to_cart_message_html', 'custom_add_to_cart_message');



// Render Global FAQ Section (Outside Tabs)
//add_action( 'woocommerce_after_single_product', 'albalu_render_global_faq_section', 20 );

function albalu_render_global_faq_section() {    
    // SAFETY CHECK: If ACF function is missing, stop immediately to prevent crash
    if ( ! function_exists( 'have_rows' ) ) {
        return;
    }

    // Check if we have rows in the global options
    if ( ! have_rows( 'faq', 'option' ) ) {
         return;
    }

    echo '<div id="albalu-global-faq" class="albalu-global-faq container py-5">';
    echo '<div class="row">';
    echo '<div class="col-5">';
    
    echo '<h2 class="mb-4">Domande frequenti</h3>';
    echo '<p class="h3 mb-4 text-uppercase text-muted small fw-medium">Se non trovi la risposta che cerchi contattaci.</p>';
     echo '</div>';
    echo '<div class="col-7">';

    echo '<div class="accordion accordion-flush" id="faqAccordion">';
    
    $i = 0;
    while ( have_rows( 'faq', 'option' ) ) {
        the_row();
        $q = get_sub_field( 'faq-question' );
        $a = get_sub_field( 'faq-answer' );

        if ( $q ) {
            $i++;
            $id = 'faq-item-' . $i;
            
            echo '<div class="accordion-item bg-transparent border-bottom">';
            echo '<h2 class="accordion-header" id="heading-' . $id . '">';
            echo '<button class="accordion-button collapsed bg-transparent shadow-none px-0 py-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-' . $id . '" aria-expanded="false" aria-controls="collapse-' . $id . '" style="color: var(--color-titoli, inherit);">';
            echo esc_html( $q );
            echo '</button>';
            echo '</h2>';
            echo '<div id="collapse-' . $id . '" class="accordion-collapse collapse" aria-labelledby="heading-' . $id . '" data-bs-parent="#faqAccordion">';
            echo '<div class="accordion-body px-0 pb-3 text-muted fw-medium">';
            echo wpautop( wp_kses_post( $a ) );
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    echo '</div>'; // .accordion
    echo '</div>'; // .col-12
    echo '</div>'; // .row
    echo '</div>'; // .container
}
