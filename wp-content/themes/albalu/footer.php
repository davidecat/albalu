
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
                        <a href="mailto:info@albalu.it" class="text-decoration-none"><!--email_off-->info@albalu.it<!--/email_off--></a>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="fas fa-phone-alt me-2"></i>
                        <a href="tel:+393533821875" class="text-decoration-none">353 382 1875</a>
                    </li>
                </ul>
                <h4 class="fw-medium mb-3 text-uppercase">Seguici su</h4>
                <div class="d-flex gap-2">
                    <a href="https://www.facebook.com/albalu.shop/" target="blank" class="footer-social-icon" aria-label="Facebook"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 320 512"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg></a>
                    <a href="https://www.instagram.com/albalu_shop/" target="blank" class="footer-social-icon" aria-label="Instagram"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 448 512"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg></a>
                    <a href="https://www.youtube.com/channel/UCqhEobP55vcPzc9Vt5we_zw" target="blank" class="footer-social-icon" aria-label="YouTube"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 576 512"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/></svg></a>
                    <a href="https://it.pinterest.com/albalushop/" target="blank" class="footer-social-icon" aria-label="Pinterest"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 384 512"><path d="M204 6.5C101.4 6.5 0 74.9 0 185.6 0 256 39.6 296 63.6 296c9.9 0 15.6-27.6 15.6-35.4 0-9.3-23.7-29.1-23.7-67.8 0-80.4 61.2-137.4 140.4-137.4 68.1 0 118.5 38.7 118.5 109.8 0 53.1-21.3 152.7-90.3 152.7-24.9 0-46.2-18-46.2-43.8 0-37.8 26.4-74.4 26.4-113.4 0-66.2-93.9-54.2-93.9 25.8 0 16.8 2.1 35.4 9.6 50.7-13.8 59.4-42 147.9-42 209.1 0 18.9 2.7 37.5 4.5 56.4 3.4 3.8 1.7 3.4 6.9 1.5 50.4-69 48.6-82.5 71.4-172.8 12.3 23.4 44.1 36 69.3 36 106.2 0 153.9-103.5 153.9-196.8C384 71.3 298.2 6.5 204 6.5z"/></svg></a>
                    <a href="https://vimeo.com/user135308061" target="blank" class="footer-social-icon" aria-label="Vimeo"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 448 512"><path d="M447.8 153.6c-2 43.6-32.4 103.3-91.4 179.1-60.9 79.2-112.4 118.8-154.6 118.8-26.1 0-48.2-24.1-66.3-72.3C100.3 250 85.3 174.3 56.2 174.3c-3.4 0-15.1 7.1-35.2 21.1L0 168.2c51.6-45.3 100.9-95.7 131.8-98.5 34.9-3.4 56.3 20.5 64.4 71.5 28.7 181.5 41.4 208.9 93.6 126.7 18.7-29.6 28.8-52.1 30.2-67.6 4.8-45.9-35.8-42.8-63.3-31 22-72.1 64.1-107.1 126.2-105.1 45.8 1.2 67.5 31.1 64.9 89.4z"/></svg></a>
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
                
               
               
                <a href="https://chat.whatsapp.com/BPTmcuoyXstFKrJI9fBWuJ?mode=gi_t" target="blank" class="btn btn-primary px-4 py-2 text-uppercase fw-bold shadow-sm mt-4" ><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 448 512" class="me-2" style="vertical-align:middle;"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>Canale Whatsapp</a>



            </div>
        </div>
    </div>

    <!-- Bottom Footer -->
    <div class="container footer-bottom">
        <div class="text-center mb-3 payment-icons">
            <img src="/wp-content/uploads/2024/06/pagamenti-placeholder-1.png" alt="Metodi di Pagamento" class="img-fluid">
        </div>
        <div class="text-center footer-copyright mb-2">
            Copyright &copy; <?= date('Y'); ?> – <strong>Alba Solving Srl</strong> – Contrada Parco snc, 70038 Terlizzi (BA) | Tel. 353 382 1875 – E-mail: <!--email_off-->info@albalu.it<!--/email_off--> | P. IVA 08393440725 – N. REA 623746
        </div>
        <div class="text-center footer-legal-links">
            <a href="/privacy-policy/" class="me-2">Privacy Policy</a>
            <a href="/cookie-policy/" class="me-2">Cookie Policy</a>
            <a href="#" class="iubenda-cs-preferences-link">Preferenze Cookie</a>
        </div>
    </div>
</footer>

<!-- To Top Button -->
<a href="#" class="btn btn-primary shadow to-top p-0 d-flex align-items-center justify-content-center position-fixed bottom-0 end-0 m-4 rounded-circle" style="width: 50px; height: 50px; z-index: 1000; background-color: #76a9b4; border-color: #76a9b4;" aria-label="Torna su">
    <i class="fas fa-chevron-up"></i>
</a>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
