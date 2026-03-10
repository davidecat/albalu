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
 * 1. Remove All Tabs & Render Separately
 */

// Remove ALL Tabs (Description, Reviews, Additional Info)
add_filter( 'woocommerce_product_tabs', 'albalu_remove_all_tabs', 98 );
function albalu_remove_all_tabs( $tabs ) {
    return array(); // Removes all tabs
}

// Render Description After Single Product Summary (Full Width, Below Image/Summary)
add_action( 'woocommerce_after_single_product_summary', 'albalu_render_product_description', 5 );
function albalu_render_product_description() {
    global $post;

    if ( ! $post ) {
        return;
    }

    $content = $post->post_content;
    
    if ( ! $content ) {
        return;
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
                            <span class="text-warning small" style="font-size: 0.8rem;">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </span>
                        </div>
                        <span class="small text-muted fw-bold" style="font-size: 0.85rem;">+800 recensioni</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
HTML;

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

function wrap_quantity_addtocart() {
	$script = <<<'JS'
jQuery(function($) {
	var $forms = $('form.cart');
	$forms.each(function(){
		var $form = $(this);
		if ($form.find('.quantity-addtocart-wrapper').length) return;
		var $qty = $form.find('.quantity').first();
		var $btn = $form.find('.single_add_to_cart_button').first();
		if ($qty.length && $btn.length) {
			$qty.add($btn).wrapAll('<div class="quantity-addtocart-wrapper"></div>');
		}
	});
	$('.elementor-widget-jet-single-add-to-cart .quantity, .elementor-widget-jet-single-add-to-cart .single_add_to_cart_button').wrapAll('<div class="quantity-addtocart-wrapper"></div>');
});
JS;
	wp_add_inline_script('wc-single-product', $script);
}
add_action( 'woocommerce_after_add_to_cart_form', 'wrap_quantity_addtocart' );

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
	";
	wp_add_inline_style('main', $css);
}
add_action('wp_enqueue_scripts', 'albalu_add_inline_styles_single_product', 30);

function albalu_static_benefits_below_addtocart() {
	if ( ! is_product() ) return;
	$base = esc_url( get_stylesheet_directory_uri() . '/assets/img' );
	echo '<div class="albalu-purchase-benefits">';
		echo '<div class="d-flex align-items-center item border-top border-bottom"><img class="benefit-icon" src="'.$base.'/truck.svg" alt="Spedizione"><p>Realizziamo e spediamo il tuo ordine in <strong>7/13 giorni lavorativi</strong>.</p></div>';
		echo '<div class="d-flex align-items-center item "><img class="benefit-icon" src="'.$base.'/paypal.svg" alt="Pagamenti PayPal"><p>Pagamenti sicuri con <strong>PayPal e Carte di Credito</strong>.</p></div>';
		echo '<div class="d-flex align-items-center item"><img class="benefit-icon" src="'.$base.'/klarna.svg" alt="Klarna 3 rate"><p>Pagamento in <strong>3 rate senza interessi</strong> con Klarna.</p></div>';
		echo '<div class="d-flex align-items-center item"><img class="benefit-icon" src="'.$base.'/consegna.svg" alt="Contrassegno"><p>Pagamento in <strong>contrassegno</strong> alla consegna.</p></div>';
		echo '<div class="d-flex align-items-center item border-bottom"><img class="benefit-icon" src="'.$base.'/bancario.svg" alt="Bonifico bancario"><p>Pagamento con <strong>bonifico bancario</strong>.</p></div>';
	echo '</div>';
}
add_action( 'woocommerce_after_add_to_cart_form', 'albalu_static_benefits_below_addtocart', 20 );

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

    echo '<div class="albalu-global-faq container py-5">';
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
