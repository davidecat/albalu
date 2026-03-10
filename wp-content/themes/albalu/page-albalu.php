<?php
/* Template Name: Albalu */
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div id="content" class="site-content p-0">

    <?php if ( ! is_front_page() ) : ?>
    <section class="page-title-bar bg-albalu-warm py-4 mb-4">
        <div class="container">
            <?php the_title('<h1 class="fs-2 fw-normal mb-0">', '</h1>'); ?>
        </div>
    </section>
    <?php endif; ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">

            <?php
            if ( function_exists( 'have_rows' ) && have_rows( 'chi_siamo_sections' ) ) :
                while ( have_rows( 'chi_siamo_sections' ) ) : the_row();
                    $layout = get_row_layout();

                    // --- HERO ---
                    if ( $layout === 'hero' ) :
                        $top_title = get_sub_field( 'top_title' );
                        $title     = get_sub_field( 'title' );
                        $subtitle  = get_sub_field( 'subtitle' );
                        $content   = get_sub_field( 'content' );
                        $bg_image  = get_sub_field( 'bg_image' );
                        $bg_color  = get_sub_field( 'bg_color' ) ?: '#f8f9fa';
                    ?>
                        <section class="chi-hero py-5" <?php if ( $bg_image ) : ?>style="--chi-hero-bg:url('<?php echo esc_url( $bg_image ); ?>');"<?php endif; ?>>
                            <div class="container py-5">
                                <?php if ( $top_title ) : ?>
                                    <p class="text-uppercase fw-bold fs-5 letter-spacing-2 mb-2"><?php echo esc_html( $top_title ); ?></p>
                                <?php endif; ?>
                                <?php if ( $title ) : ?>
                                    <h1 class="fw-normal mb-4 border-bottom"><?php echo wp_kses_post( $title ); ?></h1>
                                <?php endif; ?>
                                <?php if ( $subtitle ) : ?>
                                    <div class="lead mb-5 border-bottom fw-medium fs-4"><?php echo wp_kses_post( $subtitle ); ?></div>
                                <?php endif; ?>
                                <?php if ( $content ) : ?>
                                    <div class="chi-hero-content chi-section-content lead mb-5 fw-medium"><?php echo wp_kses_post( $content ); ?></div>
                                <?php endif; ?>
                            </div>
                        </section>

                    <?php
                    // --- TEXT BLOCK ---
                    elseif ( $layout === 'text_block' ) :
                        $top_title  = get_sub_field( 'top_title' );
                        $title      = get_sub_field( 'title' );
                        $subtitle   = get_sub_field( 'subtitle' );
                        $content    = get_sub_field( 'content' );
                        $image      = get_sub_field( 'image' );
                        $image_link = get_sub_field( 'image_link' );
                        $bg_color   = get_sub_field( 'bg_color' );
                        $text_align = get_sub_field( 'text_align' ) ?: 'center';
                        $bg_style   = $bg_color ? "background-color:" . esc_attr( $bg_color ) . ";" : '';
                    ?>
                        <section class="chi-text-block py-5" <?php echo $bg_style ? 'style="' . $bg_style . '"' : ''; ?>>
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-lg-9 text-<?php echo esc_attr( $text_align ); ?>">
                                        <?php if ( $top_title ) : ?>
                                            <p class="text-uppercase fw-bold fs-5 letter-spacing-2 mb-2"><?php echo esc_html( $top_title ); ?></p>
                                        <?php endif; ?>
                                        <?php if ( $title ) : ?>
                                            <h2 class="fw-normal mb-4 border-bottom"><?php echo wp_kses_post( $title ); ?></h2>
                                        <?php endif; ?>
                                        <?php if ( $subtitle ) : ?>
                                            <div class="lead mb-5 border-bottom fw-medium fs-4"><?php echo wp_kses_post( $subtitle ); ?></div>
                                        <?php endif; ?>
                                        <?php if ( $content ) : ?>
                                            <div class="chi-hero-content chi-section-content lead fw-medium"><?php echo wp_kses_post( $content ); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ( $image ) : ?>
                                    <div class="text-center mt-5">
                                        <?php if ( $image_link ) : ?>
                                            <a href="<?php echo esc_url( $image_link ); ?>">
                                        <?php endif; ?>
                                        <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( strip_tags( $title ) ); ?>" class="img-fluid rounded">
                                        <?php if ( $image_link ) : ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                    <?php
                    // --- TEXT WITH IMAGE ---
                    elseif ( $layout === 'text_with_image' ) :
                        $top_title      = get_sub_field( 'top_title' );
                        $title          = get_sub_field( 'title' );
                        $content        = get_sub_field( 'content' );
                        $btn_text       = get_sub_field( 'btn_text' );
                        $btn_link       = get_sub_field( 'btn_link' );
                        $btn_icon       = get_sub_field( 'btn_icon' );
                        $btn_icon_pos   = get_sub_field( 'btn_icon_position' ) ?: 'right';
                        $image          = get_sub_field( 'image' );
                        $img_layout     = get_sub_field( 'layout' ) ?: 'image_right';
                        $bg_color       = get_sub_field( 'bg_color' );
                        $bg_style    = $bg_color ? "background-color:" . esc_attr( $bg_color ) . ";" : '';
                        $order_class = ( $img_layout === 'image_left' ) ? 'order-lg-2' : '';
                        $img_order   = ( $img_layout === 'image_left' ) ? 'order-lg-1' : '';
                    ?>
                        <section class="chi-text-image py-5" <?php echo $bg_style ? 'style="' . $bg_style . '"' : ''; ?>>
                            <div class="container">
                                <div class="row align-items-center g-5">
                                    <div class="col-lg-7 <?php echo esc_attr( $order_class ); ?>">
                                        <?php if ( $top_title ) : ?>
                                            <p class="text-uppercase fw-bold fs-5 letter-spacing-2 mb-2"><?php echo esc_html( $top_title ); ?></p>
                                        <?php endif; ?>
                                        <?php if ( $title ) : ?>
                                            <h2 class="fw-normal mb-4 border-bottom"><?php echo wp_kses_post( $title ); ?></h2>
                                        <?php endif; ?>
                                        <?php if ( $content ) : ?>
                                            <div class="chi-content chi-section-content lead fw-medium "><?php echo wp_kses_post( $content ); ?></div>
                                        <?php endif; ?>
                                        <?php if ( $btn_text && $btn_link ) : ?>
                                            <a href="<?php echo esc_url( $btn_link ); ?>" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm mt-3">
                                                <?php if ( $btn_icon && $btn_icon_pos === 'left' ) : ?>
                                                    <i class="<?php echo esc_attr( $btn_icon ); ?> me-2"></i>
                                                <?php endif; ?>
                                                <?php echo esc_html( $btn_text ); ?>
                                                <?php if ( $btn_icon && $btn_icon_pos === 'right' ) : ?>
                                                    <i class="<?php echo esc_attr( $btn_icon ); ?> ms-2"></i>
                                                <?php endif; ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ( $image ) : ?>
                                        <div class="col-lg-5 <?php echo esc_attr( $img_order ); ?>">
                                            <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="img-fluid rounded">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>

                    <?php
                    // --- CONTACT INFO ---
                    elseif ( $layout === 'contact_info' ) :
                        $top_title   = get_sub_field( 'top_title' );
                        $title       = get_sub_field( 'title' );
                        $email       = get_sub_field( 'email' );
                        $phone       = get_sub_field( 'phone' );
                        $address     = get_sub_field( 'address' );
                        $address_url = get_sub_field( 'address_url' );
                        $cta_text    = get_sub_field( 'cta_text' );
                        $cta_url     = get_sub_field( 'cta_url' );
                        $bg_image    = get_sub_field( 'bg_image' );
                    ?>
                        <section class="chi-contact py-5" <?php if ( $bg_image ) : ?>style="--chi-contact-bg:url('<?php echo esc_url( $bg_image ); ?>');"<?php endif; ?>>
                            <div class="container position-relative py-5">
                                <div class="row justify-content-center">
                                    <div class="col-lg-8 text-center">
                                        <?php if ( $top_title ) : ?>
                                            <p class="text-uppercase fw-bold fs-5 letter-spacing-2 mb-2"><?php echo esc_html( $top_title ); ?></p>
                                        <?php endif; ?>
                                        <?php if ( $title ) : ?>
                                            <h2 class="fw-normal mb-3"><?php echo esc_html( $title ); ?></h2>
                                        <?php endif; ?>
                                        <hr class="mx-auto mb-5" style="max-width:500px;border-color:rgba(255,255,255,0.4);">
                                        <ul class="list-unstyled fs-5">
                                            <?php if ( $email ) : ?>
                                                <li class="mb-3"><i class="fas fa-envelope me-3"></i><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
                                            <?php endif; ?>
                                            <?php if ( $phone ) : ?>
                                                <li class="mb-3"><i class="fas fa-phone me-3"></i><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
                                            <?php endif; ?>
                                            <?php if ( $address ) : ?>
                                                <li class="mb-3"><i class="fas fa-map-marker-alt me-3"></i><?php if ( $address_url ) : ?><a href="<?php echo esc_url( $address_url ); ?>" target="_blank" rel="noopener"><?php endif; ?><?php echo nl2br( esc_html( $address ) ); ?><?php if ( $address_url ) : ?></a><?php endif; ?></li>
                                            <?php endif; ?>
                                        </ul>
                                        <?php if ( $cta_text && $cta_url ) : ?>
                                            <a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-secondary mt-3 px-4 py-4"><?php echo esc_html( $cta_text ); ?> <i class="fas fa-long-arrow-alt-right ms-2"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </section>

                    <?php
                    // --- TESTIMONIALS ---
                    elseif ( $layout === 'testimonials' ) :
                        $top_title   = get_sub_field( 'top_title' );
                        $title       = get_sub_field( 'title' );
                        $subtitle    = get_sub_field( 'subtitle' );
                        $reviews     = get_sub_field( 'reviews' );
                        $bg_color    = get_sub_field( 'bg_color' ) ?: '#eae3e0';
                    ?>
                        <section class="testimonials-section py-5" style="background-color:<?php echo esc_attr( $bg_color ); ?>;">
                            <div class="container">
                                <?php if ( $top_title ) : ?>
                                    <p class="text-uppercase fw-bold fs-5 letter-spacing-2 mb-4 text-center"><?php echo esc_html( $top_title ); ?></p>
                                <?php endif; ?>
                                <?php if ( $title ) : ?>
                                    <h2 class="fw-normal mb-2 border-bottom text-center"><?php echo wp_kses_post( $title ); ?></h2>
                                <?php endif; ?>
                                <?php if ( $subtitle ) : ?>
                                    <div class="lead mb-5 border-bottom fw-medium fs-5 text-center"><?php echo wp_kses_post( $subtitle ); ?></div>
                                <?php endif; ?>

                                <?php if ( ! empty( $reviews ) ) : ?>
                                    <div class="testimonial-swiper-wrap position-relative mt-4">
                                        <div class="swiper testimonial-swiper">
                                            <div class="swiper-wrapper">
                                                <?php foreach ( $reviews as $review ) : ?>
                                                    <div class="swiper-slide">
                                                        <div class="bg-white rounded-3 p-4 shadow-sm h-100 position-relative">
                                                            <img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" alt="Google" width="20" height="20" class="position-absolute" style="top:12px;right:12px;">
                                                            <div class="mb-2 text-warning">
                                                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                                            </div>
                                                            <?php if ( ! empty( $review['text'] ) ) : ?>
                                                                <div class="testimonial-text-wrap">
                                                                    <p class="testimonial-text testimonial-clamped"><?php echo esc_html( $review['text'] ); ?></p>
                                                                    <a href="#" class="testimonial-read-more small fw-medium">Leggi di più</a>
                                                                    <a href="#" class="testimonial-read-less small fw-medium d-none">Nascondi</a>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="d-flex align-items-center mt-3">
                                                                <?php if ( ! empty( $review['avatar'] ) ) : ?>
                                                                    <img src="<?php echo esc_url( $review['avatar'] ); ?>" alt="<?php echo esc_attr( $review['name'] ?? '' ); ?>" class="rounded-circle reviewer-avatar me-3">
                                                                <?php endif; ?>
                                                                <div>
                                                                    <?php if ( ! empty( $review['name'] ) ) : ?>
                                                                        <h6 class="reviewer-name mb-0"><?php echo esc_html( $review['name'] ); ?></h6>
                                                                    <?php endif; ?>
                                                                    <?php if ( ! empty( $review['date'] ) ) : ?>
                                                                        <small class="reviewer-date text-muted"><?php echo esc_html( $review['date'] ); ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="swiper-button-prev"></div>
                                        <div class="swiper-button-next"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                    <?php
                    // --- CATEGORIES GRID ---
                    elseif ( $layout === 'categories_grid' ) :
                        $title    = get_sub_field( 'title' );
                        $subtitle = get_sub_field( 'subtitle' );
                        $items    = get_sub_field( 'items' );
                        $bg_color = get_sub_field( 'bg_color' ) ?: '#ffffff';
                    ?>
                        <section class="chi-categories py-5" style="background-color:<?php echo esc_attr( $bg_color ); ?>;">
                            <div class="container">
                                <?php if ( $title ) : ?>
                                    <h2 class="text-uppercase fw-bold fs-5 letter-spacing-2 mb-4 text-center"><?php echo wp_kses_post( $title ); ?></h2>
                                <?php endif; ?>
                                <?php if ( $subtitle ) : ?>
                                    <h2 class="fw-normal mb-2 border-bottom text-center"><?php echo wp_kses_post( $subtitle ); ?></h2>
                                <?php endif; ?>

                                <?php if ( ! empty( $items ) ) : ?>
                                    <div class="row g-4">
                                        <?php foreach ( $items as $item ) :
                                            $cat_id = $item['category'] ?? null;
                                            if ( ! $cat_id ) { continue; }
                                            $term = get_term( $cat_id, 'product_cat' );
                                            if ( ! $term || is_wp_error( $term ) ) { continue; }
                                            $custom_image  = ! empty( $item['custom_image'] ) ? $item['custom_image'] : '';
                                            if ( $custom_image ) {
                                                $cat_image = $custom_image;
                                            } else {
                                                $thumbnail_id = get_term_meta( $cat_id, 'thumbnail_id', true );
                                                $cat_image    = $thumbnail_id ? wp_get_attachment_url( $thumbnail_id ) : '';
                                            }
                                            $cat_link      = get_term_link( $term );
                                            $custom_label  = ! empty( $item['custom_label'] ) ? $item['custom_label'] : $term->name;
                                        ?>
                                            <div class="col-6 col-md-4 col-lg-3">
                                                <div class="chi-cat-card h-100">
                                                    <div class="chi-cat-card__image">
                                                        <?php if ( $cat_image ) : ?>
                                                            <a href="<?php echo esc_url( $cat_link ); ?>">
                                                                <img src="<?php echo esc_url( $cat_image ); ?>" alt="<?php echo esc_attr( $custom_label ); ?>">
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                    <h5 class="chi-cat-card__title text-start"><?php echo esc_html( $custom_label ); ?></h5>
                                                    <a href="<?php echo esc_url( $cat_link ); ?>" class="chi-cat-card__link text-start">Tutti i prodotti <i class="fas fa-long-arrow-alt-right ms-1"></i></a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                    <?php
                    // --- FEATURES ---
                    elseif ( $layout === 'features' ) :
                        $items    = get_sub_field( 'items' );
                        $bg_color = get_sub_field( 'bg_color' ) ?: '#f8f9fa';
                    ?>
                        <section class="features-section py-5" style="background-color:<?php echo esc_attr( $bg_color ); ?>;">
                            <div class="container">
                                <?php if ( ! empty( $items ) ) : ?>
                                    <div class="row g-4">
                                        <?php foreach ( $items as $item ) : ?>
                                            <div class="col-md-4">
                                                <div class="text-start p-4 border-start">
                                                    <?php if ( ! empty( $item['icon'] ) ) : ?>
                                                        <img src="<?php echo esc_url( $item['icon'] ); ?>" alt="<?php echo esc_attr( $item['title'] ?? '' ); ?>" class="mb-3" width="70" height="70">
                                                    <?php endif; ?>
                                                    <?php if ( ! empty( $item['title'] ) ) : ?>
                                                        <h5 class="fw-bold mb-2 text-start"><?php echo esc_html( $item['title'] ); ?></h5>
                                                    <?php endif; ?>
                                                    <?php if ( ! empty( $item['description'] ) ) : ?>
                                                        <p class="text-secondary mb-0 text-start"><?php echo esc_html( $item['description'] ); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                    <?php
                    // --- GALLERY ---
                    elseif ( $layout === 'gallery' ) :
                        $gal_title    = get_sub_field( 'title' );
                        $gal_btn_text = get_sub_field( 'btn_text' );
                        $gal_btn_url  = get_sub_field( 'btn_url' );
                        $gal_images   = get_sub_field( 'images' );
                        if ( ! is_array( $gal_images ) ) { $gal_images = array(); }
                    ?>
                        <?php $gal_id = 'gallery-' . uniqid(); ?>
                        <section class="creations-section py-5 bg-light">
                            <?php if ( $gal_title || ( $gal_btn_text && $gal_btn_url ) ) : ?>
                                <div class="container-custom">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <?php if ( $gal_title ) : ?>
                                            <h2 class="h3 fw-bold mb-0"><?php echo wp_kses_post( $gal_title ); ?></h2>
                                        <?php endif; ?>
                                        <?php if ( $gal_btn_text && $gal_btn_url ) : ?>
                                            <a href="<?php echo esc_url( $gal_btn_url ); ?>" class="btn btn-primary text-white"><?php echo esc_html( $gal_btn_text ); ?> &rarr;</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="container-fluid px-0">
                                <div class="swiper creations-swiper">
                                    <div class="swiper-wrapper">
                                        <?php foreach ( $gal_images as $gal_idx => $gal_img ) :
                                            $gal_img_url = is_array( $gal_img ) ? ( $gal_img['url'] ?? '' ) : $gal_img;
                                        ?>
                                            <div class="swiper-slide">
                                                <a href="#" class="d-block ratio ratio-1x1 bg-white rounded-3 shadow-sm overflow-hidden h-100" data-bs-toggle="modal" data-bs-target="#<?php echo esc_attr( $gal_id ); ?>" data-bs-slide-to="<?php echo (int) $gal_idx; ?>">
                                                    <img src="<?php echo esc_url( $gal_img_url ); ?>" class="object-fit-contain w-100 h-100 p-2 transition-transform" alt="Creazione Albalù">
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Gallery Lightbox Modal -->
                        <div class="modal fade gallery-lightbox-modal" id="<?php echo esc_attr( $gal_id ); ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content bg-transparent border-0">
                                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" style="z-index:10;" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                    <div id="<?php echo esc_attr( $gal_id ); ?>-carousel" class="carousel slide" data-bs-ride="false">
                                        <div class="carousel-inner">
                                            <?php foreach ( $gal_images as $gal_idx => $gal_img ) :
                                                $gal_img_url = is_array( $gal_img ) ? ( $gal_img['url'] ?? '' ) : $gal_img;
                                            ?>
                                                <div class="carousel-item <?php echo $gal_idx === 0 ? 'active' : ''; ?>">
                                                    <div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
                                                        <img src="<?php echo esc_url( $gal_img_url ); ?>" class="img-fluid" style="max-height:80vh;object-fit:contain;" alt="Creazione Albalù">
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if ( count( $gal_images ) > 1 ) : ?>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo esc_attr( $gal_id ); ?>-carousel" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Precedente</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#<?php echo esc_attr( $gal_id ); ?>-carousel" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Successiva</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php
                    // --- CONTENUTO TESTUALE ---
                    elseif ( $layout === 'text_content' ) :
                        $content  = get_sub_field( 'content' );
                        $bg_color = get_sub_field( 'bg_color' ) ?: '#ffffff';
                    ?>
                        <section class="chi-text-content py-5" style="background-color:<?php echo esc_attr( $bg_color ); ?>;">
                            <div class="container">
                                <?php if ( $content ) : ?>
                                    <div class="chi-section-content"><?php echo wp_kses_post( $content ); ?></div>
                                <?php endif; ?>
                            </div>
                        </section>

                    <?php
                    // --- NEWSLETTER ---
                    elseif ( $layout === 'newsletter' ) :
                        $nl_subtitle = get_sub_field( 'subtitle' );
                        $nl_title    = get_sub_field( 'title' );
                        $nl_content  = get_sub_field( 'content' );
                        $nl_btn_text = get_sub_field( 'btn_text' );
                        $nl_btn_url  = get_sub_field( 'btn_url' );
                        $nl_bg_color = get_sub_field( 'bg_color' ) ?: '#9EA6A9';
                    ?>
                        <section class="newsletter-section py-5 text-white" style="background-color: <?php echo esc_attr( $nl_bg_color ); ?> !important;">
                            <div class="container my-5">
                                <div class="row">
                                    <div class="col-lg-8 text-start">
                                        <?php if ( $nl_subtitle ) : ?>
                                            <span class="h5 text-uppercase fw-medium ls-1 text-white"><?php echo esc_html( $nl_subtitle ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $nl_title ) : ?>
                                            <h2 class="h1 fw-medium my-3 text-white"><?php echo wp_kses_post( $nl_title ); ?></h2>
                                        <?php endif; ?>
                                        <?php if ( $nl_content ) : ?>
                                            <div class="h5 mb-4 text-white fw-medium"><?php echo wp_kses_post( $nl_content ); ?></div>
                                        <?php endif; ?>
                                        <?php if ( $nl_btn_text && $nl_btn_url ) : ?>
                                            <div class="d-flex justify-content-start">
                                                <a href="<?php echo esc_url( $nl_btn_url ); ?>" class="btn btn-primary px-4 py-2 text-white shadow-sm"><?php echo esc_html( $nl_btn_text ); ?> <i class="fas fa-arrow-right ms-2"></i></a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </section>

                    <?php
                    // --- HERO HOME PAGE ---
                    elseif ( $layout === 'hero_home_page' ) :
                        $hp_enabled          = get_sub_field( 'enabled' );
                        $hp_title            = get_sub_field( 'title' );
                        $hp_subtitle         = get_sub_field( 'subtitle' );
                        $hp_newsletter_title = get_sub_field( 'newsletter_title' );
                        $hp_newsletter_sub   = get_sub_field( 'newsletter_subtitle' );
                        $hp_btn_text         = get_sub_field( 'btn_text' );
                        $hp_btn_url          = get_sub_field( 'btn_url' );
                        $hp_fg_image         = get_sub_field( 'foreground_image' );
                        $hp_bg_image         = get_sub_field( 'background_image' );
                    ?>
                        <?php if ( $hp_enabled !== false ) : ?>
                        <div class="container mt-4">
                            <section class="hero-parallax d-flex align-items-center overflow-hidden" <?php if ( $hp_bg_image ) : ?>style="background-image: url('<?php echo esc_url( $hp_bg_image ); ?>');"<?php endif; ?>>
                                <div class="container-fluid px-4">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 py-5 text-white">
                                            <?php if ( $hp_title ) : ?>
                                                <h1 class="display-4 fw-bold mb-3"><?php echo wp_kses_post( $hp_title ); ?></h1>
                                            <?php endif; ?>
                                            <?php if ( $hp_subtitle ) : ?>
                                                <p class="lead mb-4"><?php echo wp_kses_post( $hp_subtitle ); ?></p>
                                            <?php endif; ?>
                                            <?php if ( $hp_newsletter_title || $hp_newsletter_sub ) : ?>
                                                <div class="mb-4">
                                                    <?php if ( $hp_newsletter_title ) : ?>
                                                        <p class="mb-2 fw-bold"><?php echo esc_html( $hp_newsletter_title ); ?></p>
                                                    <?php endif; ?>
                                                    <?php if ( $hp_newsletter_sub ) : ?>
                                                        <p class="mb-3 small"><?php echo esc_html( $hp_newsletter_sub ); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ( $hp_btn_text && $hp_btn_url ) : ?>
                                                <a href="<?php echo esc_url( $hp_btn_url ); ?>" class="btn btn-primary text-white rounded-0 px-4 py-3 text-uppercase fw-medium shadow-sm">
                                                    <?php echo esc_html( $hp_btn_text ); ?> <i class="fas fa-arrow-right ms-2"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-lg-6 text-center">
                                            <?php if ( $hp_fg_image ) : ?>
                                                <img src="<?php echo esc_url( $hp_fg_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $hp_title ) ); ?>" class="img-fluid">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <?php endif; ?>

                    <?php
                    // --- TRUST STRIP ---
                    elseif ( $layout === 'trust_strip' ) :
                        $ts_enabled = get_sub_field( 'enabled' );
                    ?>
                        <?php if ( $ts_enabled ) : ?>
                        <section class="bg-albalu-warm">
                           
                                <?php echo do_shortcode('[trustindex no-registration=google] '); ?>
                        
                        </section>
                        <?php endif; ?>

                    <?php
                    // --- MOST REQUESTED PRODUCTS ---
                    elseif ( $layout === 'most_requested_products' ) :
                        $mrp_enabled = get_sub_field( 'enabled' );
                    ?>
                        <?php if ( $mrp_enabled ) : ?>
                        <section class="products-section py-5 bg-white">
                            <div class="container">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
                                    <h2 class="mb-3 mb-md-0">Le <strong>bomboniere</strong> più richieste per <strong>ogni tipo di evento</strong></h2>
                                    <a href="/shop/" class="btn btn-primary px-4 py-2 shadow-sm">Scopri il catalogo <i class="fas fa-arrow-right ms-2"></i></a>
                                </div>
                                <div class="staging-product-grid">
                                    <?php echo do_shortcode('[bs-swiper-card-product order="ASC" orderby="popularity" posts="12"]'); ?>
                                </div>
                            </div>
                        </section>
                        <?php endif; ?>

                    <?php endif; ?>


                <?php endwhile; ?>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php
get_footer();
