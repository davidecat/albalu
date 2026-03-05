<?php
/**
 * The template for displaying the front page
 *
 * @package Bootscore
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();
?>

<div id="content" class="site-content p-0">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">

            <!-- 1. Trust Strip (Template 37161) -->
            <section class="trust-strip py-2 bg-albalu-warm">
                <div class="container">
                    <div class="row align-items-center">
                        <!-- Left: Text -->
                        <div class="col-lg-7 mb-2 mb-lg-0 text-center text-lg-start">
                             <p class="mb-0">
                                Produciamo <strong>Bomboniere ed Articoli da regalo</strong> 100% artigianali e Made in Italy dal 1991
                             </p>
                        </div>
                        <!-- Right: Google Reviews -->
                        <div class="col-lg-5 text-center text-lg-end">
                            <div class="d-inline-flex align-items-center justify-content-lg-end justify-content-center">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3 google-logo-circle">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48 48"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path></defs><clipPath id="b"><use xlink:href="#a"></use></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"></path><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"></path><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"></path><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"></path></svg>
                                </div>
                                <div class="text-start lh-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="fw-bold me-2 trust-strip-brand">Albalù Bomboniere</span>
                                        <span class="text-warning small trust-strip-stars">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        </span>
                                    </div>
                                    <span class="small text-muted fw-bold trust-strip-reviews">+800 recensioni</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. Hero Section -->
            <?php
            // Try ACF hero data first
            $_hero_data = null;
            if ( function_exists('get_field') ) {
                $_rows = get_field( 'page_sections', get_the_ID() );
                if ( is_array( $_rows ) ) {
                    foreach ( $_rows as $_row ) {
                        if ( ! is_array( $_row ) ) { continue; }
                        if ( ( $_row['acf_fc_layout'] ?? '' ) !== 'hero' ) { continue; }
                        if ( isset( $_row['enabled'] ) && ! $_row['enabled'] ) { continue; }
                        $_hero_data = array(
                            'enabled'             => isset( $_row['enabled'] ) ? (bool) $_row['enabled'] : true,
                            'title'               => isset( $_row['title'] ) ? (string) $_row['title'] : '',
                            'subtitle'            => isset( $_row['subtitle'] ) ? (string) $_row['subtitle'] : '',
                            'newsletter_title'    => isset( $_row['newsletter_title'] ) ? (string) $_row['newsletter_title'] : '',
                            'newsletter_subtitle' => isset( $_row['newsletter_subtitle'] ) ? (string) $_row['newsletter_subtitle'] : '',
                            'btn_text'            => isset( $_row['btn_text'] ) ? (string) $_row['btn_text'] : '',
                            'btn_url'             => isset( $_row['btn_url'] ) ? (string) $_row['btn_url'] : '',
                            'foreground_image'    => isset( $_row['foreground_image'] ) ? (string) $_row['foreground_image'] : '',
                            'background_image'    => isset( $_row['background_image'] ) ? (string) $_row['background_image'] : '',
                        );
                        break;
                    }
                }
            }

            // Fall back to defaults if no ACF data found
            if ( ! $_hero_data ) {
                $_hero_data = array(
                    'enabled'             => true,
                    'title'               => 'Bomboniere originali con kit<br>confezione in omaggio',
                    'subtitle'            => 'Su Albalù puoi trovare bomboniere originali e utili, complete di kit confezione in omaggio!',
                    'newsletter_title'    => 'Iscriviti alla newsletter di Albalù!',
                    'newsletter_subtitle' => 'Ottieni sconti esclusivi: iscriviti alla newsletter',
                    'btn_text'            => 'Clicca qui',
                    'btn_url'             => '#newsletter',
                    'foreground_image'    => 'https://albalu.b-cdn.net/wp-content/uploads/elementor/thumbs/confezioni-rinj44ahw9j48paqp3mtpbyjedbhrd85lujzwhirjg.webp',
                    'background_image'    => content_url('/uploads/2026/02/albalu-background-home-01.webp'),
                );
            }
            ?>
            <?php if ( $_hero_data['enabled'] ) : ?>
            <div class="container mt-4">
                <section class="hero-parallax d-flex align-items-center overflow-hidden" style="background-image: url('<?php echo esc_url($_hero_data['background_image']); ?>');">
                    <div class="container-fluid px-4">
                        <div class="row align-items-center">
                            <div class="col-lg-6 py-5 text-white">
                                <h1 class="display-4 fw-bold mb-3">
                                    <?php echo wp_kses_post($_hero_data['title']); ?>
                                </h1>
                                <p class="lead mb-4">
                                    <?php echo wp_kses_post($_hero_data['subtitle']); ?>
                                </p>
                                <div class="mb-4">
                                    <p class="mb-2 fw-bold"><?php echo esc_html($_hero_data['newsletter_title']); ?></p>
                                    <p class="mb-3 small"><?php echo esc_html($_hero_data['newsletter_subtitle']); ?></p>
                                </div>
                                <a href="<?php echo esc_url($_hero_data['btn_url']); ?>" class="btn btn-primary text-white rounded-0 px-4 py-3 text-uppercase fw-medium shadow-sm" >
                                    <?php echo esc_html($_hero_data['btn_text']); ?> <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                            <div class="col-lg-6 text-center">
                                <?php if ( ! empty( $_hero_data['foreground_image'] ) ) : ?>
                                <img src="<?php echo esc_url($_hero_data['foreground_image']); ?>" alt="<?php echo esc_attr(strip_tags($_hero_data['title'])); ?>" class="img-fluid">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <?php endif; ?>

            <!-- 2. Most Requested Products (WooCommerce Shortcode) -->
            <section class="products-section py-5 bg-white">
                <div class="container">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
                        <h2 class="mb-3 mb-md-0" >Le <strong>bomboniere</strong> più richieste per <strong>ogni tipo di evento</strong></h2>
                        <a href="/shop/" class="btn btn-primary px-4 py-2 shadow-sm" >Scopri il catalogo <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                    <div class="staging-product-grid">
                        <?php //echo do_shortcode('[products limit="4" columns="4" orderby="popularity"]'); ?>
                         <?php echo do_shortcode('[bs-swiper-card-product order="ASC" orderby="popularity" posts="12"]
'); ?>
                        
                    </div>
                </div>
            </section>

            <!-- 3. Categories Grid (Template 37159) -->
            <section class="categories-section py-5 bg-albalu-warm">
                <div class="container">
                    <div class="text-center mb-5">
                        <span class="text-uppercase small fw-bold ls-1">Il nostro catalogo</span>
                        <h2 class="h1 mt-2">Articoli per ogni <strong>occasione e cerimonia</strong></h2>
                    </div>
                    
                    <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 justify-content-center">
                        <?php
                        // Check if ACF Repeater has rows
                        if ( have_rows('homepage_categories') ) {
                            while ( have_rows('homepage_categories') ) {
                                the_row();
                                $category_obj = get_sub_field('category');
                                $custom_title = get_sub_field('custom_title');
                                $custom_image = get_sub_field('custom_image');

                                if ( $category_obj ) {
                                    // Use custom title if set, otherwise category name
                                    $cat_name = !empty($custom_title) ? $custom_title : $category_obj->name;
                                    
                                    // Use custom image if set, otherwise category thumbnail
                                    if ( !empty($custom_image) ) {
                                        $image_url = $custom_image;
                                    } else {
                                        $thumbnail_id = get_term_meta( $category_obj->term_id, 'thumbnail_id', true );
                                        $image_url = wp_get_attachment_url( $thumbnail_id );
                                        if ( !$image_url ) {
                                            $image_url = wc_placeholder_img_src();
                                        }
                                    }
                                    ?>
                                    <div class="col">
                                        <div class="card border-0 h-100 category-card bg-white p-3">
                                            <div class="ratio ratio-1x1 overflow-hidden mb-3">
                                                <a href="<?php echo esc_url( get_term_link( $category_obj ) ); ?>">
                                                    <img src="<?php echo esc_url( $image_url ); ?>" class="img-fluid object-fit-cover w-100 h-100" alt="<?php echo esc_attr( $cat_name ); ?>">
                                                </a>
                                            </div>
                                            <div class="category-content">
                                                <h5 class="h6 fw-bold text-uppercase mb-2">
                                                    <a href="<?php echo esc_url( get_term_link( $category_obj ) ); ?>" class="text-decoration-none text-dark">
                                                        <?php echo esc_html( $cat_name ); ?>
                                                    </a>
                                                </h5>
                                                <a href="<?php echo esc_url( get_term_link( $category_obj ) ); ?>" class="category-link small fw-bold text-decoration-none">
                                                    Tutti i prodotti <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                        } else {
                            // Fallback to default behavior if no ACF rows found
                            $args = array(
                                'taxonomy'   => 'product_cat',
                                'hide_empty' => true,
                                'parent'     => 0,
                                'orderby'    => 'menu_order',
                                'order'      => 'ASC',
                                'number'     => 8,
                            );
                            $product_categories = get_terms($args);

                            if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
                                foreach ( $product_categories as $category ) {
                                    $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
                                    $image_url = wp_get_attachment_url( $thumbnail_id );
                                    if ( ! $image_url ) $image_url = wc_placeholder_img_src();
                                    ?>
                                    <div class="col">
                                        <div class="card border-0 h-100 category-card bg-white p-3">
                                            <div class="ratio ratio-1x1 overflow-hidden mb-3">
                                                <a href="<?php echo esc_url( get_term_link( $category ) ); ?>">
                                                    <img src="<?php echo esc_url( $image_url ); ?>" class="img-fluid object-fit-cover w-100 h-100" alt="<?php echo esc_attr( $category->name ); ?>">
                                                </a>
                                            </div>
                                            <div class="category-content">
                                                <h5 class="h6 fw-bold mb-2">
                                                    <a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="text-decoration-none category-title-link">
                                                        <?php echo esc_html( $category->name ); ?>
                                                    </a>
                                                </h5>
                                                <a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="category-link small text-uppercase fw-bold text-decoration-none">
                                                    Tutti i prodotti <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Dynamic Page Sections -->
            <?php
            $_post_id = (int) get_the_ID();

            // === ACF Flexible Content sections ===
            if ( function_exists('have_rows') && have_rows('page_sections', $_post_id) ) :
                while ( have_rows('page_sections', $_post_id) ) :
                    the_row();
                    $_layout = get_row_layout();
                    $_enabled = (bool) get_sub_field('enabled');
                    if ( ! $_enabled ) { continue; }

                    // --- PROMO layout ---
                    if ( $_layout === 'promo' ) :
                        $_promo_layout = (string) get_sub_field('image_position') ?: 'right';
                        $_promo_subtitle = (string) get_sub_field('subtitle');
                        $_promo_title = (string) get_sub_field('title');
                        $_promo_content = (string) get_sub_field('content');
                        $_promo_btn_text = (string) get_sub_field('btn_text');
                        $_promo_btn_url_raw = trim( (string) get_sub_field('btn_url') );
                        $_promo_image = (string) get_sub_field('image');

                        // Normalize URL (inline albalu_normalize_url)
                        $_promo_btn_url = $_promo_btn_url_raw;
                        if ( $_promo_btn_url !== '' && ! preg_match('#^(https?:)?//#i', $_promo_btn_url) && ! preg_match('#^(mailto:|tel:)#i', $_promo_btn_url) && strpos($_promo_btn_url, '#') !== 0 ) {
                            if ( strpos($_promo_btn_url, '/') !== 0 ) { $_promo_btn_url = '/' . $_promo_btn_url; }
                            $_promo_btn_url = home_url($_promo_btn_url);
                        }

                        if ( $_promo_layout === 'left' ) {
                            $_promo_text_classes = 'col-lg-7 order-2 order-lg-2';
                            $_promo_image_classes = 'col-lg-5 order-1 order-lg-1 mb-4 mb-lg-0';
                        } else {
                            $_promo_text_classes = 'col-lg-7 order-2 order-lg-1';
                            $_promo_image_classes = 'col-lg-5 order-1 order-lg-2 mb-4 mb-lg-0';
                        }
                    ?>
                    <section class="promo-section-dynamic py-5">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="<?php echo esc_attr($_promo_text_classes); ?>">
                                    <?php if ( $_promo_subtitle ) : ?>
                                        <span class="text-uppercase text-muted small fw-bold ls-1"><?php echo esc_html($_promo_subtitle); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $_promo_title ) : ?>
                                        <h2 class="h1 fw-bold my-3"><?php echo wp_kses_post($_promo_title); ?></h2>
                                    <?php endif; ?>
                                    <?php if ( $_promo_content ) : ?>
                                        <div class="mb-4 text-secondary promo-content"><?php echo wp_kses_post($_promo_content); ?></div>
                                    <?php endif; ?>
                                    <?php if ( $_promo_btn_text && $_promo_btn_url ) : ?>
                                        <a href="<?php echo esc_url($_promo_btn_url); ?>" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm"><?php echo esc_html($_promo_btn_text); ?></a>
                                    <?php endif; ?>
                                </div>
                                <div class="<?php echo esc_attr($_promo_image_classes); ?>">
                                    <div class="ratio ratio-4x3 rounded-3 overflow-hidden">
                                        <?php if ( $_promo_image ) : ?>
                                            <img src="<?php echo esc_url($_promo_image); ?>" class="object-fit-cover" alt="<?php echo esc_attr(wp_strip_all_tags($_promo_title)); ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php
                    // --- NEWSLETTER layout ---
                    if ( $_layout === 'newsletter' ) :
                        $_nl_subtitle = (string) get_sub_field('subtitle');
                        $_nl_title = (string) get_sub_field('title');
                        $_nl_content = (string) get_sub_field('content');
                        $_nl_btn_text = (string) get_sub_field('btn_text');
                        $_nl_btn_url = (string) get_sub_field('btn_url');
                        $_nl_bg_color = (string) get_sub_field('bg_color') ?: '#9EA6A9';
                    ?>
                    <section class="newsletter-section py-5 text-white" style="background-color: <?php echo esc_attr($_nl_bg_color); ?> !important;">
                        <div class="container my-5">
                            <div class="row">
                                <div class="col-lg-8 text-start">
                                    <?php if ( $_nl_subtitle ) : ?>
                                        <span class="h5 text-uppercase fw-medium ls-1 text-white"><?php echo esc_html($_nl_subtitle); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $_nl_title ) : ?>
                                        <h2 class="h1 fw-medium my-3 text-white"><?php echo wp_kses_post($_nl_title); ?></h2>
                                    <?php endif; ?>
                                    <?php if ( $_nl_content ) : ?>
                                        <div class="h5 mb-4 text-white fw-medium"><?php echo wp_kses_post($_nl_content); ?></div>
                                    <?php endif; ?>
                                    <?php if ( $_nl_btn_text ) : ?>
                                        <div class="d-flex justify-content-start">
                                            <a href="<?php echo esc_url($_nl_btn_url); ?>" class="btn btn-primary px-4 py-2 text-white shadow-sm"><?php echo esc_html($_nl_btn_text); ?> <i class="fas fa-arrow-right ms-2"></i></a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php
                    // --- FEATURES layout ---
                    if ( $_layout === 'features' ) :
                        $_feat_items = get_sub_field('items');
                        if ( ! is_array($_feat_items) ) { $_feat_items = array(); }
                    ?>
                    <section class="features-section py-5 bg-white">
                        <div class="container">
                            <div class="row g-4">
                                <?php foreach ( $_feat_items as $_feat_item ) :
                                    $_feat_icon = isset($_feat_item['icon']) ? $_feat_item['icon'] : '';
                                    $_feat_title = isset($_feat_item['title']) ? $_feat_item['title'] : '';
                                    $_feat_desc = isset($_feat_item['description']) ? $_feat_item['description'] : '';
                                ?>
                                <div class="col-md-4">
                                    <div class="h-100 ps-4 border-start">
                                        <?php if ( $_feat_icon ) : ?>
                                        <div class="mb-3">
                                            <img src="<?php echo esc_url($_feat_icon); ?>" alt="<?php echo esc_attr($_feat_title); ?>">
                                        </div>
                                        <?php endif; ?>
                                        <?php if ( $_feat_title ) : ?>
                                        <h4 class="fw-medium text-uppercase mb-2"><?php echo esc_html($_feat_title); ?></h4>
                                        <?php endif; ?>
                                        <?php if ( $_feat_desc ) : ?>
                                        <p class="fw-medium mb-0"><?php echo wp_kses_post($_feat_desc); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php
                    // --- TESTIMONIALS layout ---
                    if ( $_layout === 'testimonials' ) :
                        $_test_title = (string) get_sub_field('title') ?: 'Le <strong>testimonianze</strong> dei nostri clienti';
                        $_test_desc = wp_strip_all_tags( (string) get_sub_field('content') ) ?: 'Crediamo nella forza dei dettagli, nella ricerca dei materiali, nella creazione della linea e siamo felici di accompagnare il cliente dalla scelta del prodotto fino all\'assistenza post vendita per soddisfare ogni sua esigenza.';
                        $_test_reviews = get_sub_field('reviews');
                        if ( ! is_array($_test_reviews) ) { $_test_reviews = array(); }
                    ?>
                    <section class="testimonials-section py-5 bg-albalu-warm">
                        <div class="container-custom">
                            <div class="text-center mb-5">
                                <span class="text-uppercase text-muted small fw-bold ls-1">Dicono di noi</span>
                                <h2 class="h1 fw-bold mt-2"><?php echo wp_kses_post($_test_title); ?></h2>
                                <p class="text-secondary mx-auto testimonials-desc"><?php echo esc_html($_test_desc); ?></p>
                            </div>
                            <div class="swiper testimonial-swiper pb-5">
                                <div class="swiper-wrapper">
                                    <?php foreach ( $_test_reviews as $_review ) :
                                        $_rev_img = isset($_review['img']) ? $_review['img'] : '';
                                        if ( is_array($_rev_img) && isset($_rev_img['url']) ) { $_rev_img = $_rev_img['url']; }
                                    ?>
                                    <div class="swiper-slide p-2">
                                        <div class="testimonial-box position-relative bg-white p-4 rounded-3 shadow-sm mb-4">
                                            <div class="position-absolute top-0 end-0 mt-3 me-3">
                                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm google-logo-circle">
                                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48 48"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path></defs><clipPath id="b"><use xlink:href="#a"></use></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"></path><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"></path><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"></path><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"></path></svg>
                                                </div>
                                            </div>
                                            <div class="text-warning mb-2"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                            <p class="text-dark mb-2 testimonial-text"><?php echo esc_html($_review['text']); ?></p>
                                            <a href="#" class="text-secondary small text-decoration-none d-inline-block">Leggi di più</a>
                                        </div>
                                        <div class="d-flex align-items-center ms-3">
                                            <?php if ( ! empty($_rev_img) ) : ?>
                                                <img src="<?php echo esc_url($_rev_img); ?>" class="rounded-circle me-3 reviewer-avatar" alt="<?php echo esc_attr($_review['name']); ?>">
                                            <?php else : ?>
                                                <div class="rounded-circle overflow-hidden me-3 reviewer-avatar">
                                                    <img src="/wp-content/uploads/2026/01/user-placeholder.jpg" onerror="this.src='https://secure.gravatar.com/avatar/?s=50&d=mm&r=g'" alt="<?php echo esc_attr($_review['name']); ?>" class="img-fluid w-100 h-100 object-fit-cover">
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark reviewer-name"><?php echo esc_html($_review['name']); ?></h6>
                                                <small class="text-muted reviewer-date"><?php echo esc_html($_review['date']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php
                    // --- GALLERY layout ---
                    if ( $_layout === 'gallery' ) :
                        $_gal_title = (string) get_sub_field('title') ?: 'Alcune delle <strong>nostre creazioni</strong>';
                        $_gal_btn_text = (string) get_sub_field('btn_text') ?: 'Esplora il catalogo';
                        $_gal_btn_url = (string) get_sub_field('btn_url') ?: '#';
                        $_gal_images = get_sub_field('images');
                        if ( ! is_array($_gal_images) ) { $_gal_images = array(); }
                    ?>
                    <section class="creations-section py-5 bg-light">
                        <div class="container-custom">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="h3 fw-bold mb-0"><?php echo wp_kses_post($_gal_title); ?></h2>
                                <a href="<?php echo esc_url($_gal_btn_url); ?>" class="btn btn-primary text-white"><?php echo esc_html($_gal_btn_text); ?> &rarr;</a>
                            </div>
                        </div>
                        <div class="container-fluid px-0">
                            <div class="swiper creations-swiper">
                                <div class="swiper-wrapper">
                                    <?php foreach ( $_gal_images as $_gal_img ) :
                                        $_gal_img_url = is_array($_gal_img) ? ($_gal_img['url'] ?? '') : $_gal_img;
                                    ?>
                                    <div class="swiper-slide">
                                        <div class="ratio ratio-1x1 bg-white rounded-3 shadow-sm overflow-hidden h-100">
                                            <img src="<?php echo esc_url($_gal_img_url); ?>" class="object-fit-contain w-100 h-100 p-2 transition-transform" alt="Creazione Albalù">
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                <?php
                endwhile;
            endif;
            ?>

        </main>
    </div>
</div>

<?php
get_footer();
