<?php
/**
 * The header for our theme
 * Template Version: 6.3.1
 *
 * @package Bootscore
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?= esc_attr(get_bloginfo('charset')); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<div id="page" class="site">
  
  <!-- Skip Links -->
  <a class="skip-link visually-hidden-focusable" href="#primary"><?php esc_html_e( 'Skip to content', 'bootscore' ); ?></a>
  <a class="skip-link visually-hidden-focusable" href="#footer"><?php esc_html_e( 'Skip to footer', 'bootscore' ); ?></a>

  <!-- Top Bar Widget -->
  <?php if (is_active_sidebar('top-bar')) : ?>
    <?php dynamic_sidebar('top-bar'); ?>
  <?php endif; ?>

  <!-- 1. Top Bar (Beige Color) -->
  <div class="top-bar py-2 small fw-bold" style="background-color: #eae3e0; color: var(--color-titoli);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 text-center text-md-start">
                Bomboniere 100% Made in Italy
            </div>
            <div class="col-md-4 text-center fw-medium">
                SPEDIZIONE GRATUITA OLTRE 149€
            </div>
            <div class="col-md-4 text-center text-md-end">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <i class="fas fa-phone-alt me-1 text-secondary"></i> Hai bisogno di aiuto? <a href="/contatti/" class="text-decoration-underline text-dark">Contattaci</a>!
                    </li>
                </ul>
            </div>
        </div>
    </div>
  </div>

    <?php do_action( 'bootscore_before_masthead' ); ?>

  <!-- 2. Main Header (Logo, Search, Icons) -->
  <header id="masthead" class="<?= esc_attr(apply_filters('bootscore/class/header', 'site-header bg-white py-4')); ?>">
    
      <?php do_action( 'bootscore_after_masthead_open' ); ?>

  <div class="container">
        <div class="row align-items-center">
            <!-- Left: Search -->
            <div class="col-4 d-none d-lg-block">
                <form role="search" method="get" class="search-form position-relative" action="<?= esc_url(home_url('/')); ?>">
                    <div class="input-group">
                        <input type="search" class="form-control border-end-0 border ps-3 rounded-0" placeholder="Cerca..." value="<?= get_search_query(); ?>" name="s" style="box-shadow: none; border-color: #ddd;" />
                        <button class="btn btn-outline-secondary border-start-0 border bg-white text-dark rounded-0" type="submit" style="border-color: #ddd;"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>

            <?php do_action( 'bootscore_before_navbar_brand' ); ?>

            <!-- Center: Logo -->
            <div class="col-12 col-lg-4 text-center">
                 <a class="navbar-brand me-0" href="<?= esc_url(home_url()); ?>">
                    <img src="<?= esc_url(home_url()); ?>/wp-content/uploads/2024/05/albalu-logo-web.png" alt="Albalù Bomboniere Logo" class="img-fluid" style="max-height: 80px;">
                </a> 
            </div>

            <?php do_action( 'bootscore_after_navbar_brand' ); ?>

            <!-- Right: Icons (User, Cart) -->
            <div class="col-12 col-lg-4 text-center text-lg-end mt-3 mt-lg-0">
                <div class="d-flex align-items-center justify-content-center justify-content-lg-end gap-3">
                    
                    <!-- Top Nav Widget -->
                    <?php if (is_active_sidebar('top-nav')) : ?>
                        <?php dynamic_sidebar('top-nav'); ?>
                    <?php endif; ?>

                    <a href="<?= esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>" class="text-dark text-decoration-none" title="Account">
                        <i class="far fa-user fa-lg"></i>
                    </a>
                    <?php if (class_exists('WooCommerce')) : ?>
                        <a href="<?= esc_url(wc_get_cart_url()); ?>" class="text-dark position-relative d-flex align-items-center gap-2 text-decoration-none" title="Cart">
                            <div class="position-relative bg-white text-dark d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-shopping-cart fa-lg"></i>
                                <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-warning text-dark border border-light d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 0.7rem;">
                                    <?= WC()->cart->get_cart_contents_count(); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Toggler -->
                     <button class="btn btn-link text-dark d-lg-none p-0 ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-navbar" aria-controls="offcanvas-navbar">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    
                    <?php do_action( 'bootscore_after_nav_toggler' ); ?>

                </div>
            </div>
        </div>
        
        <!-- Mobile Search -->
        <div class="row d-lg-none mt-3">
            <div class="col-12">
                 <form role="search" method="get" class="search-form" action="<?= esc_url(home_url('/')); ?>">
                    <div class="input-group">
                         <input type="search" class="form-control rounded-pill" placeholder="Cerca prodotti..." value="<?= get_search_query(); ?>" name="s" />
                         <button class="btn btn-outline-secondary rounded-pill ms-1 border-0" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php
    if (class_exists('WooCommerce')) :
      get_template_part('template-parts/header/collapse-search', 'woocommerce');
    else :
      get_template_part('template-parts/header/collapse-search');
    endif;
    ?>

    <?php do_action( 'bootscore_before_masthead_close' ); ?>

  </header>
  
  <?php do_action( 'bootscore_after_masthead' ); ?>

  <!-- 3. Navigation Bar (Mega Menu) -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-top py-0 d-none d-lg-block position-relative menu-border">
    <div class="container justify-content-center">
        <?php
        $mega_menus = [];
        $locations = get_nav_menu_locations();
        $loc = isset($locations['main-menu']) ? 'main-menu' : (empty($locations) ? null : array_key_first($locations));
        if ($loc) {
            $menu = wp_get_nav_menu_object($locations[$loc]);
            if ($menu) {
                $items = wp_get_nav_menu_items($menu->term_id);
                if (!$items) { $items = []; }
                $children = [];
                foreach ($items as $it) {
                    $parent = (int)$it->menu_item_parent;
                    if (!isset($children[$parent])) $children[$parent] = [];
                    $children[$parent][] = $it;
                }
                foreach ($items as $it) {
                    if ((int)$it->menu_item_parent === 0) {
                        $title = function_exists('get_field') ? get_field('mega_title', $it->ID) : '';
                        $desc  = function_exists('get_field') ? get_field('mega_description', $it->ID) : '';
                        $img1  = function_exists('get_field') ? get_field('mega_img1', $it->ID) : '';
                        $img2  = function_exists('get_field') ? get_field('mega_img2', $it->ID) : '';
                        $sub_items = [];
                        if (function_exists('have_rows') && have_rows('items', $it->ID)) {
                            while (have_rows('items', $it->ID)) {
                                the_row();
                                $sub_items[] = [
                                    'label' => get_sub_field('label'),
                                    'link'  => get_sub_field('link')
                                ];
                            }
                        } else {
                            foreach ($children[$it->ID] ?? [] as $child) {
                                $sub_items[] = ['label' => $child->title, 'link' => $child->url];
                            }
                        }
                        $mega_menus[$it->title] = [
                            'link' => $it->url,
                            'title' => $title ? $title : $it->title,
                            'description' => $desc,
                            'items' => $sub_items,
                            'img1' => $img1,
                            'img2' => $img2
                        ];
                    }
                }
            }
        }
        ?>
        <ul class="navbar-nav justify-content-center w-100 gap-1 fw-medium">
            <?php foreach ($mega_menus as $name => $data) : ?>
            <li class="nav-item dropdown position-static">
                <a class="nav-link text-dark py-3 dropdown-toggle" href="<?= esc_url($data['link']); ?>" id="dropdown-<?= sanitize_title($name); ?>" role="button" aria-expanded="false">
                    <?= esc_html($name); ?>
                </a>
                <div class="dropdown-menu w-100 mt-0 border-0 p-0 rounded-0" aria-labelledby="dropdown-<?= sanitize_title($name); ?>" style="background-color: #eae3e0; border-top: 3px solid var(--color-cta-scuro) !important;">
                    <div class="container py-5">
                        <div class="row">
                            <!-- Left Column: Text & Links -->
                            <div class="col-lg-6">
                                <h6 class="text-uppercase fw-bold mb-3" style="letter-spacing: 1px; color: var(--color-titoli);"><?= esc_html($data['title']); ?></h6>
                                <?php if (!empty($data['description'])) : ?>
                                <p class="mb-4 small text-muted"><?= esc_html($data['description']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($data['items'])) : ?>
                                <ul class="list-unstyled mb-4">
                                    <?php foreach ($data['items'] as $item) : 
                                        $label = is_array($item) ? $item['label'] : $item;
                                        $url   = is_array($item) ? $item['link'] : '#';
                                    ?>
                                    <li class="mb-2">
                                        <a href="<?= esc_url($url); ?>" class="text-decoration-none text-dark small">
                                            <span class="me-2 text-muted">&raquo;</span> <?= esc_html($label); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                                
                                <a href="<?= esc_url($data['link']); ?>" class="text-decoration-none fw-bold small" style="color: var(--color-cta-chiaro);">
                                    Tutti i prodotti <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                            
                            <!-- Right Column: Images -->
                            <div class="col-lg-6">
                                <div class="row g-3">
                                    <?php if (!empty($data['img1'])) : ?>
                                    <div class="col-6">
                                        <div class="ratio ratio-1x1 bg-white">
                                            <img src="<?= esc_url($data['img1']); ?>" class="img-fluid object-fit-cover" alt="<?= esc_attr($name); ?> 1">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($data['img2'])) : ?>
                                    <div class="col-6">
                                        <div class="ratio ratio-1x1 bg-white">
                                            <img src="<?= esc_url($data['img2']); ?>" class="img-fluid object-fit-cover" alt="<?= esc_attr($name); ?> 2">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
  </nav>

  <!-- Offcanvas Menu (Mobile) -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas-navbar">
      <div class="offcanvas-header">
        <span class="h5 offcanvas-title fw-bold"></span>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body p-0">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'menu-mobile',
            'container'      => false,
            'menu_class'     => 'navbar-nav',
            'fallback_cb'    => '__return_false',
            'depth'          => 3,
            'walker'         => new bootstrap_5_wp_nav_menu_walker(),
        ));
        ?>

        <!-- Top Nav 2 Widget -->
        <?php if (is_active_sidebar('top-nav-2')) : ?>
          <?php dynamic_sidebar('top-nav-2'); ?>
        <?php endif; ?>

      </div>
  </div>

  <!-- Offcanvas User and Cart -->
    <?php
    if (class_exists('WooCommerce')) :
      get_template_part('template-parts/header/offcanvas', 'woocommerce');
    endif;
    ?>