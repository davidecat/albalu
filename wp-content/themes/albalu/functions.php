<?php

/**
 * @package Bootscore Child
 *
 * @version 6.0.0
 */


// Exit if accessed directly
defined('ABSPATH') || exit;


/**
 * Enqueue scripts and styles
 */
add_action('wp_enqueue_scripts', 'bootscore_child_enqueue_styles');
function bootscore_child_enqueue_styles() {

  // Compiled main.css
  $modified_bootscoreChildCss = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/css/main.css'));
  wp_enqueue_style('main', get_stylesheet_directory_uri() . '/assets/css/main.css', array('parent-style'), $modified_bootscoreChildCss);

  // style.css
  wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
  
  // custom.js
  // Get modification time. Enqueue file with modification date to prevent browser from loading cached scripts when file content changes. 
  $modificated_CustomJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/custom.js'));
  wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery'), $modificated_CustomJS, false, true);
}

function albalu_setup() {
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'albalu_setup' );

/* Shortcode/Footer: mostra anno corrente */
function mostra_anno_corrente () {
	$year = date('Y');
    return $year;
}
add_shortcode('anno-corrente', 'mostra_anno_corrente');

/* Shortcode/Header: mostra titolo pagina */
function header_page_title($html) {
	if(is_page()) {
		if (is_front_page()) {
			$html = "False";
		} else {
			$html = "True";
		}
	}
	if(is_single()) {
		$html = "False";
	}
	if (is_product()){
		$html = "False";
	}
	if(is_category()) {
		$html = "True";
	}
	return $html;
}
add_shortcode('controlla-titolo-pagina','header_page_title');

/* Woocommerce: ridimensiona le thumbnail della galleria */
add_filter('woocommerce_get_image_size_gallery_thumbnail', function($size) {
	return array(
		'width'  => 200,
		'height' => 200,
		'crop'   => 1,
	);
});

/* Joinchat filters */
add_filter( 'joinchat_post_types_meta_box', '__return_empty_array', 500 );
add_filter( 'joinchat_taxonomies_meta_box', '__return_empty_array', 500 );

/* Image Optimization */
add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
    if( is_front_page() && isset($attr['src']) && strpos($attr['src'], 'albalu-background-home-01.webp') !== false ) {
        $attr['fetchpriority'] = 'high';
        $attr['loading'] = 'eager';
    }
    return $attr;
}, 10, 2 );

add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
    if( is_front_page() && isset($attr['src']) && strpos($attr['src'], 'confezione-omaggio-esempio.webp') !== false ) {
        $attr['fetchpriority'] = 'high';
        $attr['loading'] = 'eager';
    }
    return $attr;
}, 10, 2 );

/* Force "Vedi il prodotto" text on loop buttons to match design */
// add_filter( 'woocommerce_product_add_to_cart_text', function() {
//    return 'Vedi il prodotto';
// } );

/**
 * CUSTOM CATEGORY PAGE LAYOUT
 * (Replaces template overrides with hooks for better compatibility)
 */
add_action('wp', 'albalu_customize_category_layout');
function albalu_customize_category_layout() {
    if ( is_product_category() || is_shop() ) {
        // 1. Remove Breadcrumbs
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        
        // 2. Hide Default Title and Add Custom Header with "ALBALU STORE"
        add_filter('woocommerce_show_page_title', '__return_false');
        add_action('woocommerce_before_main_content', 'albalu_custom_category_header', 20);

        // 3. Move Description to Bottom (after product loop)
        remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
        remove_action('woocommerce_archive_description', 'woocommerce_product_archive_description', 10);
        add_action('woocommerce_after_shop_loop', 'woocommerce_taxonomy_archive_description', 15);
        add_action('woocommerce_after_shop_loop', 'woocommerce_product_archive_description', 15);
        
        // 4. Custom Add to Cart Link (Vedi il prodotto)
        add_filter('woocommerce_loop_add_to_cart_link', 'albalu_custom_add_to_cart_link', 10, 3);
    }
}

function albalu_custom_category_header() {
    ?>
    <header class="woocommerce-products-header mb-4">
        <div class="small fw-bold text-uppercase mb-1" style="letter-spacing: 1px;">ALBALU STORE</div>
        <h1 class="woocommerce-products-header__title page-title"><?php echo single_term_title( '', false ); ?></h1>
    </header>
    <?php
}

function albalu_custom_add_to_cart_link( $html, $product, $args ) {
    $link = get_permalink( $product->get_id() );
    return sprintf(
        '<div class="mt-auto pt-2"><a href="%s" class="text-decoration-none fw-bold small d-inline-block" style="color: var(--color-cta-chiaro);">Vedi il prodotto <i class="fas fa-arrow-right ms-1"></i></a></div>',
        esc_url( $link )
    );
}

/**
 * ACF Options Page Registration
 */
add_action('acf/init', 'albalu_acf_init');
function albalu_acf_init() {
    if( function_exists('acf_add_local_field_group') ) {
        acf_add_local_field_group(array(
            'key' => 'group_albalu_menu_item',
            'title' => 'Menu Item Settings',
            'fields' => array(
                array(
                    'key' => 'field_show_as_mega',
                    'label' => 'Mostra come mega menu',
                    'name' => 'show_as_mega',
                    'type' => 'true_false',
                    'ui' => 1
                ),
                array(
                    'key' => 'field_mega_title',
                    'label' => 'Titolo Mega',
                    'name' => 'mega_title',
                    'type' => 'text'
                ),
                array(
                    'key' => 'field_mega_description',
                    'label' => 'Descrizione',
                    'name' => 'mega_description',
                    'type' => 'textarea',
                    'rows' => 2
                ),
                array(
                    'key' => 'field_mega_img1',
                    'label' => 'Immagine 1',
                    'name' => 'mega_img1',
                    'type' => 'image',
                    'return_format' => 'url',
                    'preview_size' => 'thumbnail'
                ),
                array(
                    'key' => 'field_mega_img2',
                    'label' => 'Immagine 2',
                    'name' => 'mega_img2',
                    'type' => 'image',
                    'return_format' => 'url',
                    'preview_size' => 'thumbnail'
                )
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'nav_menu_item',
                        'operator' => '==',
                        'value' => 'all'
                    )
                )
            )
        ));

        // Homepage Categories Fields
        acf_add_local_field_group(array(
            'key' => 'group_homepage_categories',
            'title' => 'Homepage Categories',
            'fields' => array(
                array(
                    'key' => 'field_home_cats',
                    'label' => 'Categorie Homepage',
                    'name' => 'homepage_categories',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Aggiungi Categoria',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_home_cat_obj',
                            'label' => 'Categoria',
                            'name' => 'category',
                            'type' => 'taxonomy',
                            'taxonomy' => 'product_cat',
                            'field_type' => 'select',
                            'return_format' => 'object'
                        ),
                        array(
                            'key' => 'field_home_cat_custom_title',
                            'label' => 'Titolo Personalizzato (Opzionale)',
                            'name' => 'custom_title',
                            'type' => 'text',
                            'instructions' => 'Lascia vuoto per usare il nome originale della categoria'
                        ),
                        array(
                            'key' => 'field_home_cat_custom_img',
                            'label' => 'Immagine Personalizzata (Opzionale)',
                            'name' => 'custom_image',
                            'type' => 'image',
                            'return_format' => 'url',
                            'preview_size' => 'thumbnail',
                            'instructions' => 'Lascia vuoto per usare l\'immagine della categoria'
                        )
                    )
                )
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page_type',
                        'operator' => '==',
                        'value' => 'front_page'
                    )
                )
            )
        ));
    }
}

add_filter( 'woocommerce_loop_add_to_cart_link', 'replace_add_to_cart_button_class', 10, 2 );

function replace_add_to_cart_button_class( $button_html, $product ) {
    // Check if the current button uses btn-primary class and replace it
    if ( strpos( $button_html, 'btn-primary' ) !== false ) {
        $button_html = str_replace( 'btn-primary', 'btn-link text-start px-0 text-decoration-none ', $button_html );
    } 
    // The default WooCommerce class for the button is 'button' and 'add_to_cart_button'
    // If your theme uses the default 'button' class, you can replace that instead:
    // $button_html = str_replace( 'button', 'btn-secondary', $button_html );

    return $button_html;
}
// Change "Add to cart" text on shop archives
add_filter( 'woocommerce_product_add_to_cart_text', 'custom_woocommerce_product_add_to_cart_text' );
function custom_woocommerce_product_add_to_cart_text() {
    return __( 'Vedi il prodotto', 'woocommerce' ); // Replace "Buy Now" with your desired text
}
