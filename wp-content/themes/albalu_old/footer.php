
<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Bootscore
 */

?>

<footer id="colophon" class="site-footer pt-5 pb-3" style="background-color: #EAE3E0;">
    
    <!-- Main Footer Widgets -->
    <div class="container-custom"> <!-- Used container-custom to match header width if needed, or just container -->
        <div class="row g-4">
            <!-- Col 1: Shop -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-4 text-uppercase" style="font-size: 1rem; color: #000;">Shop</h5>
                <ul class="list-unstyled footer-arrow-list">
                    <li><a href="/categoria-prodotto/nascita-e-battesimo/" class="text-decoration-none">Nascita e Battesimo</a></li>
                    <li><a href="/categoria-prodotto/comunione/" class="text-decoration-none">Comunione</a></li>
                    <li><a href="/categoria-prodotto/cresima/" class="text-decoration-none">Cresima</a></li>
                    <li><a href="/categoria-prodotto/compleanno/" class="text-decoration-none">Compleanno</a></li>
                    <li><a href="/categoria-prodotto/laurea/" class="text-decoration-none">Laurea</a></li>
                    <li><a href="/categoria-prodotto/matrimonio/" class="text-decoration-none">Matrimonio</a></li>
                    <li><a href="/categoria-prodotto/anniversario/" class="text-decoration-none">Anniversario</a></li>
                    <li><a href="/categoria-prodotto/complementi-darredo-e-regali/" class="text-decoration-none">Complementi D'Arredo e Regali</a></li>
                    <li><a href="/categoria-prodotto/bomboniere-per-tema/" class="text-decoration-none">Bomboniere per tema</a></li>
                    <li><a href="/categoria-prodotto/bomboniere-per-tipologia/" class="text-decoration-none">Bomboniere per Tipologia: Portafoto, Profumatori, Orologi</a></li>
                </ul>
            </div>

            <!-- Col 2: Guida all'acquisto -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-4 text-uppercase" style="font-size: 1rem; color: #000;">Guida all'acquisto</h5>
                <ul class="list-unstyled footer-arrow-list">
                    <li><a href="/chi-siamo/" class="text-decoration-none">Chi Siamo</a></li>
                    <li><a href="/notizie/" class="text-decoration-none">Notizie</a></li>
                    <li><a href="/assistenza-clienti/" class="text-decoration-none">Assistenza Clienti</a></li>
                    <li><a href="/faq/" class="text-decoration-none">Domande Frequenti- FAQ</a></li>
                    <li><a href="/pagamenti/" class="text-decoration-none">Pagamenti</a></li>
                    <li><a href="/spedizioni/" class="text-decoration-none">Spedizioni</a></li>
                    <li><a href="/resi-e-rimborsi/" class="text-decoration-none">Resi e rimborsi</a></li>
                    <li><a href="/i-nostri-sconti/" class="text-decoration-none">I nostri sconti</a></li>
                    <li><a href="/termini-e-condizioni/" class="text-decoration-none">Termini e condizioni</a></li>
                </ul>
            </div>

            <!-- Col 3: Contattaci & Social -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-4 text-uppercase" style="font-size: 1rem; color: #000;">Contattaci</h5>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2 text-dark d-flex align-items-center small">
                        <i class="fas fa-envelope me-2 text-secondary"></i> 
                        <a href="mailto:info@albalu.it" class="text-decoration-none text-dark fw-semibold">info@albalu.it</a>
                    </li>
                    <li class="mb-2 text-dark d-flex align-items-center small">
                        <i class="fas fa-phone-alt me-2 text-secondary"></i> 
                        <a href="tel:+393533821875" class="text-decoration-none text-dark fw-semibold">353 382 1875</a>
                    </li>
                </ul>
                <div class="mt-4">
                    <h5 class="fw-bold mb-3 text-uppercase" style="font-size: 1rem; color: #000;">Seguici su</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="footer-social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="footer-social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="footer-social-icon"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="footer-social-icon"><i class="fab fa-pinterest-p"></i></a>
                        <a href="#" class="footer-social-icon"><i class="fab fa-vimeo-v"></i></a>
                    </div>
                </div>
            </div>

            <!-- Col 4: Newsletter -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-4 text-uppercase" style="font-size: 1rem; color: #000;">Iscriviti alla newsletter</h5>
                <form class="position-relative">
                    <div class="mb-3">
                        <input type="email" class="form-control bg-white border-0 rounded-0 py-2 px-3" placeholder="Il tuo indirizzo email..." style="box-shadow: none;">
                    </div>
                    <div class="form-check mb-3">
                         <input class="form-check-input rounded-0" type="checkbox" value="" id="newsletterCheck" required>
                         <label class="form-check-label small text-muted" for="newsletterCheck" style="font-size: 0.8rem;">
                            Ho letto e accettato la <a href="/privacy-policy/" class="text-decoration-underline text-muted">privacy policy</a>
                         </label>
                    </div>
                    <button class="footer-newsletter-btn" type="submit">
                        Iscriviti
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Footer -->
    <div class="container-custom mt-5">
        <!-- <hr class="mb-4" style="border-color: #d0c4c0; opacity: 1;"> -->
        <div class="row align-items-center">
             <!-- Payment Methods -->
            <div class="col-12 text-center mb-3">
                <img src="/wp-content/uploads/2024/06/pagamenti-placeholder-1.png" alt="Metodi di Pagamento" class="img-fluid" style="max-height: 30px;">
            </div>
            <!-- Copyright & Links -->
            <div class="col-12 text-center">
                <p class="small text-dark mb-2 fw-semibold" style="font-size: 0.8rem;">
                    Copyright &copy; <?= date('Y'); ?> - Alba Solving Srl – Contrada Parco snc, 70038 Terlizzi (BA) | Tel. 353 382 1875 – E-mail: info@albalu.it | P. IVA 08393440725 – N. REA 623746
                </p>
                <ul class="list-inline small text-muted d-inline-block">
                    <!-- Note: reusing footer-arrow-list styles might be tricky for inline items, let's use custom spans -->
                    <li class="list-inline-item me-0"><span class="fw-bold me-1 text-dark">»</span><a href="/privacy-policy/" class="text-decoration-none text-dark fw-semibold">Privacy Policy</a></li>
                    <li class="list-inline-item me-0 ms-2"><span class="fw-bold me-1 text-dark">»</span><a href="/cookie-policy/" class="text-decoration-none text-dark fw-semibold">Cookie Policy</a></li>
                    <li class="list-inline-item me-0 ms-2"><span class="fw-bold me-1 text-dark">»</span><a href="#" class="text-decoration-none text-dark fw-semibold iubenda-cs-preferences-link">Preferenze Cookie</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<!-- To Top Button -->
<a href="#" class="btn btn-primary shadow to-top p-0 d-flex align-items-center justify-content-center position-fixed bottom-0 end-0 m-4 rounded-circle" style="width: 50px; height: 50px; z-index: 1000; background-color: #76a9b4; border-color: #76a9b4;">
    <i class="fas fa-chevron-up"></i>
</a>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
