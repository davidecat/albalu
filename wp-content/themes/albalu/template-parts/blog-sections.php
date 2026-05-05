<?php
/**
 * Blog sections: Newsletter, Categories Grid, Features, Featured Products
 * Used on archive (blog listing) and single post pages.
 * Data from ACF chi_siamo_sections of the Posts page (same sections on both pages).
 */
defined( 'ABSPATH' ) || exit;

$section_post_id = get_query_var( 'blog_sections_post_id' );
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
    // --- CATEGORIES GRID (Posts page ACF se - tum admin me add karoge) ---
    elseif ( $layout === 'categories_grid' ) :
        $title    = get_sub_field( 'title' );
        $subtitle = get_sub_field( 'subtitle' );
        $items    = get_sub_field( 'items' );
        $bg_color = get_sub_field( 'bg_color' ) ?: '#ffffff';

        if ( empty( $title ) && empty( $items ) && function_exists( 'albalu_get_default_categories_grid' ) ) {
            $defaults = albalu_get_default_categories_grid();
            if ( ! empty( $defaults ) ) {
                $title    = $defaults['title'] ?? '';
                $subtitle = $defaults['subtitle'] ?? '';
                $items    = $defaults['items'] ?? array();
                $bg_color = $defaults['bg_color'] ?: '#ffffff';
            }
        }
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
                    <div class="row g-4 justify-content-center">
                        <?php foreach ( $items as $item ) :
                            $cat_id = $item['category'] ?? null;
                            if ( ! $cat_id ) { continue; }
                            $term = get_term( $cat_id, 'product_cat' );
                            if ( ! $term || is_wp_error( $term ) ) { continue; }
                            $custom_image = ! empty( $item['custom_image'] ) ? $item['custom_image'] : '';
                            $image_id = 0;
                            if ( $custom_image ) {
                                $image_id = is_array( $custom_image ) ? ( $custom_image['ID'] ?? 0 ) : (int) $custom_image;
                            }
                            if ( ! $image_id ) {
                                $image_id = (int) get_term_meta( $cat_id, 'thumbnail_id', true );
                            }
                            $cat_link      = get_term_link( $term );
                            $custom_label  = ! empty( $item['custom_label'] ) ? $item['custom_label'] : $term->name;
                        ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="chi-cat-card h-100">
                                    <div class="chi-cat-card__image">
                                        <?php if ( $image_id ) : ?>
                                            <a href="<?php echo esc_url( $cat_link ); ?>">
                                                <?php echo wp_get_attachment_image( $image_id, 'medium_large', false, array(
                                                    'alt'     => esc_attr( $custom_label ),
                                                    'loading' => 'lazy',
                                                ) ); ?>
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
    // --- FEATURES (Caratteristiche) ---
    elseif ( $layout === 'features' ) :
        $items = get_sub_field( 'items' );
        if ( empty( $items ) && function_exists( 'albalu_get_default_features' ) ) {
            $items = albalu_get_default_features();
        }
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
