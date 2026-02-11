<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.1.0' );

/* Load child theme scripts & styles. */
function hello_elementor_child_scripts_styles() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

/* Shortcode/Footer: mostra anno corrente */
function mostra_anno_corrente () {
	$year = date('Y');
return $year;
}
add_shortcode('anno-corrente', 'mostra_anno_corrente');

/* Shortcode/Header: mostra titolo pagina */
function header_page_title($html) {
	/* 1. Controllo se è una pagina, diversa dalla homepage */
	if(is_page()) { // mostra su tutte le pagine tranne la homepage
		if (is_front_page()) {
			$html = "False";
		} else {
			$html = "True";
		}
	}
	/* 2. Controllo se è un post */
	if(is_single()) {
		$html = "False";
	}

	/* 3. Controllo se è un prodotto */
	if (is_product()){ // nascondi per i prodotti
		$html = "False";
	}

	/* 4. Controllo se è un archivio di categoria */
	if(is_category()) {
		$html = "True";
	}
	return $html;
}
add_shortcode('controlla-titolo-pagina','header_page_title');

/* Woocommerce: ridimensiona le thumbnail della galleria con le immagini del prodotto */
add_filter('woocommerce_get_image_size_gallery_thumbnail', function($size) {
	return array(
		'width'  => 200,
		'height' => 200,
		'crop'   => 1,
	);
});

/* Joinchat: nascondi metabox in post e tassonomie */
add_filter( 'joinchat_post_types_meta_box', '__return_empty_array', 500 );
add_filter( 'joinchat_taxonomies_meta_box', '__return_empty_array', 500 );

add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
    if( is_front_page() && isset($attr['src']) && strpos($attr['src'], 'albalu-background-home-01.webp') !== false ) {
        $attr['fetchpriority'] = 'high';
        $attr['loading'] = 'eager'; // evita lazy
    }
    return $attr;
}, 10, 2 );

add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
    if( is_front_page() && isset($attr['src']) && strpos($attr['src'], 'confezione-omaggio-esempio.webp') !== false ) {
        $attr['fetchpriority'] = 'high';
        $attr['loading'] = 'eager'; // evita lazy
    }
    return $attr;
}, 10, 2 );

add_filter('woocommerce_get_canonical_product_url', function($url, $product) {
    if ($product->is_type('variation')) {
        return $product->get_parent_id() ? get_permalink($product->get_parent_id()) : $url;
    }
    return $url;
}, 10, 2);
