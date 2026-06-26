<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr>
    <td><span><?php esc_html_e( 'Language (WPML):', 'woo-product-feed-elite' ); ?></span></td>
    <td>
        <select name="WPML">
        <?php foreach ( $wpml_active_languages as $key => $value ) : ?>
            <option value="<?php echo esc_attr( $key ); ?>" <?php echo $key === $wpml_selected_language ? 'selected' : ''; ?> >
                <?php echo esc_html( $value['english_name'] ); ?>
            </option>
        <?php endforeach; ?>
        </select>
    </td>
</tr>
