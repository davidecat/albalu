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

    echo '<div class="albalu-product-description container py-5">';
    echo '<h2 class="h3 mb-4">Descrizione</h2>';
    echo apply_filters( 'the_content', $content );
    echo '</div>';
}


/**
 * 2. FAQ Global Settings (ACF Options Page - Repeater)
 */

// Register Options Page for Global FAQ
add_action('acf/init', 'albalu_register_faq_options_page');
function albalu_register_faq_options_page() {
    if( function_exists('acf_add_options_sub_page') ) {
        acf_add_options_sub_page(array(
            'page_title'    => 'FAQ Prodotti',
            'menu_title'    => 'FAQ Prodotti',
            'parent_slug'   => 'edit.php?post_type=product',
            'menu_slug'     => 'faq-prodotti',
            'capability'    => 'edit_posts',
            'redirect'      => false,
        ));
    }
}

// Admin notice if ACF Options Page is not available (Only for Admin)
add_action('admin_notices', 'albalu_acf_options_page_missing_notice');
function albalu_acf_options_page_missing_notice() {
    // Check if ACF is active but Options Page function is missing (i.e. Free version)
    if ( function_exists('acf_register_block_type') && ! function_exists('acf_add_options_page') && current_user_can('activate_plugins') ) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'Attenzione: "ACF Pro" è richiesto per le FAQ Globali (Pagina Opzioni). Sembra che tu stia usando la versione Free.', 'bootscore-child' ); ?></p>
        </div>
        <?php
    }
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
    echo '<div class="col-12">';
    
    echo '<h3 class="mb-4">Domande frequenti</h3>';
    echo '<p class="mb-4 text-uppercase text-muted small fw-bold">Se non trovi la risposta che cerchi contattaci.</p>';

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
            echo '<div class="accordion-body px-0 pb-3 text-muted small">';
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
