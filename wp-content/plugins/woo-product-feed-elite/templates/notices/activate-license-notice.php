<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="notice notice-info is-dismissible adt-pfe-activate-license-notice">
    <p><strong>AdTribes Product Feed Elite License</strong></p>
    <p><?php esc_html_e( 'Enter your AdTribes Product Feed Elite license to fully activate the plugin, gain access to automatic updates, technical support, and premium features.', 'woo-product-feed-elite' ); ?></p>
    <form class="form-row form-group" id="adt-pfe-license-dialog-form">
        <input type="hidden" id="adt_pfe_activate_license_nonce" name="adt_pfe_activate_license_nonce" value="<?php echo esc_attr( wp_create_nonce( 'adt_pfe_activate_license_nonce' ) ); ?>" />

        <!-- License Key -->
        <div class="input-field">
            <label for="adt-license-key"><?php esc_html_e( 'License Key', 'woo-product-feed-elite' ); ?></label>
            <input type="password" id="adt-license-key" class="form-control license-key" placeholder="<?php esc_attr_e( 'Enter your license key', 'woo-product-feed-elite' ); ?>" value="<?php echo esc_attr( isset( $adt_license_key ) ? $adt_license_key : '' ); ?>" />
        </div>

        <!-- Activation Email -->
        <div class="input-field">
            <label for="adt-license-email"><?php esc_html_e( 'Activation Email', 'woo-product-feed-elite' ); ?></label>
            <input type="email" id="adt-activation-email" class="form-control activation-email" placeholder="<?php esc_attr_e( 'Enter your email', 'woo-product-feed-elite' ); ?>" value="<?php echo esc_attr( isset( $adt_activation_email ) ? $adt_activation_email : '' ); ?>" />
        </div>

        <div class="input-button">
            <button class="button button-pink activate-license" id="activate-license"><?php esc_html_e( 'Activate License', 'woo-product-feed-elite' ); ?></button>
        </div>
    </form>

    <p class="text-small mt-2">
        <span class="learn-more"><?php esc_html_e( 'Can\'t find your license key?', 'woo-product-feed-elite' ); ?> <a href="https://adtribes.io/my-account/?utm_source=pfe&utm_medium=licensedrm&utm_campaign=nolicensedialog" target="_blank"><?php esc_html_e( 'Login to your account.', 'woo-product-feed-elite' ); ?></a></span>
    </p>
</div>

<script type="text/javascript">
    jQuery('.adt-pfe-activate-license-notice').on('click', '.notice-dismiss', function () {
        jQuery.post(window.ajaxurl, { action: 'adt_slmw_dismiss_activate_notice' });
    });

    // On click of the activate button, get the values from the form and send them to the license page located at admin.php?page=adt_license_settings_page via a query string.
    jQuery('#activate-license').on('click', function (e) {
        e.preventDefault();

        var licenseKey = jQuery('#adt-license-key').val();
        var activationEmail = jQuery('#adt-activation-email').val();

        var baseUrl = '<?php echo esc_js( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ); ?>';
        var url     = baseUrl + '?page=adt_license_settings_page&tab=woo-product-feed-elite';

        if ( licenseKey ) {
            url += '&license_key=' + encodeURIComponent( licenseKey );
        }
        if ( activationEmail ) {
            url += '&activation_email=' + encodeURIComponent( activationEmail );
        }

        window.location.href = url;
    });
</script>

<style>
    .adt-pfe-activate-license-notice {
        padding: 1.2rem 1.2rem;
    }
    .adt-pfe-activate-license-notice p {
        margin-bottom: 0.8rem;
    }
    #adt-pfe-license-dialog-form {
        display: flex;
    }
    #adt-pfe-license-dialog-form .learn-more a {
        display: inline-block;
        color: #555;
    }

    #adt-pfe-license-dialog-form .form-row.form-group {
        display: flex;
        flex-direction: row;
        align-items: flex-end;
    }

    #adt-pfe-license-dialog-form .input-field {
        display: flex;
        flex-direction: column;
        margin-right: 1em;
    }

    #adt-pfe-license-dialog-form .input-field .form-control {
        width: 340px;
        padding: 2px 12px;
        border-radius: 2px;
        border-color: #d9d9d9;
        font-size: 16px;
    }

    #adt-pfe-license-dialog-form .input-field label {
        font-weight: 700;
        margin-bottom: 8px;
    }

    #adt-pfe-license-dialog-form button {
        height: 38px;
        background-color: #e63e77;
        border-color: #e63e77;
        color: #ffffff;
        padding: 0 23px;
        font-weight: 500;
    }

    #adt-pfe-license-dialog-form .input-button button:hover {
        background-color: #e63e77;
    }

    #adt-pfe-license-dialog-form .input-button {
        display: flex;
        align-items: end;
        margin-right: 0;
        flex-direction: initial;
    }
</style>
