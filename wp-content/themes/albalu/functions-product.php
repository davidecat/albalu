<?php
/**
 * @package Bootscore Child
 *
 * @version 6.0.0
 */


// Exit if accessed directly
defined('ABSPATH') || exit;


/**
 * 1. Remove All Tabs & Render Separately
 */

// Remove ALL Tabs (Description, Reviews, Additional Info)
add_filter( 'woocommerce_product_tabs', 'albalu_remove_all_tabs', 98 );
function albalu_remove_all_tabs( $tabs ) {
    return array(); // Removes all tabs
}

// Render Description After Single Product (Full Width)
add_action( 'woocommerce_after_single_product', 'albalu_render_product_description', 10 );
function albalu_render_product_description() {
    global $post;

    if ( ! $post ) {
        return;
    }

    $content = $post->post_content;
    
    if ( ! $content ) {
        return;
    }

    echo '<div class="albalu-product-description container py-5" style="clear:both;">';
    echo '<h2 class="h3 mb-4">Descrizione</h2>';
    echo apply_filters( 'the_content', $content );
    echo '</div>';
}



// Render Global FAQ Section (Outside Tabs)
add_action( 'woocommerce_after_single_product', 'albalu_render_global_faq_section', 20 );

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
