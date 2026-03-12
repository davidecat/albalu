<?php
/**
 * Blog sections: Newsletter, Gallery, Featured Products
 * Used on archive (blog listing) and single post pages.
 * Data from ACF chi_siamo_sections of the given post ID.
 *
 * @param int $post_id Post/Page ID to get sections from (posts page for archive, current post for single).
 */
defined( 'ABSPATH' ) || exit;

$section_post_id = get_query_var( 'blog_sections_post_id' ) ?: get_the_ID();
if ( ! $section_post_id || ! function_exists( 'have_rows' ) || ! have_rows( 'chi_siamo_sections', $section_post_id ) ) {
    return;
}

while ( have_rows( 'chi_siamo_sections', $section_post_id ) ) : the_row();
    $layout = get_row_layout();

    // --- NEWSLETTER ---
    if ( $layout === 'newsletter' ) :
        $nl_subtitle   = get_sub_field( 'subtitle' );
        $nl_title      = get_sub_field( 'title' );
        $nl_content    = get_sub_field( 'content' );
        $nl_btn_text   = get_sub_field( 'btn_text' );
        $nl_btn_url    = get_sub_field( 'btn_url' );
        $nl_bg_color   = get_sub_field( 'bg_color' ) ?: '#9EA6A9';
        $nl_text_align = get_sub_field( 'text_align' ) ?: 'left';
        $nl_text_color = get_sub_field( 'text_color' ) ?: '#000000';
        $nl_align_class = ( $nl_text_align === 'left' ) ? 'text-start' : 'text-center';
        $nl_color_style = 'color:' . esc_attr( $nl_text_color ) . ';';
        ?>
        <section class="newsletter-section py-5" style="background-color: <?php echo esc_attr( $nl_bg_color ); ?> !important;">
            <div class="container my-5">
                <div class="row justify-content-<?php echo $nl_text_align === 'center' ? 'center' : 'start'; ?>">
                    <div class="col-lg-8 <?php echo esc_attr( $nl_align_class ); ?>" style="<?php echo $nl_color_style; ?>">
                        <?php if ( $nl_subtitle ) : ?>
                            <span class="h5 text-uppercase fw-medium ls-1"><?php echo esc_html( $nl_subtitle ); ?></span>
                        <?php endif; ?>
                        <?php if ( $nl_title ) : ?>
                            <h2 class="h1 fw-medium my-3"><?php echo wp_kses_post( $nl_title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $nl_content ) : ?>
                            <div class="h5 mb-4 fw-medium"><?php echo wp_kses_post( $nl_content ); ?></div>
                        <?php endif; ?>
                        <?php if ( $nl_btn_text && $nl_btn_url ) : ?>
                            <div class="d-flex justify-content-<?php echo $nl_text_align === 'center' ? 'center' : 'start'; ?>">
                                <a href="<?php echo esc_url( $nl_btn_url ); ?>" class="btn btn-primary px-4 py-2 text-white shadow-sm"><?php echo esc_html( $nl_btn_text ); ?> <i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
    // --- FEATURED (Prodotti Più Richiesti) ---
    elseif ( $layout === 'most_requested_products' ) :
        $mrp_title    = get_sub_field( 'title' );
        $mrp_btn_text = get_sub_field( 'btn_text' );
        $mrp_btn_url  = get_sub_field( 'btn_url' );
        $mrp_enabled  = get_sub_field( 'enabled' );
        $mrp_source   = get_sub_field( 'source' ) ?: 'categoria';
        $mrp_category = get_sub_field( 'category' );
        $mrp_products = get_sub_field( 'products' );
        ?>
        <?php if ( $mrp_enabled ) : ?>
        <section class="products-section py-5 bg-white">
            <div class="container">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
                    <?php if ( $mrp_title ) : ?>
                        <h2 class="mb-3 mb-md-0"><?php echo wp_kses_post( $mrp_title ); ?></h2>
                    <?php endif; ?>
                    <?php if ( $mrp_btn_text && $mrp_btn_url ) : ?>
                        <a href="<?php echo esc_url( $mrp_btn_url ); ?>" class="btn btn-primary px-4 py-2 shadow-sm"><?php echo esc_html( $mrp_btn_text ); ?> <i class="fas fa-arrow-right ms-2"></i></a>
                    <?php endif; ?>
                </div>
                <div class="staging-product-grid">
                    <?php
                    if ( $mrp_source === 'scelta_libera' && ! empty( $mrp_products ) ) :
                        $product_ids = implode( ',', array_map( 'intval', $mrp_products ) );
                        echo do_shortcode( '[bs-swiper-card-product id="' . $product_ids . '"]' );
                    elseif ( $mrp_source === 'categoria' && $mrp_category ) :
                        $term = get_term( $mrp_category, 'product_cat' );
                        if ( $term && ! is_wp_error( $term ) ) :
                            echo do_shortcode( '[bs-swiper-card-product category="' . esc_attr( $term->slug ) . '" orderby="date" order="DESC" posts="12"]' );
                        endif;
                    else :
                        echo do_shortcode( '[bs-swiper-card-product order="ASC" orderby="popularity" posts="12"]' );
                    endif;
                    ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

    <?php endif; ?>

<?php endwhile; ?>
