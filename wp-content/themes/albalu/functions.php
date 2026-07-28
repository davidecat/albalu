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
/* ====================================================================
 * PERFORMANCE OPTIMIZATIONS — 3 blocchi attivabili gradualmente
 * ====================================================================
 * Per attivare un blocco, decommenta l'intera sezione tra START e END.
 * Testa in produzione 24h prima di passare al blocco successivo.
 * ==================================================================== */

/* Preconnect/dns-prefetch per 3rd party (Iubenda, Brevo, GTM, FB, Pinterest, Cloudflare CDN) */
add_action( 'wp_head', function() {
	$hosts = array(
		'https://cs.iubenda.com',
		'https://cdn.iubenda.com',
		'https://cdn.brevo.com',
		'https://www.googletagmanager.com',
		'https://connect.facebook.net',
		'https://assets.pinterest.com',
	);
	foreach ( $hosts as $h ) {
		echo '<link rel="preconnect" href="' . esc_url( $h ) . '" crossorigin>' . "\n";
	}

	/* Preload immagine hero LCP su home (foreground image è il vero LCP mobile) */
	if ( is_front_page() && function_exists( 'get_field' ) ) {
		$sections = get_field( 'chi_siamo_sections', get_option( 'page_on_front' ) );
		if ( is_array( $sections ) ) {
			foreach ( $sections as $s ) {
				if ( isset( $s['acf_fc_layout'] ) && $s['acf_fc_layout'] === 'hero_home_page' && ! empty( $s['enabled'] ) ) {
					// Foreground image = vero LCP mobile
					$fg = $s['foreground_image'] ?? '';
					if ( is_array( $fg ) ) {
						$fg_url = $fg['url'] ?? '';
					} elseif ( is_numeric( $fg ) ) {
						$fg_url = wp_get_attachment_image_url( (int) $fg, 'medium_large' );
					} else {
						$fg_url = $fg;
					}
					if ( $fg_url ) {
						echo '<link rel="preload" as="image" fetchpriority="high" href="' . esc_url( $fg_url ) . '">' . "\n";
					}
					// Background image (secondario)
					$bg = $s['background_image'] ?? '';
					if ( is_array( $bg ) ) {
						$bg_url = $bg['url'] ?? '';
					} elseif ( is_numeric( $bg ) ) {
						$bg_url = wp_get_attachment_image_url( (int) $bg, 'full' );
					} else {
						$bg_url = $bg;
					}
					if ( $bg_url ) {
						// L'hero background è il vero LCP su mobile (area maggiore del foreground).
						// Query string rimossa: il CSS inline usa l'URL raw ACF — devono combaciare
						// o il browser scarica la risorsa due volte.
						echo '<link rel="preload" as="image" fetchpriority="high" href="' . esc_url( strtok( $bg_url, '?' ) ) . '">' . "\n";
					}
					break;
				}
			}
		}
	}
}, 1 );

/* Defer/async strategico script 3rd party e WooCommerce non-critical */
add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
	if ( is_admin() ) return $tag;

	// Script che DEVONO restare sync (jQuery core + PEWC + Bootstrap dipendenze)
	$never_defer = array(
		'jquery', 'jquery-core', 'jquery-migrate',
		'pewc-script', 'pewc-conditions', 'pewc-dropzone',
		'wc-add-to-cart-variation',
	);
	if ( in_array( $handle, $never_defer, true ) ) return $tag;

	// Async solo per 3rd party non-critical (NO Iubenda che gestisce cookie consent, NO FB/Pinterest tracking)
	$async_patterns = array(
		'trustindex',
		'brevo.com/js',
		'sibautomation',
	);
	foreach ( $async_patterns as $p ) {
		if ( strpos( $src, $p ) !== false ) {
			if ( strpos( $tag, ' async' ) === false && strpos( $tag, ' defer' ) === false ) {
				return str_replace( '<script ', '<script async ', $tag );
			}
			return $tag;
		}
	}

	// Defer per WooCommerce non-critical (handle reali WC)
	$defer_handles = array(
		'wc-cart-fragments',
		'wc-jquery-blockui',
		'wc-js-cookie',
		'wc-add-to-cart',
	);
	if ( in_array( $handle, $defer_handles, true ) ) {
		if ( strpos( $tag, ' defer' ) === false && strpos( $tag, ' async' ) === false ) {
			return str_replace( '<script ', '<script defer ', $tag );
		}
	}

	return $tag;
}, 10, 3 );

// Rimuovi jquery-migrate (browser moderni non ne hanno bisogno, -5KB blocking)
add_action( 'wp_default_scripts', function( $scripts ) {
	if ( is_admin() ) return;
	if ( ! empty( $scripts->registered['jquery'] ) ) {
		$scripts->registered['jquery']->deps = array_diff(
			$scripts->registered['jquery']->deps,
			array( 'jquery-migrate' )
		);
	}
} );

/**
 * Rimuovi Stripe/PayPal "pay-later messaging" da pagine prodotto singolo.
 * Risparmio: 34KB inline Stripe + 16KB inline PayPal = 50KB parsing.
 * Il checkout mantiene tutti gli script (non tocchiamo).
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( is_admin() ) return;

	// Solo su pagina prodotto singolo (non su cart/checkout/account)
	if ( is_product() ) {
		wp_dequeue_script( 'wc-stripe-product' );
		wp_dequeue_script( 'wc-ppcp-product' );
		wp_dequeue_script( 'wc-stripe-bnpl-messages' );
		wp_dequeue_script( 'wc-ppcp-paylater-messages' );
	}
}, 999 );

/**
 * Rimuovi WP Emoji script/style (browser moderni gestiscono emoji nativamente).
 * Risparmio: ~3KB inline JS + 1 external script.
 */
remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles',     'print_emoji_styles'                );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script'       );
remove_action( 'admin_print_styles',  'print_emoji_styles'                );
remove_filter( 'the_content_feed',    'wp_staticize_emoji'                );
remove_filter( 'comment_text_rss',    'wp_staticize_emoji'                );
remove_filter( 'wp_mail',             'wp_staticize_emoji_for_email'       );
add_filter( 'tiny_mce_plugins', function( $plugins ) {
	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
});

/* Emoji CSS sizing + width/height inline (per non fare CLS) */
add_action( 'wp_head', function() {
	echo '<style>img.wp-smiley,img.emoji{display:inline-block!important;height:1em!important;width:1em!important;min-width:1em!important;min-height:1em!important;margin:0 .07em!important;vertical-align:-.1em!important;background:none!important;box-sizing:content-box}
/* Iubenda banner: fixed = zero layout shift (audit CWV #2) */
#iubenda-cs-banner{position:fixed!important}
/* Gallery pre-init: mostra solo la prima immagine finche flexslider non parte (evita CLS della summary) */
.woocommerce-product-gallery__wrapper>.woocommerce-product-gallery__image:nth-child(n+2){display:none}
.woocommerce-product-gallery .flex-viewport .woocommerce-product-gallery__image:nth-child(n+2){display:block}
/* Mobile: blocca altezza gallery (immagini quadrate) — flexslider init non sposta la summary */
@media (max-width:991.98px){body.single-product .woocommerce-product-gallery{aspect-ratio:1/1;overflow:hidden}}
/* Gallery visibile subito: WC la nasconde con opacity:0 fino a init JS, ritardando LCP di secondi.
   Sicuro perche pre-init mostriamo solo la prima immagine in box a altezza fissa. */
.woocommerce-product-gallery{opacity:1!important}</style>';
}, 1 );
add_action( 'template_redirect', function() {
	if ( is_admin() || is_feed() || wp_doing_ajax() ) return;
	ob_start( function( $html ) {
		// Emoji: dimensioni esplicite (CLS)
		$html = str_replace(
			'<img draggable="false" role="img" class="emoji"',
			'<img width="16" height="16" draggable="false" role="img" class="emoji"',
			$html
		);
		// Iubenda sync: async (era sync render-blocking da ~1300ms; il codice embed
		// incollato nelle impostazioni del plugin non ha async)
		$html = preg_replace(
			'#<script(\s[^>]*src="https://cs\.iubenda\.com/sync/[^"]+"[^>]*)>#',
			'<script async$1>',
			$html
		);
		return $html;
	} );
}, 999 );

/**
 * Delay JS: 3rd party non-critical caricati SOLO dopo prima interazione utente.
 * Riduce TBT mobile drasticamente.
 * ESCLUSI: Iubenda (cookie consent), GTM (già consent-driven).
 */
add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
	if ( is_admin() ) return $tag;

	// Domain patterns da delayare
	$delay_patterns = array(
		'connect.facebook.net',
		'assets.pinterest.com',
		'trustindex.io',
		'cdn.brevo.com',
		'sibautomation.com',
		'sibforms.com',
	);
	foreach ( $delay_patterns as $p ) {
		if ( strpos( $src, $p ) !== false ) {
			// Trasforma in lazy: cambia src -> data-albalu-src, aggiungi type
			$tag = preg_replace(
				'/<script\s/',
				'<script type="albalu/lazy" data-albalu-src="' . esc_attr( $src ) . '" ',
				$tag,
				1
			);
			// Rimuovi il src originale
			$tag = preg_replace( '/\ssrc=(["\'])' . preg_quote( $src, '/' ) . '\1/', '', $tag );
			// Async non ha senso su script lazy
			$tag = str_replace( ' async', '', $tag );
			$tag = str_replace( ' defer', '', $tag );
			return $tag;
		}
	}
	return $tag;
}, 15, 3 );

/**
 * Loader delay JS: aspetta prima interazione utente e attiva script lazy.
 */
add_action( 'wp_footer', function() {
	?>
	<script>
	(function() {
		var loaded = false;
		function loadDelayed() {
			if ( loaded ) return; loaded = true;
			document.querySelectorAll('script[type="albalu/lazy"]').forEach(function(s) {
				var n = document.createElement('script');
				if ( s.dataset.albaluSrc ) n.src = s.dataset.albaluSrc;
				if ( s.async ) n.async = true;
				if ( s.defer ) n.defer = true;
				// Copia altri attributi rilevanti (id, class)
				for ( var i = 0; i < s.attributes.length; i++ ) {
					var a = s.attributes[i];
					if ( ['type','data-albalu-src'].indexOf( a.name ) === -1 ) {
						n.setAttribute( a.name, a.value );
					}
				}
				s.parentNode.insertBefore( n, s );
				s.parentNode.removeChild( s );
			});
			['touchstart','mousemove','scroll','keydown','wheel'].forEach(function(e) {
				window.removeEventListener( e, loadDelayed, { passive: true } );
			});
		}
		['touchstart','mousemove','scroll','keydown','wheel'].forEach(function(e) {
			window.addEventListener( e, loadDelayed, { passive: true } );
		});
		setTimeout( loadDelayed, 5000 );
	})();
	// Megamenu: carica le immagini solo alla prima interazione col menu
	(function() {
		var done = false;
		function loadMenuImgs() {
			if ( done ) return; done = true;
			document.querySelectorAll('img.albalu-menu-img[data-albalu-src]').forEach(function(img) {
				img.src = img.dataset.albaluSrc;
				img.removeAttribute('data-albalu-src');
			});
		}
		document.querySelectorAll('.navbar, .dropdown, #offcanvas-navbar').forEach(function(el) {
			['mouseenter','touchstart','focusin'].forEach(function(e) {
				el.addEventListener( e, loadMenuImgs, { passive: true, once: false } );
			});
		});
		// Fallback: dopo il load completo della pagina (banda ormai libera)
		window.addEventListener('load', function() { setTimeout( loadMenuImgs, 2500 ); });
	})();
	</script>
	<?php
}, 999 );

/**
 * TBT prodotto: defer sulla catena PEWC + jQuery UI via WP script strategy.
 * L'API nativa rifiuta il defer da sola se un handle ha inline script
 * incompatibili o dipendenti non-defer — degrado sicuro.
 * jQuery core resta sync (troppi inline script lo usano).
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( is_admin() || ! function_exists( 'wp_script_add_data' ) ) return;
	$defer_handles = array(
		'pewc-script',
		'pewc-conditions',
		// 'pewc-dropzone' NO: PEWC stampa inline "Dropzone.autoDiscover/options"
		// che usa il globale subito — la libreria deve restare bloccante
		// sulle pagine con campo upload.
		'jquery-ui-core',
		'jquery-ui-datepicker',
		'underscore',
		'wp-util',
	);
	foreach ( $defer_handles as $h ) {
		wp_script_add_data( $h, 'strategy', 'defer' );
	}
}, 1000 );

/**
 * LCP fix: aggiungi fetchpriority=high alla prima immagine gallery su pagina prodotto.
 * L'immagine prodotto è il vero LCP su mobile (non il logo header).
 */
add_filter( 'woocommerce_single_product_image_thumbnail_html', function( $html, $attachment_id ) {
	static $first = true;
	if ( ! is_product() || ! $first ) {
		return $html;
	}
	$first = false;
	// Aggiungi fetchpriority=high all'img (solo se non presente)
	if ( strpos( $html, 'fetchpriority' ) === false ) {
		$html = preg_replace( '/<img\b/', '<img fetchpriority="high"', $html, 1 );
	}
	// Cambia loading="lazy" in loading="eager"
	$html = str_replace( 'loading="lazy"', 'loading="eager"', $html );
	return $html;
}, 10, 2 );

/**
 * Rimuovi fetchpriority=high dal logo header su pagina prodotto e home (non deve competere con LCP).
 */
add_filter( 'wp_get_attachment_image_attributes', function( $attr, $attachment ) {
	if ( ! is_product() && ! is_front_page() ) return $attr;
	if ( ! empty( $attr['class'] ) && strpos( $attr['class'], 'albalu-site-logo' ) !== false ) {
		unset( $attr['fetchpriority'] );
	}
	return $attr;
}, 10, 2 );


/* ====================================================================
 * BLOCCO 1 — SICURO (rischio basso)
 * Risparmio stimato: ~55KB di CSS
 * - Dashicons (35KB) per utenti non loggati
 * - Gutenberg block library (16.5KB) sul frontend
 * Cosa testare: visualizzazione di pagine con blocchi Gutenberg (blog,
 * post editoriali). Se i blocchi appaiono senza stile, rimuovi le
 * righe relative a wp-block-library / global-styles.
 * ==================================================================== */
// ---- BLOCCO 1 START ----
add_action( 'wp_enqueue_scripts', function() {
	if ( is_admin() ) return;

	if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) {
		// PEWC 4.3.16 dichiara dashicons come dipendenza di pewc-style:
		// NON deregistrare (salterebbe pewc-style), ma strippare la dipendenza.
		// Il frontend PEWC non usa glifi dashicons (solo l'admin).
		$styles = wp_styles();
		foreach ( $styles->registered as $handle => $style ) {
			if ( ! empty( $style->deps ) && in_array( 'dashicons', $style->deps, true ) ) {
				$styles->registered[ $handle ]->deps = array_diff( $style->deps, array( 'dashicons' ) );
			}
		}
		wp_dequeue_style( 'dashicons' );
	}

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}, PHP_INT_MAX );
// ---- BLOCCO 1 END ----


/* ====================================================================
 * BLOCCO 2 — RISCHIO MEDIO
 * Risparmio stimato: ~20KB di CSS su pagine non-Woo (home, blog, pagine)
 * - WooCommerce CSS (woocommerce.css, layout, smallscreen)
 * - WooCommerce Blocks CSS
 * Cosa testare: home, articoli del blog e pagine generiche. Verifica
 * che non ci siano widget WooCommerce (es. carrello, prodotti correlati)
 * mostrati fuori dal contesto Woo. Se sì, rimuovi questo blocco o
 * aggiungi le condizioni mancanti.
 * ==================================================================== */
// ---- BLOCCO 2 START ----
add_filter( 'woocommerce_enqueue_styles', function( $styles ) {
	if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		return array();
	}
	return $styles;
} );

add_action( 'wp_enqueue_scripts', function() {
	if ( is_admin() ) return;
	if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		wp_dequeue_style( 'wc-blocks-style' );
		wp_dequeue_style( 'wc-blocks-vendors-style' );
	}
}, 100 );
// ---- BLOCCO 2 END ----


/* ====================================================================
 * BLOCCO 3 — RISCHIO ALTO
 * Risparmio stimato: ~80KB JS + ~15KB CSS sulle pagine non prodotto
 * - PEWC (dropzone 26KB, conditions 10KB, datepicker 11KB, jQuery UI)
 * - jQuery e jquery-core spostati in footer
 * - jquery-migrate rimosso completamente
 *
 * RISCHI:
 *  - Script inline di plugin terzi (Iubenda, Brevo, Pinterest, GTM, FB
 *    Pixel) che usano $/jQuery aspettandosi sia caricato nell'head
 *  - Plugin vecchi che usano API jQuery deprecate (.live, $.browser)
 *  - PEWC su widget/blocchi fuori dalle pagine prodotto
 *
 * Cosa testare: TUTTO. Specialmente: prodotti con personalizzazione
 * PEWC, mobile menu, AJAX add-to-cart, modali, swiper, cookie banner,
 * tracking pixel.
 * ==================================================================== */
// ---- BLOCCO 3 START (solo PEWC — jQuery footer/migrate restano commentati) ----
add_action( 'wp_enqueue_scripts', function() {
	if ( is_admin() ) return;

	if ( ! is_product() ) {
		wp_dequeue_style( 'pewc-style' );
		wp_dequeue_style( 'pewc-dropzone' );
		wp_dequeue_style( 'pewc-basic' );
		wp_dequeue_script( 'pewc-script' );
		wp_dequeue_script( 'pewc-conditions' );
		wp_dequeue_script( 'pewc-dropzone' );
		wp_dequeue_script( 'jquery-ui-datepicker' );
		wp_dequeue_script( 'jquery-ui-core' );
	}
}, 100 );
// ---- BLOCCO 3 END ----

// Force font-display: swap on Font Awesome 7 (must match Bootscore FA version)
add_action( 'wp_head', function() {
	$fa_path = get_template_directory_uri() . '/assets/fontawesome/webfonts/';

	// Preload FA rimosso: 111KB che competevano con l'immagine LCP su mobile.
	// font-display:swap garantisce comunque il render; le icone appaiono poco dopo.
	?>
	<style>
	@font-face {
		font-family: "Font Awesome 7 Free";
		font-style: normal;
		font-weight: 900;
		font-display: swap;
		src: url("<?php echo esc_url( $fa_path ); ?>fa-solid-900.woff2") format("woff2");
	}
	@font-face {
		font-family: "Font Awesome 7 Free";
		font-style: normal;
		font-weight: 400;
		font-display: swap;
		src: url("<?php echo esc_url( $fa_path ); ?>fa-regular-400.woff2") format("woff2");
	}
	@font-face {
		font-family: "Font Awesome 7 Brands";
		font-style: normal;
		font-weight: 400;
		font-display: swap;
		src: url("<?php echo esc_url( $fa_path ); ?>fa-brands-400.woff2") format("woff2");
	}
	</style>
	<?php
}, 1 );

// Child theme replaces parent main.css — avoid loading both (~49KB duplicate)
add_action( 'wp_enqueue_scripts', function() {
	$styles = wp_styles();
	if ( ! isset( $styles->registered['main'] ) ) {
		return;
	}
	$src = $styles->registered['main']->src;
	if ( str_contains( $src, '/themes/bootscore/' ) ) {
		wp_dequeue_style( 'main' );
		wp_deregister_style( 'main' );
	}
}, 15 );

// Non-render-blocking CSS non critical
// Nota: FontAwesome NON qui (icone above-the-fold causano CLS)
add_filter( 'style_loader_tag', function( $tag, $handle ) {
	$non_critical = array(
		'swiper-css',
		'swiper-min-css',
		'swiper-style-css',
		'joinchat',           // WhatsApp button (below fold)
		'wc-blocks-style',    // Woo blocks (non-carrello)
	);
	if ( in_array( $handle, $non_critical, true ) ) {
		$tag = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $tag );
		$tag = str_replace( 'media="all"', 'media="print" onload="this.media=\'all\'"', $tag );
	}
	return $tag;
}, 10, 2 );

/**
 * Pages that need Swiper (sliders / product carousels).
 */
function albalu_needs_swiper() {
	if ( is_front_page() || is_product() || is_shop() || is_product_category() || is_product_tag() ) {
		return true;
	}

	global $post;
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	if ( has_shortcode( $post->post_content, 'bs-swiper-card-product' ) ) {
		return true;
	}

	return (bool) preg_match( '/\b(swiper|creations-swiper|testimonial-swiper|product-slider)\b/', $post->post_content );
}

/**
 * Logo attachment ID (cached). Upload: wp-content/uploads/2024/05/albalu-logo-web.png
 */
function albalu_get_logo_attachment_id() {
	static $logo_id = null;
	if ( null !== $logo_id ) {
		return $logo_id;
	}

	$logo_id = (int) get_theme_mod( 'albalu_logo_attachment_id', 0 );
	if ( ! $logo_id ) {
		$logo_id = (int) attachment_url_to_postid( home_url( '/wp-content/uploads/2024/05/albalu-logo-web.png' ) );
	}

	return $logo_id;
}

/**
 * Optimized header logo markup with srcset for LCP.
 */
function albalu_get_logo_img( $max_height = 80 ) {
	$logo_id     = albalu_get_logo_attachment_id();
	$max_height  = (int) $max_height;
	$intrinsic_w = 600;
	$intrinsic_h = 288;

	if ( $logo_id ) {
		$meta = wp_get_attachment_metadata( $logo_id );
		if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
			$intrinsic_w = (int) $meta['width'];
			$intrinsic_h = (int) $meta['height'];
		}
	}

	$display_width = (int) round( $max_height * ( $intrinsic_w / $intrinsic_h ) );

	$attrs = array(
		'class'         => 'albalu-site-logo',
		'alt'           => 'Albalù Bomboniere Logo',
		'fetchpriority' => 'high',
		'loading'       => 'eager',
		'decoding'      => 'async',
		'width'         => $display_width,
		'height'        => $max_height,
		'sizes'         => $display_width . 'px',
		'style'         => sprintf( 'height: %dpx; width: %dpx;', $max_height, $display_width ),
	);

	if ( $logo_id ) {
		$size = $max_height <= 60 ? 'medium' : 'large';
		return wp_get_attachment_image( $logo_id, $size, false, $attrs );
	}

	return sprintf(
		'<img src="%s" alt="%s" class="%s" style="%s" width="%d" height="%d" sizes="%s" fetchpriority="high" loading="eager" decoding="async">',
		esc_url( home_url( '/wp-content/uploads/2024/05/albalu-logo-web.png' ) ),
		esc_attr( $attrs['alt'] ),
		esc_attr( $attrs['class'] ),
		esc_attr( $attrs['style'] ),
		$display_width,
		$max_height,
		esc_attr( $attrs['sizes'] )
	);
}

/**
 * Preload LCP image: main product image on single product.
 * Home ha preload dedicato del hero foreground (vero LCP mobile).
 * Su altre pagine: logo senza fetchpriority (non è LCP).
 */
add_action( 'wp_head', function() {
	if ( is_product() ) {
		// Su mobile il vero LCP potrebbe essere il titolo H1 non l'immagine.
		// Nessun preload immagine — l'img gallery ha già fetchpriority=high inline via filter.
		return;
	}

	// Su home il vero LCP è il foreground hero (preload gestito altrove). Non preloadare il logo.
	if ( is_front_page() ) return;

	$logo_id = albalu_get_logo_attachment_id();
	if ( $logo_id ) {
		$src = wp_get_attachment_image_url( $logo_id, 'medium' );
	} else {
		$src = home_url( '/wp-content/uploads/2024/05/albalu-logo-web.png' );
	}
	if ( $src ) {
		echo '<link rel="preload" as="image" href="' . esc_url( $src ) . '">' . "\n";
	}
}, 0 );

// Product gallery: prioritize main image, lazy-load thumbnails
add_filter( 'woocommerce_gallery_image_html_attachment_image_params', function( $params, $attachment_id, $image_size ) {
	if ( ! is_product() ) {
		return $params;
	}

	global $product;
	if ( ! $product ) {
		return $params;
	}

	if ( (int) $attachment_id === (int) $product->get_image_id() ) {
		$params['fetchpriority'] = 'high';
		$params['loading']       = 'eager';
	} else {
		$params['loading'] = 'lazy';
	}

	return $params;
}, 10, 3 );


add_action('wp_enqueue_scripts', 'bootscore_child_enqueue_styles');
function bootscore_child_enqueue_styles() {

  // Usa la versione minificata se esiste (rigenerare con: npx csso-cli main.css -o main.min.css)
  $albalu_css_file = file_exists( get_stylesheet_directory() . '/assets/css/main.min.css' ) ? '/assets/css/main.min.css' : '/assets/css/main.css';
  $modified_bootscoreChildCss = date('YmdHi', filemtime(get_stylesheet_directory() . $albalu_css_file));
  wp_enqueue_style('main', get_stylesheet_directory_uri() . $albalu_css_file, array('parent-style'), $modified_bootscoreChildCss);

  wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

  // Swiper è già fornito dal plugin bs-swiper (dequeue solo se non serve, no CDN duplicate)
  if ( ! albalu_needs_swiper() ) {
    wp_dequeue_style( 'swiper-min-css' );
    wp_dequeue_style( 'swiper-style-css' );
    wp_dequeue_script( 'swiper-min-js' );
    wp_dequeue_script( 'swiper-init-js' );
    $swiper_dep = array('jquery');
  } else {
    $swiper_dep = array('jquery', 'swiper-min-js');
  }

  $modificated_CustomJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/custom.js'));
  wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/assets/js/custom.js', $swiper_dep, $modificated_CustomJS, true);
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

//4b. WooCommerce: 72 prodotti per pagina negli archivi
add_filter( 'loop_shop_per_page', function() { return 72; } );


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
/* Eventi disponibili come filtro nella search bar (slug => label).
   Modificare slug per matchare le categorie WooCommerce reali. */
/* Eventi filtro ricerca: term_id => label. Gli ID sono stabili anche se
   gli slug delle categorie vengono rinominati per SEO. */
function albalu_get_search_events() {
	return array(
		2771 => 'Battesimo',
		2772 => 'Comunione',
		2773 => 'Cresima',
		2775 => 'Laurea',
		2776 => 'Matrimonio',
		2777 => 'Anniversario',
		2774 => 'Compleanno',
		4649 => 'Natale',
	);
}

/* Mostra solo i prodotti nei risultati di ricerca + filtro per evento */
function search_only_products($query) {
	if ( is_admin() || ! $query->is_search() || ! $query->is_main_query() ) {
		return $query;
	}

	$query->set( 'post_type', 'product' );
	$query->set( 'wc_query', 'product_query' );

	// Filtro per evento (categoria prodotto, per term_id).
	// Con "Tutti" (nessun evento) la ricerca copre SOLO gli articoli generici,
	// cioè quelli fuori dalle categorie evento del filtro (sottocategorie incluse).
	$events    = albalu_get_search_events();
	$event_id  = ! empty( $_GET['event'] ) ? absint( $_GET['event'] ) : 0;
	$tax_query = $query->get( 'tax_query' );
	if ( ! is_array( $tax_query ) ) {
		$tax_query = array();
	}

	if ( $event_id && isset( $events[ $event_id ] ) ) {
		$tax_query[] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => $event_id,
			'include_children' => true,
		);
	} else {
		$tax_query[] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => array_keys( $events ),
			'operator'         => 'NOT IN',
			'include_children' => true,
		);
	}
	$query->set( 'tax_query', $tax_query );

	return $query;
}
add_filter('pre_get_posts','search_only_products');

//------------------- END ---------------------

//7a. SEO: rimuovi dal breadcrumb le categorie madri che reindirizzano (301)
//------------------- START ---------------------
/* Le madri delle 6 pagine vincenti reindirizzano alla sottocategoria stessa:
   linkarle nel breadcrumb (visibile e schema) = auto-link a un 301.
   Term ID stabili delle madri reindirizzate. */
function albalu_redirected_parent_cat_ids() {
	return array( 2771, 2773, 2774, 2775, 2776, 2777 );
}

function albalu_redirected_parent_cat_urls() {
	static $urls = null;
	if ( null !== $urls ) {
		return $urls;
	}
	$urls = array();
	foreach ( albalu_redirected_parent_cat_ids() as $tid ) {
		$link = get_term_link( (int) $tid, 'product_cat' );
		if ( ! is_wp_error( $link ) ) {
			$urls[] = untrailingslashit( $link );
		}
	}
	return $urls;
}

/* Breadcrumb visibile (WooCommerce): [ [name, url], ... ] */
add_filter( 'woocommerce_get_breadcrumb', function( $crumbs ) {
	$skip = albalu_redirected_parent_cat_urls();
	if ( empty( $skip ) ) {
		return $crumbs;
	}
	return array_values( array_filter( $crumbs, function( $crumb ) use ( $skip ) {
		$url = isset( $crumb[1] ) ? untrailingslashit( $crumb[1] ) : '';
		return '' === $url || ! in_array( $url, $skip, true );
	} ) );
}, 20 );

/* Schema BreadcrumbList (Yoast): [ [url, text], ... ] */
add_filter( 'wpseo_breadcrumb_links', function( $links ) {
	$skip = albalu_redirected_parent_cat_urls();
	if ( empty( $skip ) ) {
		return $links;
	}
	return array_values( array_filter( $links, function( $link ) use ( $skip ) {
		$url = isset( $link['url'] ) ? untrailingslashit( $link['url'] ) : '';
		return '' === $url || ! in_array( $url, $skip, true );
	} ) );
}, 20 );
//------------------- END ---------------------

//7c. FAQ categoria: sezione sopra il footer + schema FAQPage nell'head
//------------------- START ---------------------
/* Legge il repeater ACF "faqpage-category" della categoria corrente.
   Cache statica: usato sia dal render (footer) sia dallo schema (head). */
function albalu_get_category_faqs() {
	static $faqs = null;
	if ( null !== $faqs ) {
		return $faqs;
	}
	$faqs = array();
	if ( ! is_product_category() || ! function_exists( 'have_rows' ) ) {
		return $faqs;
	}
	$term = get_queried_object();
	if ( ! $term || is_wp_error( $term ) ) {
		return $faqs;
	}
	if ( have_rows( 'faqpage-category', $term ) ) {
		while ( have_rows( 'faqpage-category', $term ) ) {
			the_row();
			$q = get_sub_field( 'faqpage-category-question' );
			$a = get_sub_field( 'faqpage-category-answer' );
			if ( $q && $a ) {
				$faqs[] = array( 'q' => $q, 'a' => $a );
			}
		}
	}
	return $faqs;
}

/* Schema FAQPage nell'head (solo pagine categoria con FAQ compilate) */
add_action( 'wp_head', function() {
	$faqs = albalu_get_category_faqs();
	if ( empty( $faqs ) ) {
		return;
	}
	$term_link = get_term_link( get_queried_object() );
	if ( is_wp_error( $term_link ) ) {
		return;
	}
	$main_entity = array();
	foreach ( $faqs as $f ) {
		$main_entity[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $f['q'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_kses_post( $f['a'] ),
			),
		);
	}
	$schema = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'FAQPage',
		'@id'              => $term_link . '#faq',
		'inLanguage'       => 'it-IT',
		'mainEntityOfPage' => array( '@id' => $term_link ),
		'mainEntity'       => $main_entity,
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 8 );

/* Sezione FAQ visibile: dopo la description della categoria, sopra il footer */
add_action( 'woocommerce_after_shop_loop', function() {
	$faqs = albalu_get_category_faqs();
	if ( empty( $faqs ) ) {
		return;
	}
	?>
	<section class="albalu-cat-faq mt-5 mb-4" id="faq">
		<h2 class="fw-normal mb-4">Domande Frequenti (FAQ)</h2>
		<?php foreach ( $faqs as $f ) : ?>
			<div class="albalu-cat-faq-item mb-4">
				<h3 class="fw-bold fs-4 mb-2"><?php echo esc_html( $f['q'] ); ?></h3>
				<div class="albalu-cat-faq-answer"><?php echo wp_kses_post( $f['a'] ); ?></div>
			</div>
		<?php endforeach; ?>
	</section>
	<?php
}, 25 );
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

    $schema_items = array();

    ob_start();
    ?>
    <div class="albalu-faq-section bg-albalu-warm">
        <div class="container">
            <?php while ( have_rows( 'faq', 'option' ) ) : the_row();
                $q = get_sub_field( 'faq-question' );
                $a = get_sub_field( 'faq-answer' );
                if ( ! $q ) continue;
                if ( $a ) {
                    $schema_items[] = array(
                        '@type'          => 'Question',
                        'name'           => wp_strip_all_tags( $q ),
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text'  => wp_kses_post( wpautop( $a ) ),
                        ),
                    );
                }
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

    <?php if ( ! empty( $schema_items ) ) : ?>
    <script type="application/ld+json"><?php echo wp_json_encode( array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $schema_items,
    ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
    <?php endif; ?>

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
				'@type'       => 'Product',
				'name'        => $var_product->get_name(),
				'description' => wp_strip_all_tags( $var_desc ),
				'sku'         => $var_product->get_sku(),
				'isVariantOf' => array(
					'@type'          => 'ProductGroup',
					'name'           => $product->get_name(),
					'productGroupID' => (string) $product->get_id(),
				),
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

// Append pagination to archive title (Yoast SEO and WordPress fallback)
add_filter( 'wpseo_title', function( $title ) {
    if ( ( is_product_category() || is_product_tag() || is_shop() || is_archive() ) && ! is_singular() ) {
        $paged = max( 1, get_query_var( 'paged' ) );
        if ( $paged > 1 ) {
            $title .= ' – Pagina ' . $paged;
        }
    }
    return $title;
} );