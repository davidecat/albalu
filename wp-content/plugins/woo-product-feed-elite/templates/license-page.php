<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div class="adt-license-settings">
    <div class="adt-license-settings-container">
        <a href="https://adtribes.io/?utm_source=pfe&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank"><img class="logo" src="<?php echo esc_attr( ADT_PFE_IMAGES_URL . 'adt-logo.png' ); ?>" alt="<?php esc_attr_e( 'AdTribes', 'woo-product-feed-elite' ); ?>"></a>
        <h1 class="title">Licenses</h1>
        <p class="desc"><?php esc_html_e( 'Enter your license keys below to enjoy full access, plugin updates, and support.', 'woo-product-feed-elite' ); ?></p>
        
        <div class="adt-license-notification">
            <div class="notice">
                <p class="message"></p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'woo-product-feed-elite' ); ?></span></button>
            </div>
        </div>
        
        <div class="postbox license-box">
            <ul class="license-nav-tabs">
            <?php foreach ( $plugins_tabs as $plugins_tabs ) : ?>
                    <li class="<?php echo $plugins_tabs['active'] ? 'active' : ''; ?>">
                        <a href="<?php echo esc_url( $plugins_tabs['url'] ); ?>"><?php echo esc_html( $plugins_tabs['title'] ); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab">
                <?php do_action( 'adt_pfe_license_settings_page_content', $plugins_tabs ); ?>
            </div>
        </div>
    </div>
</div>
