<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="row">
    <p class="text-large col-left mt-0 mb-2">
        <span class="text-bold"><?php esc_html_e( 'Version: ', 'woo-product-feed-elite' ); ?></span>
        <span><?php echo esc_html( WOOCOMMERCESEA_ELITE_PLUGIN_VERSION ); ?></span><br>
        <span><?php esc_html_e( 'You are currently using AdTribes Product Feed Elite.', 'woo-product-feed-elite' ); ?></span>
    </p>
    <p class="license-status text-large col-right mt-0">
        <span class="text-bold"><?php esc_html_e( 'License Status: ', 'woo-product-feed-elite' ); ?></span>
        <span class="<?php echo esc_attr( $adt_license_status_class_name ); ?>"><?php echo esc_html( $adt_license_status_i18n ); ?></span>
        <?php if ( 'expired' === $adt_license_status_value ) : ?>
            <span class="text-bold">
                <br><a href="<?php echo esc_url( $adt_license_upgrade_url ); ?>?utm_source=pfe&utm_medium=license&utm_campaign=expiredlicenserenewlink" target="_blank"><?php esc_html_e( 'Renew Now', 'woo-product-feed-elite' ); ?></a>
            </span>
        <?php endif; ?>
    </p>
</div>
<hr>
<br>
<div class="row">
    <div class="col-left">
        <form class="form-row form-group" id="adt-pfe-license-form" data-ajax-action="adt_activate_license">
            <input type="hidden" id="adt_pfe_activate_license_nonce" name="adt_pfe_activate_license_nonce" value="<?php echo esc_attr( wp_create_nonce( 'adt_pfe_activate_license_nonce' ) ); ?>" />

            <!-- License Key -->
            <div class="input-field">
                <label for="adt-license-key"><?php esc_html_e( 'License Key', 'woo-product-feed-elite' ); ?></label>
                <input type="password" id="adt-license-key" class="form-control license-key" placeholder="<?php esc_attr_e( 'Enter your license key', 'woo-product-feed-elite' ); ?>" value="<?php echo esc_attr( $adt_license_key ); ?>" />
            </div>

            <!-- Activation Email -->
            <div class="input-field">
                <label for="adt-license-email"><?php esc_html_e( 'Activation Email', 'woo-product-feed-elite' ); ?></label>
                <input type="email" id="adt-activation-email" class="form-control activation-email" placeholder="<?php esc_attr_e( 'Enter your email', 'woo-product-feed-elite' ); ?>" value="<?php echo esc_attr( $adt_activation_email ); ?>" />
            </div>
    
            <div class="input-button">
                <button class="button button-pink activate-license" id="activate-license"><?php esc_html_e( 'Activate License', 'woo-product-feed-elite' ); ?></button>
            </div>
        </form>

        <p class="text-small mt-2">
            <span class="learn-more"><?php esc_html_e( 'Can\'t find your license key?', 'woo-product-feed-elite' ); ?> <a href="https://adtribes.io/my-account/?utm_source=pfe&utm_medium=license&utm_campaign=lostlicenselogin" target="_blank"><?php esc_html_e( 'Login to your account.', 'woo-product-feed-elite' ); ?></a></span>
        </p>

    </div>
</div>
