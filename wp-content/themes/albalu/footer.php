
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
    <div class="container"> <!-- Used container-custom to match header width if needed, or just container -->
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <h4 class="fw-medium mb-4 text-uppercase">Shop</h4>
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'footer_shop',
                    'container'      => false,
                    'menu_class'     => 'list-unstyled footer-arrow-list',
                    'fallback_cb'    => false,
                ) );
                ?>
            </div>

            <div class="col-lg-3 col-md-6">
                <h4 class="fw-medium mb-4 text-uppercase">Guida all'acquisto</h4>
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'footer_guide',
                    'container'      => false,
                    'menu_class'     => 'list-unstyled footer-arrow-list',
                    'fallback_cb'    => false,
                ) );
                ?>
            </div>

            <!-- Col 3: Contattaci & Social -->
            <div class="col-lg-3 col-md-6">
                <h4 class="fw-medium mb-4 text-uppercase">Contattaci</h4>
                <ul class="list-unstyled footer-contact-list mb-4">
                    <li class="d-flex align-items-center">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:info@albalu.it" class="text-decoration-none">info@albalu.it</a>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="fas fa-phone-alt me-2"></i>
                        <a href="tel:+393533821875" class="text-decoration-none">353 382 1875</a>
                    </li>
                </ul>
                <h4 class="fw-medium mb-3 text-uppercase">Seguici su</h4>
                <div class="d-flex gap-2">
                    <a href="https://www.facebook.com/albalu.shop/" target="blank" class="footer-social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/albalu_shop/" target="blank" class="footer-social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/channel/UCqhEobP55vcPzc9Vt5we_zw" target="blank" class="footer-social-icon"><i class="fab fa-youtube"></i></a>
                    <a href="https://it.pinterest.com/albalushop/" target="blank" class="footer-social-icon"><i class="fab fa-pinterest-p"></i></a>
                    <a href="https://vimeo.com/user135308061" target="blank" class="footer-social-icon"><i class="fab fa-vimeo-v"></i></a>
                </div>
            </div>

            <!-- Col 4: Newsletter -->
            <div class="col-lg-3 col-md-6">
                <h4 class="fw-medium mb-4 text-uppercase">Tieniti aggiornato!</h4>
                <!-- <form>
                    <div class="mb-3">
                        <input type="email" class="form-control bg-white border-0 rounded-0 py-2 px-3" placeholder="Il tuo indirizzo email..." style="box-shadow: none;">
                    </div>
                    <div class="form-check mb-3">
                         <input class="form-check-input rounded-0" type="checkbox" value="" id="newsletterCheck" required>
                         <label class="form-check-label small" for="newsletterCheck" style="font-size: 0.8rem; color: var(--color-testo);">
                            Ho letto e accettato la <a href="/privacy-policy/" class="text-decoration-underline" style="color: var(--color-testo);">privacy policy</a>
                         </label>
                    </div>
                    <button class="btn btn-primary text-white" type="submit">
                        Iscriviti
                    </button>
                </form> -->
                <a href="https://85f18f4f.sibforms.com/serve/MUIFAIYltb5x5Z7FR0AOy2HMcCL3SDU89lVZXRC29IRSot1g6Tzs4pmkKvNq84up1dK5RBMGmfmtZVSGy4lMhw0vf3GmDkVNALfCqLBTzGn5pliYkx16XYmtYRfr4EycF7YhU7sKD-4dfqubzOYWhiejuodx-LHFbsWVH4NmyffmpOSMBDUKlcig6QG3PuSDU8S1Rq9b768LRkyvCA==" target="blank" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm mt-1" >Iscriviti alla Newletter</a>
                
               
               
                <a href="https://chat.whatsapp.com/BPTmcuoyXstFKrJI9fBWuJ?mode=gi_t" target="blank" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm mt-4" ><i class="fab fa-whatsapp me-2"></i>Canale Whatsapp</a>



            </div>
        </div>
    </div>

    <!-- Bottom Footer -->
    <div class="container footer-bottom">
        <div class="text-center mb-3 payment-icons">
            <img src="/wp-content/uploads/2024/06/pagamenti-placeholder-1.png" alt="Metodi di Pagamento" class="img-fluid">
        </div>
        <div class="text-center footer-copyright mb-2">
            Copyright &copy; <?= date('Y'); ?> – <strong>Alba Solving Srl</strong> – Contrada Parco snc, 70038 Terlizzi (BA) | Tel. 353 382 1875 – E-mail: info@albalu.it | P. IVA 08393440725 – N. REA 623746
        </div>
        <div class="text-center footer-legal-links">
            <a href="/privacy-policy/" class="me-2">Privacy Policy</a>
            <a href="/cookie-policy/" class="me-2">Cookie Policy</a>
            <a href="#" class="iubenda-cs-preferences-link">Preferenze Cookie</a>
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
