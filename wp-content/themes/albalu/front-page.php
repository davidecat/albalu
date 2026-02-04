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

            <!-- 2. Hero Section -->
            <section class="hero-section py-5 mb-5" style="background-color: #F9F9F9; display: none;">
                <div class="container">
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
                                <a href="/shop/" class="btn btn-primary rounded-0 px-4 py-3 text-uppercase fw-bold shadow-sm" style="background-color: var(--color-cta-scuro); border: none; letter-spacing: 1px;">
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
                <div class="container">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
                        <h2 class="fw-bold-1 mb-3 mb-md-0" style="color: var(--color-titoli);">Le <strong>bomboniere</strong> più richieste per <strong>ogni tipo di evento</strong></h2>
                        <a href="/shop/" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm" style="background-color: var(--color-cta-scuro); border: none; letter-spacing: 1px;">Scopri il catalogo <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                    <div class="staging-product-grid">
                        <?php echo do_shortcode('[products limit="4" columns="4" orderby="popularity"]'); ?>
                    </div>
                </div>
            </section>

            <!-- 3. Categories Grid (Template 37159) -->
            <section class="categories-section py-5 bg-white">
                <div class="container">
                    <div class="text-center mb-5">
                        <span class="text-uppercase text-muted small fw-bold ls-1">Il nostro catalogo</span>
                        <h2 class="h1 fw-bold mt-2">Articoli per ogni <strong>occasione e cerimonia</strong></h2>
                    </div>
                    
                    <div class="row g-4 row-cols-2 row-cols-md-5 justify-content-center">
                        <?php
                        $cats = [
                            ['name' => 'Compleanno', 'img' => 'https://albalu.displayer25.com/wp-content/uploads/2026/01/Profumatore-in-Legno-Tema-Compleanno-Albalu-Bomboniere-473_610x610_crop_center-1-1.webp', 'link' => '/categoria-prodotto/compleanno/'],
                            ['name' => 'Laurea', 'img' => 'https://albalu.displayer25.com/wp-content/uploads/2026/01/Profumatore-in-Legno-Tema-Laurea-con-Applicazione-Gufetto-Albalu-Bomboniere-527_610x610_crop_center-1.webp', 'link' => '/categoria-prodotto/laurea/'],
                            ['name' => 'Matrimonio', 'img' => 'https://albalu.displayer25.com/wp-content/uploads/2026/01/Farfalla-con-Base-da-Appoggio-in-Cristallo-Colorato-Albalu-Bomboniere-169_610x610_crop_center-1.webp', 'link' => '/categoria-prodotto/matrimonio/'],
                            ['name' => 'Anniversario', 'img' => 'https://albalu.displayer25.com/wp-content/uploads/2026/01/Confettata-Barattolino-Vetro-Portaconfetti-e-Portaspezie-Albalu-Bomboniere-358-PhotoRoom_610x610_crop_center-1.webp', 'link' => '/categoria-prodotto/anniversario/'],
                            ['name' => 'Arredo e regali', 'img' => 'https://albalu.displayer25.com/wp-content/uploads/2026/01/Bomboniera-Battesimo-Portafoto-in-Legno-con-Applicazioni-Bimbi-a-Tema-Orsetti-e-Animali-Albalu-Bomboniere-227-PhotoRoom_610x610_crop_center-1.webp', 'link' => '/categoria-prodotto/complementi-arredo-e-regali/']
                        ];
                        
                        foreach ($cats as $cat) {
                            ?>
                            <div class="col">
                                <a href="<?php echo $cat['link']; ?>" class="d-block text-decoration-none text-dark category-card-link">
                                    <div class="card border-0 h-100 category-card bg-transparent">
                                        <div class="ratio ratio-1x1 overflow-hidden rounded-3 mb-3 bg-white shadow-sm">
                                            <img src="<?php echo $cat['img']; ?>" class="img-fluid object-fit-contain w-100 h-100 transition-transform p-3" alt="<?php echo $cat['name']; ?>">
                                        </div>
                                        <h5 class="h6 fw-bold text-uppercase mb-1 text-center"><?php echo $cat['name']; ?></h5>
                                        <div class="text-center">
                                            <span class="small text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Vedi <i class="fas fa-chevron-right ms-1" style="font-size: 0.7rem;"></i></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- 4. Promo Section 1 (Albalù Store) -->
            <section class="promo-section-1 py-5">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-5 order-2 order-lg-1">
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
                        <div class="col-lg-7 order-1 order-lg-2 mb-4 mb-lg-0">
                             <div class="ratio ratio-4x3 rounded-3 overflow-hidden">
                                <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/08.webp" class="object-fit-cover" alt="Promo Image">
                             </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 5. Newsletter Section (Template 37163) -->
            <section class="newsletter-section py-5 text-white" style="background-color: var(--color-elementi) !important;">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-8">
                            <span class="text-uppercase small fw-bold ls-1 text-white-50">Rendiamo memorabile il tuo evento</span>
                            <h2 class="h1 fw-bold my-3 text-white">Iscriviti alla newsletter di Albalù</h2>
                            <p class="mb-4 text-white-50">
                                Iscriviti alla nostra newsletter e ricevi in esclusiva idee originali per le tue bomboniere, sconti riservati fino al 30%, anteprime sulle nuove collezioni e consigli personalizzati per ogni evento speciale. Non perdere le offerte dedicate agli iscritti!
                            </p>
                            <div class="d-flex justify-content-center">
                                <a href="#" class="btn btn-light px-4 py-2 text-uppercase fw-bold" style="color: var(--color-cta-scuro);">Iscriviti</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 6. About Section (Chi siamo) -->
            <section class="promo-section-2 py-5">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                             <div class="ratio ratio-1x1 rounded-3 overflow-hidden">
                                <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/Profumatore-in-Resina-effetto-Marmo-con-Diffusore-a-Tema-Albero-della-Vita-e-Applicazione-Centrale-in-Legno-Sagomato-Albalu-Bomboniere-745_610x610_crop_center.webp" class="object-fit-cover" alt="Chi Siamo">
                             </div>
                        </div>
                        <div class="col-lg-6">
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
            <section class="testimonials-section py-5 bg-light">
                <div class="container">
                    <div class="text-center mb-5">
                        <span class="text-uppercase text-muted small fw-bold ls-1">Dicono di noi</span>
                        <h2 class="h1 fw-bold mt-2">Le <strong>testimonianze</strong> dei nostri clienti</h2>
                        <p class="text-secondary mx-auto" style="max-width: 800px;">
                            Crediamo nella forza dei dettagli, nella ricerca dei materiali, nella creazione della linea e siamo felici di accompagnare il cliente dalla scelta del prodotto fino all'assistenza post vendita per soddisfare ogni sua esigenza.
                        </p>
                    </div>
                    
                    <div class="row g-4">
                        <!-- Static Testimonials matching style -->
                         <?php 
                         $reviews = [
                             ['name' => 'Flo Mochi', 'date' => '17 Ottobre 2023', 'text' => 'Ho ordinato delle bomboniere per la laurea e sono rimasta davvero soddisfatta! Il servizio offerto è stato rapidissimo...', 'initials' => 'FM'],
                             ['name' => 'Anna Si', 'date' => '15 Ottobre 2023', 'text' => 'Le bomboniere sono bellissime, di ottima qualità e biglietto per la professionalità. Super...', 'initials' => 'AS'],
                             ['name' => 'Elena Taldeco', 'date' => '12 Ottobre 2023', 'text' => 'Bomboniere bellissime e di ottima qualità, prezzo giusto. Consegna rapida e servizio clienti disponibile...', 'initials' => 'ET']
                         ];
                         foreach($reviews as $review) {
                         ?>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm p-4 rounded-3">
                                <div class="text-warning mb-3"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                <p class="text-secondary mb-4 fst-italic">"<?php echo $review['text']; ?>"</p>
                                <div class="d-flex align-items-center mt-auto">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 45px; height: 45px; background-color: var(--color-cta-scuro); font-size: 1.2rem;"><?php echo $review['initials']; ?></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo $review['name']; ?></h6>
                                        <small class="text-muted"><?php echo $review['date']; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </section>

            <!-- 8. Features Icons Section (Template 37171) -->
            <section class="features-section py-5 bg-white">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="me-3 text-center" style="width: 60px;">
                                    <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/albalu-customere-care-1.svg" alt="Assistenza Clienti" style="width: 50px; height: 50px;">
                                </div>
                                <div>
                                    <h5 class="fw-bold h6 text-uppercase mb-2">Assistenza Clienti</h5>
                                    <p class="small text-secondary mb-0">Siamo sempre disponibili per aiutarti a scegliere la bomboniera perfetta. Contattaci per un supporto rapido e personalizzato.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="me-3 text-center" style="width: 60px;">
                                    <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/albalu-quality-1.svg" alt="100% Made in Italy" style="width: 50px; height: 50px;">
                                </div>
                                <div>
                                    <h5 class="fw-bold h6 text-uppercase mb-2">100% Made in Italy</h5>
                                    <p class="small text-secondary mb-0">Le nostre bomboniere sono autentici prodotti artigianali italiani, realizzati con materiali di alta qualità e cura per i dettagli.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="me-3 text-center" style="width: 60px;">
                                    <img src="https://albalu.displayer25.com/wp-content/uploads/2026/01/albalu-delivery-1.svg" alt="Spedizione Gratuita" style="width: 50px; height: 50px;">
                                </div>
                                <div>
                                    <h5 class="fw-bold h6 text-uppercase mb-2">Spedizione Gratuita</h5>
                                    <p class="small text-secondary mb-0">Spedizione gratuita per ordini superiori a 149€. Ordina in tutta facilità e ricevi direttamente a casa.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 9. Creations / Instagram -->
            <section class="creations-section py-5 bg-light">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3 fw-bold mb-0">Alcune delle <strong>nostre creazioni</strong></h2>
                        <a href="#" class="btn btn-outline-secondary btn-sm">Vedi tutto</a>
                    </div>
                    <div class="row g-3 row-cols-2 row-cols-md-4 row-cols-lg-6">
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
                        <div class="col">
                            <div class="ratio ratio-1x1 bg-white rounded-3 shadow-sm overflow-hidden h-100">
                                <img src="<?php echo $img; ?>" class="object-fit-contain w-100 h-100 p-2 transition-transform" alt="Creazione Albalù">
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </section>

        </main>
    </div>
</div>

<?php
get_footer();
