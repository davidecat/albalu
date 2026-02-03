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

  <!-- 1. Top Bar (Beige Color) -->
  <div class="top-bar py-2 small fw-bold" style="background-color: var(--color-sfondi-chiaro); color: var(--color-titoli);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 text-center text-md-start">
                Bomboniere 100% Made in Italy
            </div>
            <div class="col-md-4 text-center">
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

  <!-- 2. Main Header (Logo, Search, Icons) -->
  <header id="masthead" class="site-header bg-white py-4">
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

            <!-- Center: Logo -->
            <div class="col-12 col-lg-4 text-center">
                 <a class="navbar-brand me-0" href="<?= esc_url(home_url()); ?>">
                    <img src="<?= esc_url(home_url()); ?>/wp-content/uploads/2024/05/albalu-logo-web.png" alt="Albalù Bomboniere Logo" class="img-fluid" style="max-height: 80px;">
                </a> 
            </div>

            <!-- Right: Icons (User, Cart) -->
            <div class="col-12 col-lg-4 text-center text-lg-end mt-3 mt-lg-0">
                <div class="d-flex align-items-center justify-content-center justify-content-lg-end gap-3">
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
  </header>

  <!-- 3. Navigation Bar (Mega Menu) -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-0 d-none d-lg-block position-relative menu-border">
    <div class="container justify-content-center">
        <?php
        $mega_menus = [
            'Nascita e Battesimo' => [
                'link' => '/categoria-prodotto/nascita-e-battesimo/',
                'title' => 'Nascita e Battesimo',
                'items' => ['Bomboniere Nascita', 'Bomboniere Battesimo', 'Confettate e Segnaposto'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_nascita-01.webp',
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_nascita-02.webp'
            ],
            'Comunione' => [
                'link' => '/categoria-prodotto/comunione/',
                'title' => 'Comunione',
                'items' => ['Bomboniere Comunione', 'Bomboniere Comunione Bambino', 'Confettate e Segnaposto'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_comunione-01.webp',
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_comunione-02.webp'
            ],
            'Cresima' => [
                'link' => '/categoria-prodotto/cresima/',
                'title' => 'Cresima',
                'items' => ['Bomboniere Cresima', 'Confettate e Segnaposto'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_cresima-01.webp',
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_cresima-02.webp'
            ],
            'Compleanno' => [
                'link' => '/categoria-prodotto/compleanno/',
                'title' => 'Compleanno',
                'items' => ['Bomboniere Compleanno', 'Confettate e Segnaposto'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_compleanno-01.webp',
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_compleanno-02.webp'
            ],
            'Laurea' => [
                'link' => '/categoria-prodotto/laurea/',
                'title' => 'Laurea',
                'items' => ['Bomboniere Laurea', 'Confettate e Segnaposto'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_laurea-01.webp',
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_laurea-02.webp'
            ],
            'Matrimonio' => [
                'link' => '/categoria-prodotto/matrimonio/',
                'title' => 'Matrimonio',
                'items' => ['Bomboniere Matrimonio', 'Confettate e Segnaposto'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_matrimonio-01.webp',
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_matrimonio-02.webp'
            ],
            'Anniversario' => [
                'link' => '/categoria-prodotto/anniversario/',
                'title' => 'Anniversario',
                'items' => ['Bomboniere Anniversario', 'Confettate e Segnaposto'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_anniversario-01.webp',
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_anniversario-02.webp'
            ],
            'Complementi D\'Arredo e Regali' => [
                'link' => '/categoria-prodotto/complementi-darredo-e-regali/',
                'title' => 'Complementi D\'Arredo e Regali',
                'items' => ['Orologi da Parete', 'Quadri', 'Regali', 'Portafoto Argentati', 'Portafoto Regalo Infanzia', 'Prodotti Padre Pio'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_complementi-arredo-e-regali-01.webp',
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2024/09/megamenu_complementi-arredo-e-regali-02.webp'
            ],
            'Tema' => [
                'link' => '/categoria-prodotto/bomboniere-per-tema/',
                'title' => 'Bomboniere per tema',
                'items' => ['Bomboniere tema amore', 'Bomboniere tema animali', 'Bomboniere tema calcio', 'Bomboniere tema fiori', 'Bomboniere tema musica', 'Bomboniere tema mare', 'Bomboniere tema viaggio', 'Bomboniere Albero della Vita'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2025/10/megamenu_bomboniere-per-tema-01.jpg',
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2025/10/megamenu_bomboniere-per-tema-02.jpg'
            ],
            'Tipologia' => [
                'link' => '/categoria-prodotto/bomboniere-per-tipologia/',
                'title' => 'Bomboniere per tipologia',
                'items' => ['Bomboniere Segnalibri', 'Bomboniere Portafoto', 'Bomboniere Profumatori'],
                'img1' => 'https://albalu.displayer25.com/wp-content/uploads/2026/01/megamenu_bomboniere-per-tema-01.jpg', // Reused per dump
                'img2' => 'https://albalu.displayer25.com/wp-content/uploads/2026/01/megamenu_bomboniere-per-tema-02.jpg'  // Reused per dump
            ],
        ];
        ?>
        <ul class="navbar-nav justify-content-center w-100 gap-3">
            <?php foreach ($mega_menus as $name => $data) : ?>
            <li class="nav-item dropdown position-static">
                <a class="nav-link text-dark py-3 dropdown-toggle" href="<?= esc_url($data['link']); ?>" id="dropdown-<?= sanitize_title($name); ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 1rem; font-weight: 600;">
                    <?= esc_html($name); ?>
                </a>
                <div class="dropdown-menu w-100 mt-0 shadow-lg border-0 p-0" aria-labelledby="dropdown-<?= sanitize_title($name); ?>" style="border-top: 3px solid var(--color-cta-scuro) !important;">
                    <div class="container py-4">
                        <div class="row">
                            <!-- Col 1: Links -->
                            <div class="col-lg-3 border-end">
                                <h6 class="text-uppercase fw-bold mb-3" style="font-size: 1.1rem; color: var(--color-titoli);"><?= esc_html($data['title']); ?></h6>
                                <p class="small text-muted mb-3">Scopri la nostra selezione esclusiva.</p>
                                <hr class="mb-3" style="width: 50px; border-top: 2px solid var(--color-cta-scuro); opacity: 1;">
                                <ul class="list-unstyled mb-4">
                                    <?php foreach ($data['items'] as $item) : ?>
                                    <li class="mb-2">
                                        <a href="#" class="text-decoration-none text-secondary hover-primary transition-colors">
                                            <i class="fas fa-angle-right me-2 small text-muted"></i> <?= esc_html($item); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="<?= esc_url($data['link']); ?>" class="btn btn-primary rounded-0 btn-sm px-4 text-uppercase fw-bold">Vedi tutto</a>
                            </div>
                            <!-- Col 2: Image 1 -->
                            <div class="col-lg-4 offset-lg-1">
                                <div class="ratio ratio-1x1 overflow-hidden">
                                    <img src="<?= esc_url($data['img1']); ?>" class="img-fluid object-fit-cover w-100 h-100" alt="<?= esc_attr($name); ?> 1">
                                </div>
                            </div>
                            <!-- Col 3: Image 2 -->
                            <div class="col-lg-4">
                                <div class="ratio ratio-1x1 overflow-hidden">
                                    <img src="<?= esc_url($data['img2']); ?>" class="img-fluid object-fit-cover w-100 h-100" alt="<?= esc_attr($name); ?> 2">
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
      <div class="offcanvas-header bg-light border-bottom">
        <span class="h5 offcanvas-title fw-bold">MENU</span>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body p-0">
        <ul class="list-group list-group-flush">
             <?php foreach ($mega_menus as $name => $data) : ?>
            <li class="list-group-item border-0 border-bottom">
                <a class="text-decoration-none text-dark d-block py-2 fw-bold text-uppercase" href="<?= esc_url($data['link']); ?>"><?= esc_html($name); ?> <i class="fas fa-chevron-right float-end text-muted small mt-1"></i></a>
            </li>
            <?php endforeach; ?>
        </ul>
      </div>
  </div>
