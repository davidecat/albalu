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
            <section class="trust-strip py-2" style="background-color: #eae3e0;">
                <div class="container">
                    <div class="row align-items-center">
                        <!-- Left: Text -->
                        <div class="col-lg-7 mb-2 mb-lg-0 text-center text-lg-start">
                             <p class="mb-0" style="color: #3F494F; font-size: 1.05rem;">
                                Produciamo <strong>Bomboniere ed Articoli da regalo</strong> 100% artigianali e Made in Italy dal 1991
                             </p>
                        </div>
                        <!-- Right: Google Reviews -->
                        <div class="col-lg-5 text-center text-lg-end">
                            <div class="d-inline-flex align-items-center justify-content-lg-end justify-content-center">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 40px; height: 40px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48 48"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path></defs><clipPath id="b"><use xlink:href="#a"></use></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"></path><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"></path><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"></path><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"></path></svg>
                                </div>
                                <div class="text-start lh-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="fw-bold me-2" style="color: #3F494F;">Albalù Bomboniere</span>
                                        <span class="text-warning small" style="font-size: 0.8rem;">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        </span>
                                    </div>
                                    <span class="small text-muted fw-bold" style="font-size: 0.85rem;">+800 recensioni</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. Hero Section Parallax -->
            <div class="container mt-4">
                <section class="hero-parallax d-flex align-items-center overflow-hidden">
                    <div class="container-fluid px-4">
                        <div class="row align-items-center">
                            <div class="col-lg-6 py-5 text-white">
                                <h1 class="display-4 fw-bold mb-3">
                                    Bomboniere originali con kit<br>
                                    confezione in omaggio
                                </h1>
                                <p class="lead mb-4">
                                    Su Albalù puoi trovare bomboniere originali e utili, complete di kit confezione in omaggio!
                                </p>
                                
                                <div class="mb-4">
                                    <p class="mb-2 fw-bold">Iscriviti alla newsletter di Albalù!</p>
                                    <p class="mb-3 small">Ottieni sconti esclusivi: iscriviti alla newsletter</p>
                                </div>

                                <a href="#newsletter" class="btn btn-info text-white rounded-0 px-4 py-3 text-uppercase fw-bold shadow-sm" style="background-color: var(--color-cta-chiaro); border: none;">
                                    Clicca qui <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                            <div class="col-lg-6 text-center">
                                 <!-- Foreground Image -->
                                 <img src="https://albalu.b-cdn.net/wp-content/uploads/elementor/thumbs/confezioni-rinj44ahw9j48paqp3mtpbyjedbhrd85lujzwhirjg.webp" alt="Kit Confezione Omaggio" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- 2. Most Requested Products (WooCommerce Shortcode) -->
            <section class="products-section py-5 bg-white">
                <div class="container">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
                        <h2 class="mb-3 mb-md-0" >Le <strong>bomboniere</strong> più richieste per <strong>ogni tipo di evento</strong></h2>
                        <a href="/shop/" class="btn btn-primary px-4 py-2 shadow-sm" >Scopri il catalogo <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                    <div class="staging-product-grid">
                        <?php echo do_shortcode('[products limit="4" columns="4" orderby="popularity"]'); ?>
                    </div>
                </div>
            </section>

            <!-- 3. Categories Grid (Template 37159) -->
            <section class="categories-section py-5" style="background-color: #eae3e0">
                <div class="container">
                    <div class="text-center mb-5">
                        <span class="text-uppercase small fw-bold ls-1" style="color: var(--color-titoli);">Il nostro catalogo</span>
                        <h2 class="h1 mt-2" style="color: var(--color-titoli);">Articoli per ogni <strong>occasione e cerimonia</strong></h2>
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

            <!-- Dynamic Page Sections (Managed via ACF Flexible Content) -->
            <?php
            // Try to render sections from ACF Flexible Content first
            $page_sections_html = function_exists('albalu_render_page_sections') ? albalu_render_page_sections() : '';

            if ( ! empty( $page_sections_html ) ) {
                echo $page_sections_html;
            } else {
                // FALLBACK: If no sections are defined in ACF, show the default layout
                
                // 4. Promo Section (1)
                $albalu_promo_1 = function_exists('albalu_render_page_section_by_layout') ? albalu_render_page_section_by_layout('promo', 1, get_the_ID()) : '';
                if ( ! empty($albalu_promo_1) ) {
                    echo $albalu_promo_1;
                } else {
                    get_template_part('template-parts/sections/promo-section');
                }

                // 5. Newsletter Section
                if ( function_exists('albalu_render_newsletter_section') ) {
                    echo albalu_render_newsletter_section('', '', '', '', '', '#9EA6A9');
                }

                // 6. Promo Section (2)
                $albalu_promo_2 = function_exists('albalu_render_page_section_by_layout') ? albalu_render_page_section_by_layout('promo', 2, get_the_ID()) : '';
                if ( ! empty($albalu_promo_2) ) {
                    echo $albalu_promo_2;
                }

                // 7. Testimonials
                echo do_shortcode('[albalu_page_section layout="testimonials" index="1"]');

                // 8. Features Icons Section
                if ( function_exists('albalu_render_features_section') ) {
                    echo albalu_render_features_section();
                }

                // 9. Gallery Section
                if ( function_exists('albalu_render_gallery_section') ) {
                    echo albalu_render_gallery_section();
                }
            }
            ?>

        </main>
    </div>
</div>

<?php
get_footer();
