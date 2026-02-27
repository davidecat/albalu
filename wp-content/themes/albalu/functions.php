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

  // Enqueue Swiper CSS
  wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.0.0');

  // Enqueue Swiper JS
  wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.0.0', true);
  
  // custom.js
  // Get modification time. Enqueue file with modification date to prevent browser from loading cached scripts when file content changes. 
  $modificated_CustomJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/custom.js'));
  wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery', 'swiper-js'), $modificated_CustomJS, false, true);
}

function albalu_setup() {
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    register_nav_menu( 'menu-mobile', 'Menu Mobile' );
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

        // Promo Section (Global/Reusable)
        acf_add_local_field_group(array(
            'key' => 'group_page_sections',
            'title' => 'Page Sections (Flexible)',
            'fields' => array(
                array(
                    'key' => 'field_page_sections',
                    'label' => 'Sections',
                    'name' => 'page_sections',
                    'type' => 'flexible_content',
                    'button_label' => 'Aggiungi Sezione',
                    'layouts' => array(
                        array(
                            'key' => 'layout_page_sections_promo',
                            'name' => 'promo',
                            'label' => 'Promo',
                            'display' => 'block',
                            'sub_fields' => array(
                                array(
                                    'key' => 'field_page_sections_promo_enabled',
                                    'label' => 'Abilita',
                                    'name' => 'enabled',
                                    'type' => 'true_false',
                                    'ui' => 1,
                                    'default_value' => 1,
                                ),
                                array(
                                    'key' => 'field_page_sections_promo_image_position',
                                    'label' => 'Posizione Immagine',
                                    'name' => 'image_position',
                                    'type' => 'select',
                                    'choices' => array(
                                        'right' => 'Immagine a Destra',
                                        'left' => 'Immagine a Sinistra',
                                    ),
                                    'default_value' => 'right',
                                ),
                                array(
                                    'key' => 'field_page_sections_promo_subtitle',
                                    'label' => 'Sottotitolo (Small)',
                                    'name' => 'subtitle',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_promo_title',
                                    'label' => 'Titolo Principale',
                                    'name' => 'title',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_promo_content',
                                    'label' => 'Contenuto',
                                    'name' => 'content',
                                    'type' => 'wysiwyg',
                                    'media_upload' => false,
                                ),
                                array(
                                    'key' => 'field_page_sections_promo_btn_text',
                                    'label' => 'Testo Bottone',
                                    'name' => 'btn_text',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_promo_btn_url',
                                    'label' => 'Link Bottone',
                                    'name' => 'btn_url',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_promo_image',
                                    'label' => 'Immagine',
                                    'name' => 'image',
                                    'type' => 'image',
                                    'return_format' => 'url',
                                    'preview_size' => 'medium',
                                ),
                            ),
                        ),
                        array(
                            'key' => 'layout_page_sections_testimonials',
                            'name' => 'testimonials',
                            'label' => 'Testimonials',
                            'display' => 'block',
                            'sub_fields' => array(
                                array(
                                    'key' => 'field_page_sections_testimonials_enabled',
                                    'label' => 'Abilita',
                                    'name' => 'enabled',
                                    'type' => 'true_false',
                                    'ui' => 1,
                                    'default_value' => 1,
                                ),
                                array(
                                    'key' => 'field_page_sections_testimonials_title',
                                    'label' => 'Titolo',
                                    'name' => 'title',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_testimonials_content',
                                    'label' => 'Contenuto',
                                    'name' => 'content',
                                    'type' => 'wysiwyg',
                                    'media_upload' => false,
                                ),
                                array(
                                    'key' => 'field_page_sections_testimonials_reviews',
                                    'label' => 'Recensioni',
                                    'name' => 'reviews',
                                    'type' => 'repeater',
                                    'button_label' => 'Aggiungi Recensione',
                                    'sub_fields' => array(
                                        array(
                                            'key' => 'field_testimonial_name',
                                            'label' => 'Nome',
                                            'name' => 'name',
                                            'type' => 'text',
                                        ),
                                        array(
                                            'key' => 'field_testimonial_date',
                                            'label' => 'Data',
                                            'name' => 'date',
                                            'type' => 'text',
                                            'placeholder' => 'es. 19 Ottobre 2025'
                                        ),
                                        array(
                                            'key' => 'field_testimonial_text',
                                            'label' => 'Testo Recensione',
                                            'name' => 'text',
                                            'type' => 'textarea',
                                            'rows' => 3
                                        ),
                                        array(
                                            'key' => 'field_testimonial_initials',
                                            'label' => 'Iniziali',
                                            'name' => 'initials',
                                            'type' => 'text',
                                            'maxlength' => 3,
                                            'instructions' => 'es. CS'
                                        ),
                                        array(
                                            'key' => 'field_testimonial_img',
                                            'label' => 'Immagine Utente (Opzionale)',
                                            'name' => 'img',
                                            'type' => 'image',
                                            'return_format' => 'array',
                                            'preview_size' => 'thumbnail'
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'key' => 'layout_page_sections_newsletter',
                            'name' => 'newsletter',
                            'label' => 'Newsletter',
                            'display' => 'block',
                            'sub_fields' => array(
                                array(
                                    'key' => 'field_page_sections_newsletter_enabled',
                                    'label' => 'Abilita',
                                    'name' => 'enabled',
                                    'type' => 'true_false',
                                    'ui' => 1,
                                    'default_value' => 1,
                                ),
                                array(
                                    'key' => 'field_page_sections_newsletter_subtitle',
                                    'label' => 'Sottotitolo (Small)',
                                    'name' => 'subtitle',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_newsletter_title',
                                    'label' => 'Titolo',
                                    'name' => 'title',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_newsletter_content',
                                    'label' => 'Contenuto',
                                    'name' => 'content',
                                    'type' => 'wysiwyg',
                                    'media_upload' => false,
                                ),
                                array(
                                    'key' => 'field_page_sections_newsletter_btn_text',
                                    'label' => 'Testo Bottone',
                                    'name' => 'btn_text',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_newsletter_btn_url',
                                    'label' => 'Link Bottone',
                                    'name' => 'btn_url',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_newsletter_bg_color',
                                    'label' => 'Colore Sfondo',
                                    'name' => 'bg_color',
                                    'type' => 'color_picker',
                                    'default_value' => '#9EA6A9',
                                ),
                            ),
                        ),
                        array(
                            'key' => 'layout_page_sections_features',
                            'name' => 'features',
                            'label' => 'Features Icons',
                            'display' => 'block',
                            'sub_fields' => array(
                                array(
                                    'key' => 'field_page_sections_features_enabled',
                                    'label' => 'Abilita',
                                    'name' => 'enabled',
                                    'type' => 'true_false',
                                    'ui' => 1,
                                    'default_value' => 1,
                                ),
                                array(
                                    'key' => 'field_page_sections_features_items',
                                    'label' => 'Elementi',
                                    'name' => 'items',
                                    'type' => 'repeater',
                                    'layout' => 'block',
                                    'button_label' => 'Aggiungi Elemento',
                                    'sub_fields' => array(
                                        array(
                                            'key' => 'field_features_icon',
                                            'label' => 'Icona',
                                            'name' => 'icon',
                                            'type' => 'image',
                                            'return_format' => 'url',
                                            'preview_size' => 'thumbnail',
                                        ),
                                        array(
                                            'key' => 'field_features_title',
                                            'label' => 'Titolo',
                                            'name' => 'title',
                                            'type' => 'text',
                                        ),
                                        array(
                                            'key' => 'field_features_desc',
                                            'label' => 'Descrizione',
                                            'name' => 'description',
                                            'type' => 'textarea',
                                            'rows' => 3,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'key' => 'layout_page_sections_gallery',
                            'name' => 'gallery',
                            'label' => 'Gallery',
                            'display' => 'block',
                            'sub_fields' => array(
                                array(
                                    'key' => 'field_page_sections_gallery_enabled',
                                    'label' => 'Abilita',
                                    'name' => 'enabled',
                                    'type' => 'true_false',
                                    'ui' => 1,
                                    'default_value' => 1,
                                ),
                                array(
                                    'key' => 'field_page_sections_gallery_title',
                                    'label' => 'Titolo',
                                    'name' => 'title',
                                    'type' => 'text',
                                ),
                                array(
                                    'key' => 'field_page_sections_gallery_content',
                                    'label' => 'Contenuto',
                                    'name' => 'content',
                                    'type' => 'wysiwyg',
                                    'media_upload' => false,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                ),
            ),
        ));
    }
}

function albalu_render_promo_section( $section ) {
    if ( ! is_array( $section ) ) {
        return '';
    }

    $layout = isset( $section['layout'] ) ? (string) $section['layout'] : 'right';
    $subtitle = isset( $section['subtitle'] ) ? (string) $section['subtitle'] : '';
    $title = isset( $section['title'] ) ? (string) $section['title'] : '';
    $content = isset( $section['content'] ) ? (string) $section['content'] : '';
    $btn_text = isset( $section['btn_text'] ) ? (string) $section['btn_text'] : '';
    $btn_url = isset( $section['btn_url'] ) ? (string) $section['btn_url'] : '';
    $image = isset( $section['image'] ) ? (string) $section['image'] : '';

    $btn_url = albalu_normalize_url( $btn_url );

    if ( $layout === 'left' ) {
        $text_classes = 'col-lg-7 order-2 order-lg-2';
        $image_classes = 'col-lg-5 order-1 order-lg-1 mb-4 mb-lg-0';
    } else {
        $text_classes = 'col-lg-7 order-2 order-lg-1';
        $image_classes = 'col-lg-5 order-1 order-lg-2 mb-4 mb-lg-0';
    }

    ob_start();
    ?>
    <section class="promo-section-dynamic py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="<?php echo esc_attr( $text_classes ); ?>">
                    <?php if ( $subtitle ) : ?>
                        <span class="text-uppercase text-muted small fw-bold ls-1"><?php echo esc_html( $subtitle ); ?></span>
                    <?php endif; ?>

                    <?php if ( $title ) : ?>
                        <h2 class="h1 fw-bold my-3"><?php echo wp_kses_post( $title ); ?></h2>
                    <?php endif; ?>

                    <?php if ( $content ) : ?>
                        <div class="mb-4 text-secondary promo-content">
                            <?php echo wp_kses_post( $content ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $btn_text && $btn_url ) : ?>
                        <a href="<?php echo esc_url( $btn_url ); ?>" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm">
                            <?php echo esc_html( $btn_text ); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="<?php echo esc_attr( $image_classes ); ?>">
                    <div class="ratio ratio-4x3 rounded-3 overflow-hidden">
                        <?php if ( $image ) : ?>
                            <img src="<?php echo esc_url( $image ); ?>" class="object-fit-cover" alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

function albalu_normalize_url( $value ) {
    $value = trim( (string) $value );
    if ( $value === '' ) {
        return '';
    }

    if ( preg_match( '#^(https?:)?//#i', $value ) ) {
        return $value;
    }

    if ( preg_match( '#^(mailto:|tel:)#i', $value ) ) {
        return $value;
    }

    if ( strpos( $value, '#' ) === 0 ) {
        return $value;
    }

    if ( strpos( $value, '/' ) !== 0 ) {
        $value = '/' . $value;
    }

    return home_url( $value );
}

function albalu_render_simple_section( $section_class, $title, $content ) {
    $section_class = trim( (string) $section_class );
    $title = (string) $title;
    $content = (string) $content;

    if ( $title === '' && $content === '' ) {
        return '';
    }

    ob_start();
    ?>
    <section class="<?php echo esc_attr( $section_class ); ?> py-5">
        <div class="container">
            <?php if ( $title ) : ?>
                <h2 class="h2 mb-3"><?php echo esc_html( $title ); ?></h2>
            <?php endif; ?>
            <?php if ( $content ) : ?>
                <div class="text-secondary">
                    <?php echo wp_kses_post( $content ); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

function albalu_render_testimonials_slider_section( $title, $description, $reviews = array() ) {
    $title = (string) $title;
    $description = (string) $description;

    if ( $title === '' ) {
        $title = 'Le <strong>testimonianze</strong> dei nostri clienti';
    }

    if ( $description === '' ) {
        $description = 'Crediamo nella forza dei dettagli, nella ricerca dei materiali, nella creazione della linea e siamo felici di accompagnare il cliente dalla scelta del prodotto fino all\'assistenza post vendita per soddisfare ogni sua esigenza.';
    }

    if ( empty( $reviews ) || ! is_array( $reviews ) ) {
        $reviews = array(
            array(
                'name' => 'Chiara Spanevello',
                'date' => '19 Ottobre 2025',
                'text' => 'Ciao, ho aspettato arrivasse la Cresima di mia figlia prima di scrivere una recensione, ovviamente positiva! Ordinate bomboniere con confetti e...',
                'initials' => 'CS',
                'img' => '/wp-content/uploads/2026/01/user-1.jpg',
            ),
            array(
                'name' => 'FM March',
                'date' => '17 Ottobre 2025',
                'text' => 'Ho ordinato delle bomboniere per la laurea e sono rimasta davvero soddisfatta! Il servizio offerto è stato rapidissimo, la comunicazione...',
                'initials' => 'FM',
                'img' => '',
            ),
            array(
                'name' => 'Anna Di',
                'date' => '15 Ottobre 2025',
                'text' => 'Le bomboniere sono bellissime e di qualità. Vi ringrazio per la professionalità. A presto ❤️',
                'initials' => 'AD',
                'img' => '',
            ),
            array(
                'name' => 'Eliane Jabbour',
                'date' => '13 Ottobre 2025',
                'text' => 'Bomboniere bellissime e di ottima qualità, prezzo giusto. Consegna rapida e servizio clienti disponibile e attento a tutte le modifiche. Tutto...',
                'initials' => 'EJ',
                'img' => '/wp-content/uploads/2026/01/user-4.jpg',
            ),
            array(
                'name' => 'Maria Rossi',
                'date' => '10 Ottobre 2025',
                'text' => 'Esperienza fantastica! Prodotti di alta qualità e spedizione velocissima. Consiglio vivamente a tutti per le vostre occasioni speciali.',
                'initials' => 'MR',
                'img' => '',
            ),
            array(
                'name' => 'Luca Bianchi',
                'date' => '05 Ottobre 2025',
                'text' => 'Gentilezza e professionalità. Le bomboniere sono arrivate perfette e confezionate con molta cura. Grazie mille!',
                'initials' => 'LB',
                'img' => '',
            ),
        );
    }

    ob_start();
    ?>
    <section class="testimonials-section py-5" style="background-color: #eae3e0">
        <div class="container-custom">
            <div class="text-center mb-5">
                <span class="text-uppercase text-muted small fw-bold ls-1">Dicono di noi</span>
                <h2 class="h1 fw-bold mt-2"><?php echo wp_kses_post( $title ); ?></h2>
                <p class="text-secondary mx-auto" style="max-width: 800px;">
                    <?php echo esc_html( $description ); ?>
                </p>
            </div>

            <div class="swiper testimonial-swiper pb-5">
                <div class="swiper-wrapper">
                    <?php foreach ( $reviews as $review ) : 
                        $img_url = isset($review['img']) ? $review['img'] : '';
                        // If it's an ACF image array, get the URL
                        if ( is_array( $img_url ) && isset( $img_url['url'] ) ) {
                            $img_url = $img_url['url'];
                        }
                    ?>
                        <div class="swiper-slide p-2">
                            <div class="testimonial-box position-relative bg-white p-4 rounded-3 shadow-sm mb-4">
                                <!-- Google G Logo -->
                                <div class="position-absolute top-0 end-0 mt-3 me-3">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48 48"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path></defs><clipPath id="b"><use xlink:href="#a"></use></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"></path><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"></path><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"></path><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"></path></svg>
                                    </div>
                                </div>

                                <div class="text-warning mb-2"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                <p class="text-dark mb-2 testimonial-text" style="font-size: 0.95rem; line-height: 1.5; min-height: 70px;">
                                    <?php echo esc_html( $review['text'] ); ?>
                                </p>
                                <a href="#" class="text-secondary small text-decoration-none d-inline-block">Leggi di più</a>
                            </div>

                            <div class="d-flex align-items-center ms-3">
                                <?php if ( ! empty( $img_url ) ) : ?>
                                    <img src="<?php echo esc_url( $img_url ); ?>" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;" alt="<?php echo esc_attr( $review['name'] ); ?>">
                                <?php else : ?>
                                    <div class="rounded-circle overflow-hidden me-3" style="width: 50px; height: 50px;">
                                        <img src="/wp-content/uploads/2026/01/user-placeholder.jpg" onerror="this.src='https://secure.gravatar.com/avatar/?s=50&d=mm&r=g'" alt="<?php echo esc_attr( $review['name'] ); ?>" class="img-fluid w-100 h-100 object-fit-cover">
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 1rem; color: #003057 !important;"><?php echo esc_html( $review['name'] ); ?></h6>
                                    <small class="text-muted" style="font-size: 0.85rem;"><?php echo esc_html( $review['date'] ); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev" style="color: #000; background: #fff; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); left: 0;"></div>
                <div class="swiper-button-next" style="color: #000; background: #fff; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); right: 0;"></div>
            </div>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

function albalu_render_newsletter_section( $subtitle, $title, $content, $btn_text, $btn_url, $bg_color = '#9EA6A9' ) {
    $subtitle = (string) $subtitle;
    $title = (string) $title;
    $content = (string) $content;
    $btn_text = (string) $btn_text;
    $btn_url = (string) $btn_url;
    $bg_color = (string) $bg_color;

    if ( $bg_color === '' ) {
        $bg_color = '#9EA6A9';
    }

    if ( $subtitle === '' && $title === '' && $content === '' ) {
        // Fallback content if empty
        $subtitle = 'Rendiamo memorabile il tuo evento';
        $title = 'Iscriviti alla newsletter di Albalù';
        $content = 'Iscriviti alla nostra newsletter e ricevi in esclusiva idee originali per le tue bomboniere, sconti riservati fino al 20%, anteprime sulle nuove collezioni e consigli personalizzati per ogni evento speciale. Non perdere le offerte dedicate agli iscritti!';
        $btn_text = 'Clicca qui';
        $btn_url = '#';
    }

    ob_start();
    ?>
    <section class="newsletter-section py-5 text-white" style="background-color: <?php echo esc_attr( $bg_color ); ?> !important;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 text-start">
                    <?php if ( $subtitle ) : ?>
                        <span class="text-uppercase small fw-bold ls-1 text-white-50"><?php echo esc_html( $subtitle ); ?></span>
                    <?php endif; ?>
                    
                    <?php if ( $title ) : ?>
                        <h2 class="h1 fw-normal my-3 text-white"><?php echo wp_kses_post( $title ); ?></h2>
                    <?php endif; ?>
                    
                    <?php if ( $content ) : ?>
                        <div class="mb-4 text-white">
                            <?php echo wp_kses_post( $content ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $btn_text ) : ?>
                        <div class="d-flex justify-content-start">
                            <a href="<?php echo esc_url( $btn_url ); ?>" class="btn btn-info px-4 py-2 text-white shadow-sm" style="background-color: #76A9B4; border: none; border-radius: 0;">
                                <?php echo esc_html( $btn_text ); ?> <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

function albalu_render_features_section( $items = array() ) {
    if ( empty( $items ) ) {
        // Fallback items
        $items = array(
            array(
                'icon' => '/wp-content/uploads/2026/01/albalu-customere-care-1.svg',
                'title' => 'Assistenza Clienti',
                'description' => 'Siamo sempre disponibili per aiutarti a scegliere la bomboniera perfetta. Contattaci per un supporto rapido e personalizzato.'
            ),
            array(
                'icon' => '/wp-content/uploads/2026/01/albalu-quality-1.svg',
                'title' => '100% Made in Italy',
                'description' => 'Le nostre bomboniere sono autentici prodotti artigianali italiani, realizzati con materiali di alta qualità e cura per i dettagli.'
            ),
            array(
                'icon' => '/wp-content/uploads/2026/01/albalu-delivery-1.svg',
                'title' => 'Spedizione Gratuita da 149€',
                'description' => 'Su ordini superiori a 149€, la spedizione è gratuita! Ricevi le tue bomboniere direttamente a casa, senza costi aggiuntivi.'
            )
        );
    }

    ob_start();
    ?>
    <section class="features-section py-5 bg-white">
        <div class="container">
            <div class="row g-4">
                <?php foreach ( $items as $item ) : 
                    $icon = isset( $item['icon'] ) ? $item['icon'] : '';
                    $title = isset( $item['title'] ) ? $item['title'] : '';
                    $description = isset( $item['description'] ) ? $item['description'] : '';
                ?>
                <div class="col-md-4">
                    <div class="h-100 ps-4 border-start" style="border-color: #EAE3E0 !important;">
                        <?php if ( $icon ) : ?>
                        <div class="mb-3">
                            <img src="<?php echo esc_url( $icon ); ?>" alt="<?php echo esc_attr( $title ); ?>" style="width: 70px; height: 70px;">
                        </div>
                        <?php endif; ?>
                        
                        <?php if ( $title ) : ?>
                        <h5 class="fw-bold h6 text-uppercase mb-2"><?php echo esc_html( $title ); ?></h5>
                        <?php endif; ?>
                        
                        <?php if ( $description ) : ?>
                        <p class="small text-secondary mb-0"><?php echo wp_kses_post( $description ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

function albalu_render_page_section_by_layout( $layout, $index = 1, $post_id = 0 ) {
    $layout = (string) $layout;
    $index = max( 1, (int) $index );

    $post_id = (int) $post_id;
    if ( $post_id <= 0 ) {
        $post_id = (int) get_the_ID();
    }

    if ( ! function_exists( 'get_field' ) ) {
        return '';
    }

    $rows = get_field( 'page_sections', $post_id );
    if ( ! is_array( $rows ) ) {
        return '';
    }

    $matched = array();
    foreach ( $rows as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }

        if ( ( $row['acf_fc_layout'] ?? '' ) !== $layout ) {
            continue;
        }

        if ( isset( $row['enabled'] ) && ! $row['enabled'] ) {
            continue;
        }

        $matched[] = $row;
    }

    $selected = $matched[ $index - 1 ] ?? null;
    if ( ! is_array( $selected ) ) {
        return '';
    }

    if ( $layout === 'promo' ) {
        $section = array(
            'layout' => isset( $selected['image_position'] ) ? (string) $selected['image_position'] : 'right',
            'subtitle' => isset( $selected['subtitle'] ) ? (string) $selected['subtitle'] : '',
            'title' => isset( $selected['title'] ) ? (string) $selected['title'] : '',
            'content' => isset( $selected['content'] ) ? (string) $selected['content'] : '',
            'btn_text' => isset( $selected['btn_text'] ) ? (string) $selected['btn_text'] : '',
            'btn_url' => isset( $selected['btn_url'] ) ? (string) $selected['btn_url'] : '',
            'image' => isset( $selected['image'] ) ? (string) $selected['image'] : '',
        );

        return albalu_render_promo_section( $section );
    }

    if ( $layout === 'newsletter' ) {
        $subtitle = isset( $selected['subtitle'] ) ? (string) $selected['subtitle'] : '';
        $title = isset( $selected['title'] ) ? (string) $selected['title'] : '';
        $content = isset( $selected['content'] ) ? (string) $selected['content'] : '';
        $btn_text = isset( $selected['btn_text'] ) ? (string) $selected['btn_text'] : '';
        $btn_url = isset( $selected['btn_url'] ) ? (string) $selected['btn_url'] : '';
        $bg_color = isset( $selected['bg_color'] ) ? (string) $selected['bg_color'] : '#9EA6A9';
        
        return albalu_render_newsletter_section( $subtitle, $title, $content, $btn_text, $btn_url, $bg_color );
    }

    if ( $layout === 'features' ) {
        $items = isset( $selected['items'] ) && is_array( $selected['items'] ) ? $selected['items'] : array();
        return albalu_render_features_section( $items );
    }

    if ( $layout === 'testimonials' ) {
        $title = isset( $selected['title'] ) ? (string) $selected['title'] : '';
        $description = isset( $selected['content'] ) ? wp_strip_all_tags( (string) $selected['content'] ) : '';
        $reviews = isset( $selected['reviews'] ) && is_array( $selected['reviews'] ) ? $selected['reviews'] : array();
        return albalu_render_testimonials_slider_section( $title, $description, $reviews );
    }

    if ( $layout === 'gallery' ) {
        $title = isset( $selected['title'] ) ? (string) $selected['title'] : '';
        $content = isset( $selected['content'] ) ? (string) $selected['content'] : '';
        return albalu_render_simple_section( 'page-section-gallery', $title, $content );
    }

    return '';
}

function albalu_page_section_shortcode( $atts = array() ) {
    $atts = shortcode_atts(
        array(
            'layout' => '',
            'index' => 1,
            'post_id' => 0,
        ),
        $atts,
        'albalu_page_section'
    );

    $layout = (string) $atts['layout'];
    $index = (int) $atts['index'];
    $post_id = (int) $atts['post_id'];

    if ( $layout === '' ) {
        return '';
    }

    return albalu_render_page_section_by_layout( $layout, $index, $post_id );
}
add_shortcode( 'albalu_page_section', 'albalu_page_section_shortcode' );

function albalu_render_page_sections( $post_id = 0 ) {
    if ( ! function_exists( 'have_rows' ) ) {
        return '';
    }

    $post_id = (int) $post_id;
    if ( $post_id <= 0 ) {
        $post_id = (int) get_the_ID();
    }

    if ( ! have_rows( 'page_sections', $post_id ) ) {
        return '';
    }

    ob_start();
    while ( have_rows( 'page_sections', $post_id ) ) {
        the_row();

        $layout = get_row_layout();
        if ( $layout === 'promo' ) {
            $enabled = (bool) get_sub_field( 'enabled' );
            if ( ! $enabled ) {
                continue;
            }

            $section = array(
                'layout' => (string) get_sub_field( 'image_position' ),
                'subtitle' => (string) get_sub_field( 'subtitle' ),
                'title' => (string) get_sub_field( 'title' ),
                'content' => (string) get_sub_field( 'content' ),
                'btn_text' => (string) get_sub_field( 'btn_text' ),
                'btn_url' => (string) get_sub_field( 'btn_url' ),
                'image' => (string) get_sub_field( 'image' ),
            );

            echo albalu_render_promo_section( $section );
            continue;
        }

        if ( $layout === 'newsletter' ) {
            $enabled = (bool) get_sub_field( 'enabled' );
            if ( ! $enabled ) {
                continue;
            }

            $subtitle = (string) get_sub_field( 'subtitle' );
            $title = (string) get_sub_field( 'title' );
            $content = (string) get_sub_field( 'content' );
            $btn_text = (string) get_sub_field( 'btn_text' );
            $btn_url = (string) get_sub_field( 'btn_url' );
            $bg_color = (string) get_sub_field( 'bg_color' );

            echo albalu_render_newsletter_section( $subtitle, $title, $content, $btn_text, $btn_url, $bg_color );
            continue;
        }

        if ( $layout === 'features' ) {
            $enabled = (bool) get_sub_field( 'enabled' );
            if ( ! $enabled ) {
                continue;
            }

            $items = get_sub_field( 'items' );
            if ( ! is_array( $items ) ) {
                $items = array();
            }

            echo albalu_render_features_section( $items );
            continue;
        }

        if ( $layout === 'testimonials' ) {
            $enabled = (bool) get_sub_field( 'enabled' );
            if ( ! $enabled ) {
                continue;
            }

            $title = (string) get_sub_field( 'title' );
            $content = (string) get_sub_field( 'content' );
            $reviews = get_sub_field( 'reviews' );
            
            if ( ! is_array( $reviews ) ) {
                $reviews = array();
            }

            // Strip tags from content for description parameter as expected by the function
            $description = wp_strip_all_tags( $content );
            
            echo albalu_render_testimonials_slider_section( $title, $description, $reviews );
            continue;
        }

        if ( $layout === 'gallery' ) {
            $enabled = (bool) get_sub_field( 'enabled' );
            if ( ! $enabled ) {
                continue;
            }

            $title = (string) get_sub_field( 'title' );
            $content = (string) get_sub_field( 'content' );
            ?>
            <section class="page-section-gallery py-5">
                <div class="container">
                    <?php if ( $title ) : ?>
                        <h2 class="h2 mb-3"><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $content ) : ?>
                        <div class="text-secondary">
                            <?php echo wp_kses_post( $content ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php
            continue;
        }
    }

    return (string) ob_get_clean();
}

function albalu_page_sections_shortcode( $atts = array() ) {
    $atts = shortcode_atts(
        array(
            'post_id' => 0,
        ),
        $atts,
        'albalu_page_sections'
    );

    $post_id = (int) $atts['post_id'];
    if ( $post_id <= 0 ) {
        $post_id = (int) get_the_ID();
    }

    return albalu_render_page_sections( $post_id );
}
add_shortcode( 'albalu_page_sections', 'albalu_page_sections_shortcode' );

function albalu_promo_section_shortcode( $atts = array() ) {
    $atts = shortcode_atts(
        array(
            'index' => 1,
            'post_id' => 0,
        ),
        $atts,
        'albalu_promo_section'
    );

    $post_id = (int) $atts['post_id'];
    if ( $post_id <= 0 ) {
        $post_id = get_the_ID();
    }

    $index = max( 1, (int) $atts['index'] );

    if ( ! function_exists( 'get_field' ) ) {
        return '';
    }

    $rows = get_field( 'page_sections', $post_id );
    if ( is_array( $rows ) ) {
        $promo_rows = array();
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            if ( ! isset( $row['acf_fc_layout'] ) || $row['acf_fc_layout'] !== 'promo' ) {
                continue;
            }

            if ( isset( $row['enabled'] ) && ! $row['enabled'] ) {
                continue;
            }

            $promo_rows[] = $row;
        }

        $selected = isset( $promo_rows[ $index - 1 ] ) ? $promo_rows[ $index - 1 ] : null;
        if ( is_array( $selected ) ) {
            $section = array(
                'layout' => isset( $selected['image_position'] ) ? (string) $selected['image_position'] : 'right',
                'subtitle' => isset( $selected['subtitle'] ) ? (string) $selected['subtitle'] : '',
                'title' => isset( $selected['title'] ) ? (string) $selected['title'] : '',
                'content' => isset( $selected['content'] ) ? (string) $selected['content'] : '',
                'btn_text' => isset( $selected['btn_text'] ) ? (string) $selected['btn_text'] : '',
                'btn_url' => isset( $selected['btn_url'] ) ? (string) $selected['btn_url'] : '',
                'image' => isset( $selected['image'] ) ? (string) $selected['image'] : '',
            );

            return albalu_render_promo_section( $section );
        }
    }

    $sections = get_field( 'promo_sections', $post_id );
    if ( ! is_array( $sections ) ) {
        return '';
    }

    $section = isset( $sections[ $index - 1 ] ) ? $sections[ $index - 1 ] : null;
    if ( ! is_array( $section ) ) {
        return '';
    }

    if ( isset( $section['enabled'] ) && ! $section['enabled'] ) {
        return '';
    }

    return albalu_render_promo_section( $section );
}
add_shortcode('albalu_promo_section', 'albalu_promo_section_shortcode');

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
