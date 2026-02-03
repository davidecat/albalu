<?php
/**
 * Enqueue scripts and styles
 */
function albalu_enqueue_styles() {
    // Enqueue parent style
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    
    // Enqueue Google Fonts (Roboto)
    wp_enqueue_style('google-fonts-roboto', 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap', [], null);

    // Enqueue child style
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'));
    
    // Enqueue migrated assets (example)
    // wp_enqueue_style('woo-product-detail', get_stylesheet_directory_uri() . '/assets/css/woocommerce-prodotto-dettaglio.css');
}
add_action('wp_enqueue_scripts', 'albalu_enqueue_styles');

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
