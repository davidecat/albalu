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
// Force font-display: swap on Font Awesome and preload critical font
add_action( 'wp_head', function() {
	$fa_path = get_template_directory_uri() . '/assets/fontawesome/webfonts/';

	// Preload critical solid font
	echo '<link rel="preload" href="' . esc_url( $fa_path . 'fa-solid-900.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";

	// Override Font Awesome @font-face with font-display: swap
	?>
	<style>
	@font-face {
		font-family: "Font Awesome 6 Free";
		font-style: normal;
		font-weight: 900;
		font-display: swap;
		src: url("<?php echo esc_url( $fa_path ); ?>fa-solid-900.woff2") format("woff2"),
			 url("<?php echo esc_url( $fa_path ); ?>fa-solid-900.ttf") format("truetype");
	}
	@font-face {
		font-family: "Font Awesome 6 Free";
		font-style: normal;
		font-weight: 400;
		font-display: swap;
		src: url("<?php echo esc_url( $fa_path ); ?>fa-regular-400.woff2") format("woff2"),
			 url("<?php echo esc_url( $fa_path ); ?>fa-regular-400.ttf") format("truetype");
	}
	@font-face {
		font-family: "Font Awesome 6 Brands";
		font-style: normal;
		font-weight: 400;
		font-display: swap;
		src: url("<?php echo esc_url( $fa_path ); ?>fa-brands-400.woff2") format("woff2"),
			 url("<?php echo esc_url( $fa_path ); ?>fa-brands-400.ttf") format("truetype");
	}
	</style>
	<?php
}, 1 );


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
  wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery', 'swiper-js'), $modificated_CustomJS, true);
}

add_filter('bootscore/skip_cart', '__return_false');

/* PEWC: remove empty Customizer inline CSS (values not set) */
remove_action( 'wp_head', 'pewc_customize_css' );

/* PEWC: card-style radio/checkbox options (loads after PEWC inline styles) */
add_action( 'wp_enqueue_scripts', 'albalu_pewc_card_styles', 10001 );
function albalu_pewc_card_styles() {
    if ( ! wp_style_is( 'pewc-style', 'enqueued' ) ) {
        return;
    }
    $rmi = '.pewc-group-image_swatch.pewc-replace-main-image > .pewc-item-field-wrapper > .pewc-radio-images-wrapper';
    $css = "
    {$rmi} {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    {$rmi} > .pewc-radio-image-wrapper {
        padding: 14px 16px 14px 46px !important;
        border-radius: 8px !important;
        border: 2px solid #dee2e6 !important;
        margin: 0 !important;
        transition: border-color 0.2s, background-color 0.2s;
    }
    {$rmi} > .pewc-radio-image-wrapper:hover {
        border-color: #adb5bd !important;
    }
    {$rmi} > .pewc-radio-image-wrapper.checked {
        border-color: #578e99 !important;
        background-color: #f5fafa;
    }
    {$rmi} > .pewc-radio-image-wrapper > label {
        height: auto;
        line-height: 1.4;
        padding-left: 0 !important;
    }
    {$rmi} > .pewc-radio-image-wrapper > label > img {
        display: none !important;
    }
    {$rmi} > .pewc-radio-image-wrapper > label > .pewc-theme-element {
        display: block !important;
        top: 50% !important;
        left: -30px !important;
        transform: translateY(-50%);
        width: 22px !important;
        height: 22px !important;
        border: 2px solid #adb5bd;
        border-radius: 4px;
        background: #fff !important;
    }
    {$rmi} > .pewc-radio-image-wrapper.checked > label > .pewc-theme-element {
        background: #578e99 !important;
        border-color: #578e99;
    }
    {$rmi} > .pewc-radio-image-wrapper > label > .pewc-theme-element::after {
        left: 6px !important;
        top: 2px !important;
        width: 5px !important;
        height: 10px !important;
        border: solid white !important;
        border-width: 0 3px 3px 0 !important;
        transform: rotate(45deg);
    }
    ";
    wp_add_inline_style( 'pewc-style', $css );
}


remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

/* Related products: move after description (priority 10) and before FAQ (priority 20) */
/* Hide product meta (categories, tags) from single product page */
add_action( 'template_redirect', function() {
    if ( is_product() ) {
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
        add_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products', 15 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
    }
} );

/* Override Bootscore sale flash badge classes */
add_filter( 'woocommerce_sale_flash', function( $html, $post, $product ) {
    return '<span class="badge position-absolute start-0 mt-3 ms-3 me-4 z-1 py-2 px-2">' . esc_html__( 'Sale!', 'woocommerce' ) . '</span>';
}, 20, 3 );

function albalu_setup() {
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    register_nav_menus( array(
        'menu-mobile'   => 'Menu Mobile',
        'footer_shop'   => 'Footer Shop',
        'footer_guide'  => 'Footer Guida all\'acquisto',
    ) );
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
    if ( ! function_exists( 'is_product_category' ) ) {
        return;
    }
    if ( is_product_category() || is_shop() ) {
        // 1. Remove Breadcrumbs
        
        
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


add_filter('woocommerce_sale_flash', 'albalu_custom_sale_badge', 20, 3);
function albalu_custom_sale_badge($html, $post, $product) {
    if ( is_product() ) {
        return $html;
    }
    return '<span class="badge position-absolute start-0 mt-3 ms-3 me-4 z-1 py-2 px-2">' . esc_html__('Sale!', 'woocommerce') . '</span>';
}


/**
 * Cart page: cross-sell products beside cart totals in a Bootstrap row
 */
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );

add_action( 'woocommerce_cart_collaterals', 'albalu_cart_collaterals_layout', 10 );
function albalu_cart_collaterals_layout() {
    echo '<div class="row">';

    // Left column: cross-sell products (columns=1 so each product is col-12)
    echo '<div class="col-lg-6 mb-4 mb-lg-0">';
    woocommerce_cross_sell_display( 2, 1 );
    echo '</div>';

    // Right column: cart totals
    echo '<div class="col-lg-6">';
    woocommerce_cart_totals();
    echo '</div>';

    echo '</div>';
}

require_once get_stylesheet_directory() . '/functions-product.php';

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

// Snippet Start -----------

//1. Product Add-ons: condizione per tag e modifiche visualizzazione
//------------------- START ---------------------
/* Field Group/Backend: imposta regole di visualizzazione per tag invece di categorie */
function filtra_per_tag_invece_di_categorie($args, $group_id, $group, $rule) {
	return 'product_tag';
}
add_filter('pewc_filter_global_categories_taxonomy','filtra_per_tag_invece_di_categorie', 10, 4);

/* Field Group/Generale: aggiungi classe custom ai gruppi selezionati */
function prefix_filter_single_product_classes( $classes, $item ) {
	// Check if the field ID is 3358 and add a custom class
	if ( isset( $item['field_id'] ) && $item['field_id'] == 3358 ) {
		$classes[] = 'custom-class-for-3358';
	}
	return $classes;
}
add_filter( 'pewc_filter_single_product_classes', 'prefix_filter_single_product_classes', 10, 2 );

/* Field Group/Titolo: non mostrare il titolo del gruppo */
add_filter('pewc_filter_group_title', '__return_empty_string');

/* Field Group/Descrizione: sostituisci "<p>" con "<div>" per correggere il markup quando si inserisce uno shortcode */
add_filter( 'pewc_filter_group_description', function($description) {
	$description = str_replace('<p', '<div', $description);
	$description = str_replace('</p', '</div', $description);
	return $description;
}, 10, 2 );

/* Field: imposta descrizione come placeholder per i campi di testo */
add_filter( 'pewc_description_as_placeholder', function( $set, $item ) {
	if ( $item['field_type'] == 'text' ) {
		$set = true;
	}
	return $set;
}, 10, 2 );

/* Aggiungi al carrello l'immagine predefinita del prodotto, non quella generata dal plugin */
remove_filter('pewc_after_add_cart_item_data', 'pewc_create_composite_image', 10, 2);
//------------------- START ---------------------

//2. WooCommerce/Dettaglio prodotto: mostra attributi prodotto (shortcode)
//------------------- START ---------------------
/* Shortcode: mostra attributi prodotto (label_personalizzata e misura_prodotto_reale) */
function mostra_attributi_prodotto_shortcode( $atts ) {
	global $product;
	if (!is_a($product, 'WC_Product')) {
		$product = wc_get_product( $id );
	}
	if (is_a($product, 'WC_Product')) {
		/* Recupera i dati degli attributi */
		$label_personalizzata = $product->get_attribute( 'label_personalizzata' );
		$misura_prodotto_reale = $product->get_attribute( 'misura_prodotto_reale' );
		/* Verifica se almeno uno dei due attributi è stato impostato */
		if ($label_personalizzata || $misura_prodotto_reale) {
			echo '<ul class="prodotto-attributi">';
			/* Mostra "label_personalizzata" se presente */
			if ($label_personalizzata) {
				echo '<li><i aria-hidden="true" class="fas fa-box-open"></i> '.$label_personalizzata.'</li>';
			}
			/* Mostra "misura_prodotto_reale" se presente */
			if ($misura_prodotto_reale) {
				echo '<li><i aria-hidden="true" class="fas fa-fw fa-ruler-combined"></i> '.$misura_prodotto_reale.'</li>';
			}
			echo '</ul>';
		}
	}
}
add_shortcode('mostra-attributi-prodotto', 'mostra_attributi_prodotto_shortcode');
//------------------- END ---------------------

//3. Product Add-ons: controlli per "replace main image" + toggle button
//------------------- START ---------------------
function fix_pewc_replace_main_image() {
	wp_add_inline_script('wc-single-product', "
		jQuery(function($) {
			var showingCustom = false;
			var savedOriginalSrc = '';
			var savedCustomSrc = '';

			// Save original image src before PEWC replaces it
			var \$mainImg = $('.woocommerce-product-gallery .woocommerce-product-gallery__image img').first();
			if (\$mainImg.length) {
				savedOriginalSrc = \$mainImg.attr('src');
			}

			// Inject toggle button inside the gallery
			$('.woocommerce-product-gallery').append('<button id=\"pewc-image-toggle\" type=\"button\" class=\"btn btn-sm btn-outline-secondary pewc-image-toggle\" style=\"display:none;\">Vedi foto prodotto</button>');

			// Nascondi le thumbnails quando si seleziona personalizzazione
			$(document).on('click', '.pewc-replace-main-image .pewc-radio-checkbox-image-wrapper.pewc-radio-image-wrapper-1', function () {
				$('.elementor-widget-woocommerce-product-images span.onsale').addClass('hide');
				$('.woocommerce-product-gallery ol.flex-control-thumbs').addClass('hide');
				$('.woocommerce-product-gallery a.woocommerce-product-gallery__trigger').addClass('hide');
				$('.woocommerce-product-gallery ol.flex-control-thumbs li:first-child img').trigger('click');
				showingCustom = true;
				$('#pewc-image-toggle').fadeIn(200);
			});

			// Mostra di nuovo le thumbnails quando si deseleziona
			$(document).on('click', '.pewc-replace-main-image .pewc-radio-checkbox-image-wrapper.pewc-radio-image-wrapper-0', function () {
				$('.woocommerce-product-gallery ol.flex-control-thumbs').removeClass('hide');
				$('.woocommerce-product-gallery a.woocommerce-product-gallery__trigger').removeClass('hide');
				showingCustom = false;
				$('#pewc-image-toggle').fadeOut(200);
			});

			// Toggle button click handler
			$(document).on('click', '#pewc-image-toggle', function () {
				var \$img = $('.woocommerce-product-gallery .woocommerce-product-gallery__image img').first();
				if (!\$img.length) return;

				var oldSrc = \$img.attr('data-pewc-old-src') || savedOriginalSrc;
				var currentSrc = \$img.attr('src');

				if (!oldSrc) return;

				if (showingCustom) {
					// Save custom src, show original
					savedCustomSrc = currentSrc;
					\$img.attr('src', oldSrc);
					if (\$img.attr('data-pewc-old-srcset')) {
						\$img.attr('srcset', \$img.attr('data-pewc-old-srcset'));
					}
					$('.woocommerce-product-gallery ol.flex-control-thumbs').removeClass('hide');
					// Hide all PEWC overlay layers
					$('.pewc-image-layer').addClass('pewc-layer-hidden');
					$(this).text('Vedi foto confezione personalizzata');
					showingCustom = false;
				} else {
					// Restore custom src
					var customSrc = savedCustomSrc || \$img.attr('data-pewc-custom-src');
					if (customSrc) {
						\$img.attr('src', customSrc);
						\$img.attr('srcset', '');
					}
					$('.woocommerce-product-gallery ol.flex-control-thumbs').addClass('hide');
					// Show PEWC overlay layers again
					$('.pewc-image-layer').removeClass('pewc-layer-hidden');
					$(this).text('Vedi foto prodotto');
					showingCustom = true;
				}
			});

			// Track when PEWC updates the image so we can save the custom src
			var observer = new MutationObserver(function(mutations) {
				mutations.forEach(function(m) {
					if (m.attributeName === 'src') {
						var \$img = $(m.target);
						var oldSrc = \$img.attr('data-pewc-old-src');
						var newSrc = \$img.attr('src');
						if (oldSrc && newSrc !== oldSrc) {
							savedCustomSrc = newSrc;
							\$img.attr('data-pewc-custom-src', newSrc);
						}
					}
				});
			});

			var mainImg = $('.woocommerce-product-gallery .woocommerce-product-gallery__image img').first()[0];
			if (mainImg) {
				observer.observe(mainImg, { attributes: true, attributeFilter: ['src'] });
			}
		});
	");
}
add_action( 'woocommerce_after_add_to_cart_form', 'fix_pewc_replace_main_image' );

/* Sync PEWC grand total into .pewc-main-price and hide subtotals.
   For sale products: show strikethrough regular price + add-ons and sale price + add-ons.
   Works with both simple and variable products. */
function albalu_pewc_sync_price() {
	if ( ! is_product() ) return;

	global $product;

	// For simple products, get prices directly
	// For variable products, build a map of variation_id => regular_price
	$variation_regular_prices = array();
	$simple_regular = 0;
	$simple_sale    = 0;
	$is_simple_sale = false;

	if ( $product->is_type( 'variable' ) ) {
		$variations = $product->get_available_variations();
		foreach ( $variations as $variation ) {
			$var_obj = wc_get_product( $variation['variation_id'] );
			if ( $var_obj ) {
				$reg = (float) $var_obj->get_regular_price();
				$variation_regular_prices[ $variation['variation_id'] ] = (float) wc_get_price_including_tax( $var_obj, array( 'price' => $reg ) );
			}
		}
	} elseif ( $product->is_on_sale() ) {
		$is_simple_sale = true;
		$simple_regular = (float) wc_get_price_including_tax( $product, array( 'price' => $product->get_regular_price() ) );
		$simple_sale    = (float) wc_get_price_including_tax( $product, array( 'price' => $product->get_sale_price() ) );
	}
	?>
	<script>
	jQuery(function($) {
		$('.pewc-total-field-wrapper').hide();

		var isVariable = <?php echo $product->is_type( 'variable' ) ? 'true' : 'false'; ?>;
		var isSimpleSale = <?php echo $is_simple_sale ? 'true' : 'false'; ?>;
		var simpleRegularBase = <?php echo $simple_regular; ?>;
		var simpleSaleBase = <?php echo $simple_sale; ?>;
		var variationRegularPrices = <?php echo wp_json_encode( $variation_regular_prices ); ?>;

		var currentRegularBase = 0;
		var currentSaleBase = 0;
		var isOnSale = false;

		var $mainPrice = $('.pewc-main-price').not('.pewc-quickview-product-wrapper .pewc-main-price').first();
		if (!$mainPrice.length) return;

		if (!isVariable && isSimpleSale) {
			isOnSale = true;
			currentRegularBase = simpleRegularBase;
			currentSaleBase = simpleSaleBase;
		}

		// For variable products, update prices when a variation is selected
		if (isVariable) {
			$('form.cart').on('found_variation', function(e, variation) {
				var varId = variation.variation_id;
				currentSaleBase = parseFloat(variation.display_price) || 0;
				currentRegularBase = variationRegularPrices[varId] || currentSaleBase;
				isOnSale = (currentRegularBase > currentSaleBase);
			});
			$('form.cart').on('hide_variation', function() {
				isOnSale = false;
				currentRegularBase = 0;
				currentSaleBase = 0;
			});
		}

		function updatePrice(saleGrandTotalRaw) {
			var qty = parseFloat($('form.cart .quantity .qty').val()) || 1;

			if (isOnSale && currentRegularBase > 0) {
				var addonsWithQty = saleGrandTotalRaw - (currentSaleBase * qty);
				var regularGrandTotal = (currentRegularBase * qty) + addonsWithQty;

				var regFmt  = pewc_wc_price(regularGrandTotal.toFixed(pewc_vars.decimals));
				var saleFmt = pewc_wc_price(saleGrandTotalRaw.toFixed(pewc_vars.decimals));
				$mainPrice.html('<del>' + regFmt + '</del> <ins>' + saleFmt + '</ins>');
			} else {
				var fmt = pewc_wc_price(saleGrandTotalRaw.toFixed(pewc_vars.decimals));
				$mainPrice.html(fmt);
			}
		}

		// PEWC event: args are [base_total_price, base_price, grand_total]
		$('body').on('pewc_after_update_total_js', function(e, addonsOnly, rawGrandTotal) {
			if (typeof rawGrandTotal === 'number' && !isNaN(rawGrandTotal)) {
				updatePrice(rawGrandTotal);
			}
		});
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'albalu_pewc_sync_price' );

//------------------- END ---------------------

//6b. Show WooCommerce error notices in a modal that fades out after 5 seconds
//------------------- START ---------------------
add_action( 'wp_footer', 'albalu_error_notices_modal' );
function albalu_error_notices_modal() {
	if ( ! is_product() ) return;
	?>
	<!-- Error notices modal -->
	<div id="albalu-error-modal" class="albalu-error-modal" style="display:none;">
		<div class="albalu-error-modal-content">
			<div class="albalu-error-modal-body"></div>
		</div>
	</div>
	<style>
		.albalu-error-modal {
			position: fixed;
			top: 0; left: 0; width: 100%; height: 100%;
			background: rgba(0,0,0,0.4);
			z-index: 99999;
			display: flex;
			align-items: center;
			justify-content: center;
			opacity: 1;
			transition: opacity 0.4s ease;
		}
		.albalu-error-modal.fade-out {
			opacity: 0;
		}
		.albalu-error-modal-content {
			background: #c0392b;
			color: #fff;
			border-radius: 8px;
			padding: 1.5rem;
			max-width: 500px;
			width: 90%;
			box-shadow: 0 4px 20px rgba(0,0,0,0.25);
			font-size: 0.875rem;
			font-weight: 500;
		}
		.albalu-error-modal-body ul {
			list-style: none;
			padding: 0;
			margin: 0;
		}
		.albalu-error-modal-body ul li {
			padding: 0.4rem 0;
			color: #fff;
			border-bottom: 1px solid rgba(255,255,255,0.2);
		}
		.albalu-error-modal-body ul li:last-child {
			border-bottom: none;
		}
		.albalu-error-modal-body ul li::before {
			display: none;
		}
		.albalu-error-modal-body .woocommerce-error {
			background: #c0392b !important;
			border: none !important;
			padding: 0 !important;
			margin: 0 !important;
			color: #fff !important;
		}
		.albalu-error-modal-body .woocommerce-error::before {
			display: none !important;
		}
	</style>
	<script>
	jQuery(function($) {
		var $modal = $('#albalu-error-modal');
		var $modalBody = $modal.find('.albalu-error-modal-body');
		var fadeTimer = null;

		function showErrorModal($errors) {
			// Clear any previous timer and reset modal state
			if (fadeTimer) clearTimeout(fadeTimer);
			$modalBody.empty();

			$modalBody.html($errors.clone());
			$errors.remove();

			// Close the mini-cart offcanvas if open
			var offcanvasCart = document.getElementById('offcanvas-cart');
			if (offcanvasCart) {
				var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasCart);
				if (bsOffcanvas) bsOffcanvas.hide();
			}

			$modal.removeClass('fade-out').show();
			fadeTimer = setTimeout(function() {
				$modal.addClass('fade-out');
				setTimeout(function() { $modal.hide(); }, 400);
			}, 5000);
		}

		// Check for error notices after page load (form validation triggers page reload)
		var $existing = $('.woocommerce-error');
		if ($existing.length) {
			showErrorModal($existing);
		}

		// Intercept AJAX-injected notices only from add-to-cart requests
		$(document).ajaxComplete(function(event, xhr, settings) {
			if (!settings.url || settings.url.indexOf('wc-ajax') === -1) return;
			setTimeout(function() {
				var $errors = $('.woocommerce-error').not('#albalu-error-modal .woocommerce-error');
				if ($errors.length) {
					showErrorModal($errors);
				}
			}, 100);
		});

		// Close modal on background click
		$modal.on('click', function(e) {
			if ($(e.target).is($modal)) {
				if (fadeTimer) clearTimeout(fadeTimer);
				$modal.addClass('fade-out');
				setTimeout(function() { $modal.hide(); }, 400);
			}
		});
	});
	</script>
	<?php
}
//------------------- END ---------------------

/* Add Albalu-style title bar to cart/checkout pages and hide the default entry-title */
add_action( 'bootscore_before_title', function( $context ) {
	if ( $context !== 'page' ) return;
	if ( ! ( is_cart() || is_checkout() ) ) return;
	?>
	</div></div></div></main></div></div><!-- close page.php wrappers temporarily -->
	<section class="page-title-bar bg-albalu-warm py-4 mb-4">
		<div class="container">
			<?php the_title( '<h1 class="fs-2 fw-normal mb-0">', '</h1>' ); ?>
		</div>
	</section>
	<div class="site-content container pt-3 pb-5"><div class="content-area"><div class="row"><div class="col"><main class="site-main"><div class="entry-header" style="display:none;">
	<?php
} );

//4. WooCommerce/Checkout
//------------------- START ---------------------
/* WooCommerce/Checkout: disattiva il check di default al campo "Ship to different address" */
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
//------------------- END ---------------------

//4b. WooCommerce: 24 prodotti per pagina negli archivi
add_filter( 'loop_shop_per_page', function() { return 24; } );


//5. WooCommerce/Dettaglio prodotto: wrap quantità + addToCart + modifica messaggio addToCart
//------------------- START ---------------------
/* Dettaglio prodotto: wrappa quantità e pulsante add to cart */
// function wrap_quantity_addtocart() {
// 	wp_add_inline_script('wc-single-product', "
// 		jQuery(function($) {
// 			$('.elementor-widget-jet-single-add-to-cart .quantity, .elementor-widget-jet-single-add-to-cart .single_add_to_cart_button').wrapAll('<div class=\"quantity-addtocart-wrapper\"></div>');
// 		});
// 	");
// }
// add_action( 'woocommerce_after_add_to_cart_form', 'wrap_quantity_addtocart' );

/* Dettaglio prodotto: modifica messaggio di aggiunta al carrello */
// function custom_add_to_cart_message() {
// 	$message = 'Il prodotto è stato aggiunto al carrello! <a href="'.esc_url(wc_get_page_permalink('cart')).'" tabindex="1" class="button button-gotocart wc-forward"><i aria-hidden="true" class="fas fa-shopping-cart"></i>&nbsp;Vai al carrello</a>';
// 	return $message;
// }
// add_filter('wc_add_to_cart_message_html', 'custom_add_to_cart_message');
//------------------- END ---------------------

//6. WooCommerce/Dettaglio prodotto: visualizzazione prezzi variazioni
//------------------- START ---------------------
/* Nascondi il prezzo della variazione e mostralo al posto del prezzo normale del prodotto */
function update_price_with_variation_price() {
	global $product;
	$price = $product->get_price_html();
	wp_add_inline_script('wc-add-to-cart-variation', "
		jQuery(function($) {
			$(document).on('found_variation', 'form.cart', function( event, variation ) {
				if(variation.price_html) $('#dettaglio-prodotto-info .elementor-widget-woocommerce-product-price > div > .price').html(variation.price_html);
				$('.woocommerce-variation-price').hide();
			});
			$(document).on('hide_variation', 'form.cart', function( event, variation ) {
				$('#dettaglio-prodotto-info .elementor-widget-woocommerce-product-price > div > .price').html('" . $price . "');
			});
		});
	");
}
add_action( 'woocommerce_variable_add_to_cart', 'update_price_with_variation_price' );

/* Mostra sempre il prezzo delle varianti, anche quando hanno tutte lo stesso prezzo */
add_filter( 'woocommerce_show_variation_price', '__return_true' );

/* WooCommerce/Dettaglio prodotto: nascondi pulsante "svuota" per le variazioni */
add_filter( 'woocommerce_reset_variations_link', '__return_empty_string', 9999 );
//------------------- END ---------------------

//7. WooCommerce/Generale: mostra solo prodotti nei risultati di ricerca
//------------------- START ---------------------
/* Mostra solo i prodotti nei risultati di ricerca */
function search_only_products($query) {
	if (!is_admin() && $query->is_search()) {
		$query->set('post_type', 'product');
		$query->set('wc_query', 'product_query');
	}
	return $query;
}
add_filter('pre_get_posts','search_only_products');
//------------------- END ---------------------

//7b. Separa sottocategorie dai prodotti nelle pagine categoria
//------------------- START ---------------------
/* Rimuove l'inserimento automatico delle sottocategorie nel loop prodotti.
   Le sottocategorie vengono renderizzate separatamente in archive-product.php */
add_action( 'wp_loaded', function() {
	remove_filter( 'woocommerce_product_loop_start', 'woocommerce_maybe_show_product_subcategories' );
} );
//------------------- END ---------------------

//8. Product Add-ons: shortcode in descrizione gruppo
//------------------- START ---------------------
/* Forza lo shortcode al posto della descrizione in base al gruppo */
add_filter( 'pewc_get_group_description', function( $group_description, $group_id ) {
	/* Confezione Bomboniera Completa o kit gratis (CONF1) */
    if ($group_id == 1272) {
        ob_start();
        wc_get_template_part('content', 'kitconfezione');
        $group_description = ob_get_clean();
    }
    return $group_description;
}, 10, 2 );

/* Field Group/Descrizione: sostituisci "<p>" con "<div>" per correggere il markup quando si inserisce uno shortcode 
add_filter( 'pewc_filter_group_description', function($group_description) {
	$group_description = str_replace('<p', '<div', $group_description);
	$group_description = str_replace('</p', '</div', $group_description);
	return $group_description;
}, 10, 2 );
*/
//------------------- END ---------------------

//9. WooCommerce/Dettaglio prodotto: mostra SKU variazione
//------------------- START ---------------------
function display_variation_sku() {
	global $product;
	if (!$product->is_type('variable')) return;
	$parent_sku = $product->get_sku();
	wp_add_inline_script('wc-add-to-cart-variation', "
		jQuery(function($) {
			var parentSku = '" . esc_js( $parent_sku ) . "';
			var parentDesc = $('.woocommerce-product-details__short-description').html();
			$(document).on('found_variation', 'form.cart', function( event, variation ) {
				$('#product-sku span').text(variation.sku || parentSku);
				if (variation.variation_description) {
					$('.woocommerce-product-details__short-description').html(variation.variation_description);
				} else {
					$('.woocommerce-product-details__short-description').html(parentDesc);
				}
			});
			$(document).on('hide_variation', 'form.cart', function() {
				$('#product-sku span').text(parentSku);
				$('.woocommerce-product-details__short-description').html(parentDesc);
			});
		});
	");
}
add_action('woocommerce_after_add_to_cart_form', 'display_variation_sku');
//------------------- END ---------------------

//10. search
//------------------- START ---------------------

function tp_search_results_shortcode() {

    if ( empty($_GET['keyword']) ) {
        return '<p>No search keyword found.</p>';
    }

    $keyword = sanitize_text_field($_GET['keyword']);

    // Split multiple words
    $search_words = preg_split('/\s+/', $keyword);

    // Build "AND" search conditions
    $title_query = array('relation' => 'AND');

    foreach ($search_words as $word) {
        $title_query[] = array(
            'key'     => '_none_',  // fake key to force meta_query usage
            'compare' => 'EXISTS',
        );
    }

    // WP_Query cannot directly do word-boundary title searches
add_filter('posts_search', function($search, $wp_query) use ($search_words) {
    global $wpdb;

    if ( empty($search_words) ) {
        return $search;
    }

    $conditions = [];

    foreach ($search_words as $word) {
        $word = esc_sql($word);

        // Exact word match in TITLE only — punctuation safe
        $conditions[] = "
            ( {$wpdb->posts}.post_title REGEXP '(^|[^a-zA-Z0-9]){$word}($|[^a-zA-Z0-9])' )
        ";
    }

    if (!empty($conditions)) {
        $search = " AND " . implode(" AND ", $conditions);
    }

    return $search;
}, 10, 2);

    // Query products
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        's'              => $keyword, // needed so WP loads posts_search filter
    );

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        echo '<div class="tp-search-results">';
        while ($query->have_posts()) : $query->the_post();
            wc_get_template_part('content', 'product');
        endwhile;
        echo '</div>';
    } else {
        echo '<p>No products found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
// add_shortcode('tp_search_results', 'tp_search_results_shortcode');

// [tp_search_form]
function tp_search_form_shortcode() {
    $action_url = site_url('/search-text/');

    ob_start();
    ?>
    <form method="get" action="<?php echo esc_url($action_url); ?>">
        <input type="text" name="keyword" placeholder="Search products..." required />
        <button type="submit">Search</button>
    </form>
    <?php
    return ob_get_clean();
}
// add_shortcode('tp_search_form', 'tp_search_form_shortcode');
function tp_exact_title_search( $where, $query ) {
    global $wpdb;

    // Run only on search pages using ?s=
    if ( ! $query->is_search() ) {
        return $where;
    }

    // get the search string
    $keyword = $query->get('s');

    if ( empty($keyword) ) return $where;

    // split by space
    $words = preg_split('/\s+/', trim($keyword));

    $conditions = [];

    foreach ( $words as $word ) {
        $word = esc_sql($word);

        // exact word boundary match in title only
        $conditions[] = "
            {$wpdb->posts}.post_title REGEXP '(^|[^a-zA-Z0-9]){$word}($|[^a-zA-Z0-9])'
        ";
    }

    if ( ! empty($conditions) ) {
        // remove default WP search (very important)
        $where = preg_replace("/\(\s*{$wpdb->posts}\.post_title\s+LIKE\s*'%.*?%'\s*\)/", "1=1", $where);

        // add our exact title search
        $where .= " AND (" . implode(" AND ", $conditions) . ")";
    }

    return $where;
}

// add_filter('posts_where', 'tp_exact_title_search', 9999, 2);

function tp_custom_search_synonyms( $query ) {
    if ( is_admin() || ! $query->is_search() || !$query->is_main_query() ) {
        return;
    }

    $original_search = $query->get( 's' ); // save original for search box

    if ( empty( $original_search ) ) {
        return;
    }

    // Synonym groups
    $synonyms = [
        ['segnalibro', 'segnalibri'],
        ['profumatore', 'profumatori', 'profumi'],
        ['albero vita', 'albero della vita'],
        ['portafoto', 'porta foto'],
    ];

    $matched_group = [];

    foreach ( $synonyms as $group ) {
        foreach ( $group as $word ) {
            if ( mb_strtolower($original_search) === mb_strtolower($word) ) {
                $matched_group = $group;
                break 2;
            }
        }
    }

    if ( empty( $matched_group ) ) {
        return;
    }

    // Prevent WordPress from applying its default search
    $query->set( 's', null );

    // Custom WHERE SQL
    add_filter( 'posts_where', function( $where ) use ( $matched_group, $original_search ) {
        global $wpdb;

        $parts = [];
        foreach ( $matched_group as $word ) {
            $parts[] = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($word) . '%');
        }

        if ( ! empty( $parts ) ) {
            $where .= " AND (" . implode(" OR ", $parts) . ")";
        }

        return $where;
    });

    // Keep original term visible in search box
    add_filter( 'get_search_query', function() use ( $original_search ) {
        return $original_search;
    });
}
add_action( 'pre_get_posts', 'tp_custom_search_synonyms' );
//------------------- END ---------------------

// Snippet End -----------


// Nav wrapper class
add_filter('bootscore/class/breadcrumb/nav', function() {
    return 'wc-breadcrumb overflow-x-auto text-nowrap mb-2 mt-2 py-4 px-3 rounded';
});


// Get default features from the "Chi Siamo" page (first 3 items)
function albalu_get_default_features() {
    $chi_siamo = get_page_by_path( 'chi-siamo' );
    if ( ! $chi_siamo ) {
        return array();
    }

    $sections = get_field( 'chi_siamo_sections', $chi_siamo->ID );
    if ( empty( $sections ) ) {
        return array();
    }

    foreach ( $sections as $s ) {
        if ( $s['acf_fc_layout'] === 'features' && ! empty( $s['items'] ) ) {
            return array_slice( $s['items'], 0, 3 );
        }
    }

    return array();
}

// Get default categories grid data from the "Chi Siamo" page
function albalu_get_default_categories_grid() {
    $chi_siamo = get_page_by_path( 'chi-siamo' );
    if ( ! $chi_siamo ) {
        return array();
    }

    $sections = get_field( 'chi_siamo_sections', $chi_siamo->ID );
    if ( empty( $sections ) ) {
        return array();
    }

    foreach ( $sections as $s ) {
        if ( $s['acf_fc_layout'] === 'categories_grid' ) {
            return $s;
        }
    }

    return array();
}

//FAQ Section Start
function albalu_faq_shortcode() {
    if ( ! function_exists( 'have_rows' ) || ! have_rows( 'faq', 'option' ) ) {
        return '';
    }

    ob_start();
    ?>
    <div class="albalu-faq-section bg-albalu-warm">
        <div class="container">
            <?php while ( have_rows( 'faq', 'option' ) ) : the_row();
                $q = get_sub_field( 'faq-question' );
                $a = get_sub_field( 'faq-answer' );
                if ( ! $q ) continue;
            ?>
            <div class="albalu-faq-item">
                <p class="albalu-faq-question"><?php echo esc_html( $q ); ?></p>
                <hr class="albalu-faq-divider">
                <?php if ( $a ) : ?>
                <div class="albalu-faq-answer"><?php echo wp_kses_post( wpautop( $a ) ); ?></div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <style>
        .albalu-faq-section {
            width: 100vw;
            position: relative;
            left: 50%;
            margin-left: -50vw;
            padding: 20px 0 10px;
        }
        .albalu-faq-item {
            padding: 14px 0 18px;
        }
        .albalu-faq-question {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--color-titoli);
            margin: 0 0 12px;
            line-height: 1.4;
        }
        .albalu-faq-divider {
            border: 0;
            border-top: 1px solid #c8c0ba;
            margin: 0 0 12px;
            opacity: 1;
        }
        .albalu-faq-answer {
            font-size: 0.9375rem;
            color: var(--color-testo);
            line-height: 1.65;
            margin: 0;
        }
        .albalu-faq-answer p {
            margin: 0 0 4px;
        }
        .albalu-faq-answer p:last-child {
            margin-bottom: 0;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'albalu_faq', 'albalu_faq_shortcode' );
//FAQ Section End

// Show base product price (before PEWC extras) in admin order details
add_action( 'woocommerce_after_order_itemmeta', 'albalu_show_base_price_in_order', 20, 3 );
function albalu_show_base_price_in_order( $item_id, $item, $product ) {
	if ( ! is_admin() || ! ( $item instanceof WC_Order_Item_Product ) ) {
		return;
	}

	$extras = $item->get_meta( 'product_extras' );
	$base_price = null;

	if ( is_array( $extras ) && isset( $extras['original_price'] ) ) {
		$base_price = $extras['original_price'];
	} elseif ( $product ) {
		$base_price = $product->get_price();
	}

	if ( ! is_null( $base_price ) && $base_price !== '' ) {
		printf(
			'<div class="wc-order-item-sku" style="margin-top:4px;"><strong>%s</strong>: <strong>%s</strong></div>',
			esc_html__( 'Prezzo base', 'albalu' ),
			wp_kses_post( wc_price( $base_price ) )
		);
	}
}



// Add shippingDetails, returnPolicy, GTIN and hasVariant to Yoast Product schema
add_filter( 'wpseo_schema_product', function( $data ) {
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product ) return $data;

	$price = (float) $product->get_price();
	$shipping_cost = ( $price >= 149 ) ? '0' : '6.90';

	$shipping = array(
		'@type'               => 'OfferShippingDetails',
		'shippingRate'        => array(
			'@type'    => 'MonetaryAmount',
			'value'    => $shipping_cost,
			'currency' => 'EUR',
		),
		'shippingDestination' => array(
			'@type'          => 'DefinedRegion',
			'addressCountry' => 'IT',
		),
		'deliveryTime'        => array(
			'@type'        => 'ShippingDeliveryTime',
			'handlingTime' => array(
				'@type'    => 'QuantitativeValue',
				'minValue' => 1,
				'maxValue' => 6,
				'unitCode' => 'DAY',
			),
			'transitTime'  => array(
				'@type'    => 'QuantitativeValue',
				'minValue' => 1,
				'maxValue' => 7,
				'unitCode' => 'DAY',
			),
		),
	);

	$return_policy = array(
		'@type'                => 'MerchantReturnPolicy',
		'applicableCountry'    => 'IT',
		'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
		'merchantReturnDays'   => 14,
		'returnMethod'         => 'https://schema.org/ReturnByMail',
		'returnFees'           => 'https://schema.org/FreeReturn',
	);

	// GTIN from barcode plugin field
	$barcode = get_post_meta( $product->get_id(), 'usbs_barcode_field', true );
	if ( ! empty( $barcode ) ) {
		$data['gtin'] = $barcode;
	}

	// Add shipping/return to existing offers
	if ( isset( $data['offers'] ) ) {
		$offers = isset( $data['offers'][0] ) ? $data['offers'] : array( $data['offers'] );
		foreach ( $offers as &$offer ) {
			$offer['shippingDetails']          = $shipping;
			$offer['hasMerchantReturnPolicy']   = $return_policy;
		}
		$data['offers'] = $offers;
	}

	// Add hasVariant for variable products
	if ( $product->is_type( 'variable' ) ) {
		$variations = $product->get_available_variations();
		$variants = array();
		foreach ( $variations as $variation ) {
			$var_product = wc_get_product( $variation['variation_id'] );
			if ( ! $var_product ) continue;

			$var_desc = $var_product->get_description();
			if ( empty( $var_desc ) ) {
				$var_desc = $product->get_short_description();
			}
			if ( empty( $var_desc ) ) {
				$var_desc = $product->get_description();
			}

			$variant = array(
				'@type'          => 'Product',
				'name'           => $var_product->get_name(),
				'description'    => wp_strip_all_tags( $var_desc ),
				'sku'            => $var_product->get_sku(),
				'productGroupID' => (string) $product->get_id(),
				'offers' => array(
					'@type'         => 'Offer',
					'url'           => $var_product->get_permalink(),
					'price'         => $var_product->get_price(),
					'priceCurrency' => get_woocommerce_currency(),
					'availability'  => $var_product->is_in_stock()
						? 'https://schema.org/InStock'
						: 'https://schema.org/OutOfStock',
					'priceValidUntil'          => date( 'Y-12-31', strtotime( '+1 year' ) ),
					'shippingDetails'          => $shipping,
					'hasMerchantReturnPolicy'  => $return_policy,
				),
			);

			// Variation GTIN
			$var_barcode = get_post_meta( $variation['variation_id'], 'usbs_barcode_field', true );
			if ( ! empty( $var_barcode ) ) {
				$variant['gtin'] = $var_barcode;
			}

			// Variation image
			$image_id = $var_product->get_image_id();
			if ( $image_id ) {
				$variant['image'] = wp_get_attachment_url( $image_id );
			}

			// Variation attributes
			$attributes = $var_product->get_attributes();
			foreach ( $attributes as $attr_name => $attr_value ) {
				if ( ! empty( $attr_value ) ) {
					$variant['additionalProperty'][] = array(
						'@type' => 'PropertyValue',
						'name'  => wc_attribute_label( $attr_name ),
						'value' => $attr_value,
					);
				}
			}

			$variants[] = $variant;
		}

		if ( ! empty( $variants ) ) {
			$data['hasVariant'] = $variants;
		}
	}

	return $data;
} );

// Force canonical URL on product pages with variation parameters (strips query strings)
add_filter('wpseo_canonical', function( $canonical ) {
    if ( is_product() && ! empty( $_GET ) ) {
        return get_permalink();
    }
    return $canonical;
});