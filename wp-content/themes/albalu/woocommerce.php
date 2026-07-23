
<?php

/**
 * The template for displaying all WooCommerce pages
 * Template Version: 6.3.1
 *
 * Overrides bootscore/woocommerce.php
 *
 * @package Albalu
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();
?>

  <div class="container-fluid px-0 d-none d-lg-block" style="background-color: #eae3e0">
    <div class="container">
    <?php woocommerce_breadcrumb(); ?>
</div>
  </div>

  <div class="container container px-auto text-center my-3 fw-medium">

									<p>+10000 Clienti Soddisfatti <img draggable="false" role="img" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/17.0.2/svg/2b50.svg"><img draggable="false" role="img" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/17.0.2/svg/2b50.svg"><img draggable="false" role="img" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/17.0.2/svg/2b50.svg"><img draggable="false" role="img" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/17.0.2/svg/2b50.svg"><img draggable="false" role="img" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/17.0.2/svg/2b50.svg"></p>


  </div>

  <?php if ( is_product_category() ) : ?>
    <?php
    $term = get_queried_object();
    $custom_name = get_field( 'nome_categoria_visualizzato', $term );
    $cat_title = ! empty( $custom_name ) ? $custom_name : single_term_title( '', false );
    $paged = max( 1, get_query_var( 'paged' ) );
    if ( $paged > 1 ) {
        $cat_title .= ' – Pagina ' . $paged;
    }
    ?>
    <section class="bg-white py-4 mb-4">
        <div class="container">
            <h1 class="fs-2 fw-medium mb-0"><?php echo esc_html( $cat_title ); ?></h1>
        </div>
    </section>
  <?php endif; ?>


  <div id="content" class="site-content <?= esc_attr(apply_filters('bootscore/class/container', 'container', 'woocommerce')); ?> <?= esc_attr(apply_filters('bootscore/class/content/spacer', 'pt-3 pb-5', 'woocommerce')); ?>">
    <div id="primary" class="content-area">

      <main id="main" class="site-main">

        <?php do_action( 'bootscore_after_primary_open', 'woocommerce' ); ?>

        <div class="row">
          <div class="<?= esc_attr(apply_filters('bootscore/class/main/col', 'col')); ?>">
            <?php
            if ( is_product_category() || is_shop() ) {
                // Subcategories separated from products
                if ( is_product_category() ) {
                    $parent_id = get_queried_object_id();
                } else {
                    $parent_id = 0;
                }
                $subcategories = get_terms( array(
                    'taxonomy'   => 'product_cat',
                    'parent'     => $parent_id,
                    'hide_empty' => true,
                    // Categorie che reindirizzano (301): mai mostrarle come card
                    // 2780 = bomboniere-comunione (reindirizza alla madre stessa)
                    'exclude'    => array( 2780 ),
                ) );

                if ( ! empty( $subcategories ) && ! is_wp_error( $subcategories ) ) {
                    ?>
                    <div class="row g-4 mb-5 products subcategories-section justify-content-center">
                        <?php
                        foreach ( $subcategories as $subcategory ) {
                            wc_get_template( 'content-product_cat.php', array(
                                'category' => $subcategory,
                            ) );
                        }
                        ?>
                    </div>
                    <?php
                }
            }
            woocommerce_content();
            ?>
          </div>
          <?php get_sidebar(); ?>
        </div><!-- row -->
      </main><!-- #main -->
    </div><!-- #primary -->
  </div><!-- #content -->
<?php
get_footer();