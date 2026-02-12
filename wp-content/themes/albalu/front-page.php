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
                <div class="container-custom">
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

            <!-- 2. Hero Section -->
            <section class="hero-section py-5 mb-5" style="background-color: #F9F9F9; display: none;">
                <div class="container-custom">
                    <div class="row align-items-center">
                        <div class="col-lg-6 order-2 order-lg-1">
                            <span class="badge bg-white text-dark shadow-sm px-3 py-2 rounded-0 mb-3 text-uppercase border" style="font-size: 0.8rem; letter-spacing: 1px; border-color: #eee;">
                                <i class="fas fa-star text-warning me-1"></i> Bomboniere Artigianali
                            </span>
                            <h1 class="display-4 fw-bold mb-3" style="color: var(--color-titoli); font-family: 'Roboto', sans-serif;">
                                Le tue cerimonie,<br>
                                <span style="color: var(--color-cta-scuro);">i nostri dettagli unici.</span>
                            </h1>
                            <p class="lead text-secondary mb-4">
                                Dal 1991 realizziamo bomboniere artigianali Made in Italy per rendere indimenticabile ogni tuo evento speciale.
                            </p>
                            <div class="d-flex gap-3">
                                <a href="/shop/" class="btn btn-primary rounded-0 px-4 py-3 text-uppercase fw-bold shadow-sm" style="border: none; letter-spacing: 1px;">
                                    Vai allo Shop
                                </a>
                                <a href="/contatti/" class="btn btn-outline-secondary rounded-0 px-4 py-3 text-uppercase fw-bold" style="border: 2px solid #ddd; color: var(--color-titoli);">
                                    Richiedi Info
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-6 order-1 order-lg-2 mb-4 mb-lg-0 text-center">
                             <div class="position-relative">
                                <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/albalu-background-home-01.webp" alt="Albalù Hero" class="img-fluid position-relative z-index-1">
                             </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. Most Requested Products (WooCommerce Shortcode) -->
            <section class="products-section py-5 bg-white">
                <div class="container-custom">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
                        <h2 class="mb-3 mb-md-0" style="color: var(--color-titoli);">Le <strong>bomboniere</strong> più richieste per <strong>ogni tipo di evento</strong></h2>
                        <a href="/shop/" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm" style="border: none; letter-spacing: 1px;">Scopri il catalogo <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                    <div class="staging-product-grid">
                        <?php echo do_shortcode('[products limit="4" columns="4" orderby="popularity"]'); ?>
                    </div>
                </div>
            </section>

            <!-- 3. Categories Grid (Template 37159) -->
            <section class="categories-section py-5" style="background-color: #eae3e0">
                <div class="container-custom">
                    <div class="text-center mb-5">
                        <span class="text-uppercase small fw-bold ls-1" style="color: var(--color-titoli);">Il nostro catalogo</span>
                        <h2 class="h1 mt-2" style="color: var(--color-titoli);">Articoli per ogni <strong>occasione e cerimonia</strong></h2>
                    </div>
                    
                    <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 justify-content-center">
                        <?php
                        // Fetch top-level product categories
                        $args = array(
                            'taxonomy'   => 'product_cat',
                            'hide_empty' => true,
                            'parent'     => 0,
                            'orderby'    => 'menu_order', // Allow user to order via drag-n-drop in admin if needed, or 'name'
                            'order'      => 'ASC',
                            'number'     => 8,
                        );
                        $product_categories = get_terms($args);

                        if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
                            foreach ( $product_categories as $category ) {
                                // Get category image
                                $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
                                $image_url = wp_get_attachment_url( $thumbnail_id );
                                
                                // Fallback image if no category image is set
                                if ( ! $image_url ) {
                                    $image_url = wc_placeholder_img_src();
                                }
                                ?>
                                <div class="col">
                                    <div class="card border-0 h-100 category-card bg-white p-3">
                                        <div class="ratio ratio-1x1 overflow-hidden mb-3">
                                            <a href="<?php echo esc_url( get_term_link( $category ) ); ?>">
                                                <img src="<?php echo esc_url( $image_url ); ?>" class="img-fluid object-fit-cover w-100 h-100" alt="<?php echo esc_attr( $category->name ); ?>">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h5 class="h6 fw-bold text-uppercase mb-2">
                                                <a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="text-decoration-none text-dark">
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
                        ?>
                    </div>
                </div>
            </section>

            <!-- 4. Promo Section 1 (Albalù Store) -->
            <section class="promo-section-1 py-5">
                <div class="container-custom">
                    <div class="row align-items-center">
                        <div class="col-lg-7 order-2 order-lg-1">
                            <span class="text-uppercase text-muted small fw-bold ls-1">Albalù Store</span>
                            <h2 class="h1 fw-bold my-3">Rendiamo memorabile il <strong>tuo evento</strong></h2>
                            <p class="mb-4 text-secondary">
                                Affidati alle mani di artigiani esperti che producono le bomboniere per le tue occasioni speciali rigorosamente in Italia.
                            </p>
                            <p class="mb-4 text-secondary small">
                                Su Albalù puoi trovare bomboniere originali ed utili, oppure puoi scegliere i complementi d'arredo per la casa, regali per l'infanzia e articoli religiosi creati mescolando cura del artigianato con l'eleganza del design italiano.
                            </p>
                            <a href="/shop/" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm">Scopri i nostri prodotti</a>
                        </div>
                        <div class="col-lg-5 order-1 order-lg-2 mb-4 mb-lg-0">
                             <div class="ratio ratio-4x3 rounded-3 overflow-hidden">
                                <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/08.webp" class="object-fit-cover" alt="Promo Image">
                             </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 5. Newsletter Section (Template 37163) -->
            <section class="newsletter-section py-5 text-white" style="background-color: #9EA6A9 !important;">
                <div class="container-custom">
                    <div class="row">
                        <div class="col-lg-8 text-start">
                            <span class="text-uppercase small fw-bold ls-1 text-white-50">Rendiamo memorabile il tuo evento</span>
                            <h2 class="h1 fw-normal my-3 text-white">Iscriviti alla newsletter di Albalù</h2>
                            <p class="mb-4 text-white">
                                Iscriviti alla nostra newsletter e ricevi in esclusiva idee originali per le tue bomboniere, sconti riservati fino al 20%, anteprime sulle nuove collezioni e consigli personalizzati per ogni evento speciale. Non perdere le offerte dedicate agli iscritti!
                            </p>
                            <div class="d-flex justify-content-start">
                                <a href="#" class="btn btn-info px-4 py-2 text-white shadow-sm" style="background-color: #76A9B4; border: none; border-radius: 0;">Clicca qui <i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 6. About Section (Chi siamo) -->
            <section class="promo-section-2 py-5">
                <div class="container-custom">
                    <div class="row align-items-center">
                        <div class="col-lg-5 mb-4 mb-lg-0">
                             <div class="ratio ratio-1x1 rounded-3 overflow-hidden">
                                <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/Profumatore-in-Resina-effetto-Marmo-con-Diffusore-a-Tema-Albero-della-Vita-e-Applicazione-Centrale-in-Legno-Sagomato-Albalu-Bomboniere-745_610x610_crop_center.webp" class="object-fit-cover" alt="Chi Siamo">
                             </div>
                        </div>
                        <div class="col-lg-7">
                            <span class="text-uppercase text-muted small fw-bold ls-1">Chi siamo</span>
                            <h2 class="h1 fw-bold my-3">Siamo un gruppo di <strong>giovani pugliesi innamorati della vita</strong></h2>
                            <h3 class="h4 fw-normal mb-3 text-secondary">e dei suoi piaceri più semplici!</h3>
                            <p class="mb-4 text-secondary">
                                "Tutto nasce da una nostra esperienza di vita quotidiana: la difficoltà nello scegliere il regalo giusto per l'anniversario di una coppia di nostri amici!"
                            </p>
                            <p class="mb-4 text-secondary small">
                                La soluzione? Una sfera che in pochi click ti porta in un mondo dedicato alle ricorrenze: bomboniere, idee regalo, prima infanzia, arredamento e design e articoli religiosi! Un nome: Albalù!
                            </p>
                            <a href="/chi-siamo/" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm">Scopri di più</a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 7. Testimonials Section (Template 37165) -->
            <section class="testimonials-section py-5" style="background-color: #eae3e0">
                <div class="container-custom">
                    <div class="text-center mb-5">
                        <span class="text-uppercase text-muted small fw-bold ls-1">Dicono di noi</span>
                        <h2 class="h1 fw-bold mt-2">Le <strong>testimonianze</strong> dei nostri clienti</h2>
                        <p class="text-secondary mx-auto" style="max-width: 800px;">
                            Crediamo nella forza dei dettagli, nella ricerca dei materiali, nella creazione della linea e siamo felici di accompagnare il cliente dalla scelta del prodotto fino all'assistenza post vendita per soddisfare ogni sua esigenza.
                        </p>
                    </div>
                    
                    <div class="swiper testimonial-swiper pb-5">
                        <div class="swiper-wrapper">
                        <!-- Static Testimonials matching style -->
                         <?php 
                         $reviews = [
                             [
                                 'name' => 'Chiara Spanevello', 
                                 'date' => '19 Ottobre 2025', 
                                 'text' => 'Ciao, ho aspettato arrivasse la Cresima di mia figlia prima di scrivere una recensione, ovviamente positiva! Ordinate bomboniere con confetti e...', 
                                 'initials' => 'CS',
                                 'img' => 'https://albalu.displayer25.com/wp-content/uploads/2026/01/user-1.jpg' // Placeholder
                             ],
                             [
                                 'name' => 'FM March', 
                                 'date' => '17 Ottobre 2025', 
                                 'text' => 'Ho ordinato delle bomboniere per la laurea e sono rimasta davvero soddisfatta! Il servizio offerto è stato rapidissimo, la comunicazione...', 
                                 'initials' => 'FM',
                                 'img' => ''
                             ],
                             [
                                 'name' => 'Anna Di', 
                                 'date' => '15 Ottobre 2025', 
                                 'text' => 'Le bomboniere sono bellissime e di qualità. Vi ringrazio per la professionalità. A presto ❤️', 
                                 'initials' => 'AD',
                                 'img' => ''
                             ],
                             [
                                 'name' => 'Eliane Jabbour', 
                                 'date' => '13 Ottobre 2025', 
                                 'text' => 'Bomboniere bellissime e di ottima qualità, prezzo giusto. Consegna rapida e servizio clienti disponibile e attento a tutte le modifiche. Tutto...', 
                                 'initials' => 'EJ',
                                 'img' => 'https://albalu.displayer25.com/wp-content/uploads/2026/01/user-4.jpg' // Placeholder
                             ],
                             [
                                 'name' => 'Maria Rossi', 
                                 'date' => '10 Ottobre 2025', 
                                 'text' => 'Esperienza fantastica! Prodotti di alta qualità e spedizione velocissima. Consiglio vivamente a tutti per le vostre occasioni speciali.', 
                                 'initials' => 'MR',
                                 'img' => ''
                             ],
                             [
                                 'name' => 'Luca Bianchi', 
                                 'date' => '05 Ottobre 2025', 
                                 'text' => 'Gentilezza e professionalità. Le bomboniere sono arrivate perfette e confezionate con molta cura. Grazie mille!', 
                                 'initials' => 'LB',
                                 'img' => ''
                             ]
                         ];
                         foreach($reviews as $review) {
                         ?>
                        <div class="swiper-slide p-2">
                            <div class="testimonial-box position-relative bg-white p-4 rounded-3 shadow-sm mb-4">
                                <!-- Google G Logo -->
                                <div class="position-absolute top-0 end-0 mt-3 me-3">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48 48"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path></defs><clipPath id="b"><use xlink:href="#a"></use></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"></path><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"></path><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"></path><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"></path></svg>
                                    </div>
                                    <!-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z" fill="#4285F4"/><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z" fill="none"/></svg> -->
                                </div>

                                <div class="text-warning mb-2"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                <!-- <h5 class="fw-bold mb-2" style="font-size: 1rem;">Perfette e bellissime!!!</h5> -->
                                <p class="text-dark mb-2 testimonial-text" style="font-size: 0.95rem; line-height: 1.5; min-height: 70px;">
                                    <?php echo $review['text']; ?>
                                </p>
                                <a href="#" class="text-secondary small text-decoration-none d-inline-block">Leggi di più</a>
                            </div>
                            
                            <div class="d-flex align-items-center ms-3">
                                <?php if(!empty($review['img']) && false) { 
                                ?>
                                    <img src="<?php echo $review['img']; ?>" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php } else { 
                                    $colors = ['#BCAAA4', '#90A4AE', '#009688', '#795548'];
                                    $bg_color = $colors[array_rand($colors)];
                                ?>
                                    <!-- Use an image placeholder if no image, or initials -->
                                    <div class="rounded-circle overflow-hidden me-3" style="width: 50px; height: 50px;">
                                         <!-- Using a generic user placeholder image to match design better than initials box -->
                                         <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/user-placeholder.jpg" onerror="this.src='https://secure.gravatar.com/avatar/?s=50&d=mm&r=g'" alt="<?php echo $review['name']; ?>" class="img-fluid w-100 h-100 object-fit-cover">
                                    </div>
                                <?php } ?>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 1rem; color: #003057 !important;"><?php echo $review['name']; ?></h6>
                                    <small class="text-muted" style="font-size: 0.85rem;"><?php echo $review['date']; ?></small>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        </div>
                        <!-- Swiper Pagination/Navigation -->
                        <!-- <div class="swiper-pagination position-relative mt-4"></div> -->
                        <div class="swiper-button-prev" style="color: #000; background: #fff; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); left: 0;"></div>
                        <div class="swiper-button-next" style="color: #000; background: #fff; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); right: 0;"></div>
                    </div>
                </div>
            </section>

            <!-- 8. Features Icons Section (Template 37171) -->
            <section class="features-section py-5 bg-white">
                <div class="container-custom">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="h-100 ps-4 border-start" style="border-color: #EAE3E0 !important;">
                                <div class="mb-3">
                                    <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/albalu-customere-care-1.svg" alt="Assistenza Clienti" style="width: 70px; height: 70px;">
                                </div>
                                <h5 class="fw-bold h6 text-uppercase mb-2">Assistenza Clienti</h5>
                                <p class="small text-secondary mb-0">Siamo sempre disponibili per aiutarti a scegliere la bomboniera perfetta. Contattaci per un supporto rapido e personalizzato.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="h-100 ps-4 border-start" style="border-color: #EAE3E0 !important;">
                                <div class="mb-3">
                                    <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/albalu-quality-1.svg" alt="100% Made in Italy" style="width: 70px; height: 70px;">
                                </div>
                                <h5 class="fw-bold h6 text-uppercase mb-2">100% Made in Italy</h5>
                                <p class="small text-secondary mb-0">Le nostre bomboniere sono autentici prodotti artigianali italiani, realizzati con materiali di alta qualità e cura per i dettagli.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="h-100 ps-4 border-start" style="border-color: #EAE3E0 !important;">
                                <div class="mb-3">
                                    <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/albalu-delivery-1.svg" alt="Spedizione Gratuita" style="width: 70px; height: 70px;">
                                </div>
                                <h5 class="fw-bold h6 text-uppercase mb-2">Spedizione Gratuita da 149€</h5>
                                <p class="small text-secondary mb-0">Su ordini superiori a 149€, la spedizione è gratuita! Ricevi le tue bomboniere direttamente a casa, senza costi aggiuntivi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 9. Creations / Instagram -->
            <section class="creations-section py-5 bg-light">
                <div class="container-custom">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3 fw-bold mb-0">Alcune delle <strong>nostre creazioni</strong></h2>
                        <a href="#" class="btn btn-primary text-white">Esplora il catalogo &rarr;</a>
                    </div>
                    
                    <div class="swiper creations-swiper">
                        <div class="swiper-wrapper">
                        <?php 
                        $gallery = [
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/download_12_-PhotoRoom_610x610_crop_center.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/NewTemplate-PhotoRoom_3_610x610_crop_center.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/Profumatore-a-Forma-di-Cuore-in-Resina-Colorata-con-Applicazione-in-Legno-Ciuccio-Albalu-Bomboniere-906_610x610_crop_center.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/rosa-PhotoRoom_610x610_crop_center.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/Orologio-Quadrato-in-Legno-Colorato-a-Tema-Bimbi-con-Orsetto-Sole-e-Nuvolette-Celeste-Rosa-e-Panna-Albalu-Bomboniere-973_610x610_crop_center.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/Profumatore-Base-in-Vetro-con-tappo-in-Sughero-e-Applicazione-in-Legno-a-Tema-Vita-Albalu-Bomboniere-719-PhotoRoom_610x610_crop_center.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/Bomboniera-Quadretto-in-Legno-con-Piastrella-Sacra-in-Gres-Porcellanato-e-Cornice-Bianca-Rettangolare-Albalu-Bomboniere-606_40e81b2e-7681-4690-b118-4f5d948a0120_610x610_crop.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/Sessione-studio-016-2-PhotoRoom-PhotoRoom_1_610x610_crop_center.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/Bomboniera-Albero-della-Vita-con-Cuore-e-Applicazione-in-Porcellana-per-Battesimo-Albalu-Bomboniere-932_1_-PhotoRoom_610x610_crop_center.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/08.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/Bomboniera-Clip-portafoto-Base-a-Nuvoletta-e-Applicazione-in-Legno-Animaletti-Nascita-e-Battesimo-Albalu-Bomboniere-473_610x610_crop_center.webp',
                            'https://albalu.displayer25.com/wp-content/uploads/2026/01/04.webp'
                        ];
                        foreach($gallery as $img) { ?>
                        <div class="swiper-slide">
                            <div class="ratio ratio-1x1 bg-white rounded-3 shadow-sm overflow-hidden h-100">
                                <img src="<?php echo $img; ?>" class="object-fit-contain w-100 h-100 p-2 transition-transform" alt="Creazione Albalù">
                            </div>
                        </div>
                        <?php } ?>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>
</div>

<?php
get_footer();
