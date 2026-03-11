
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


  <div id="content" class="site-content <?= esc_attr(apply_filters('bootscore/class/container', 'container', 'woocommerce')); ?> <?= esc_attr(apply_filters('bootscore/class/content/spacer', 'pt-3 pb-5', 'woocommerce')); ?>">
    <div id="primary" class="content-area">

      <main id="main" class="site-main">

        <?php do_action( 'bootscore_after_primary_open', 'woocommerce' ); ?>

        <div class="row">
          <div class="<?= esc_attr(apply_filters('bootscore/class/main/col', 'col')); ?>">
            <?php woocommerce_content(); ?>
          </div>
          <?php get_sidebar(); ?>
        </div><!-- row -->
      </main><!-- #main -->
    </div><!-- #primary -->
  </div><!-- #content -->
<?php
get_footer();