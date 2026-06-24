<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr>
    <td><span><?php esc_html_e( 'TranslatePress Language:', 'woo-product-feed-elite' ); ?></span></td>
    <td>
        <select name="TRANSLATEPRESS">
        <?php foreach ( $trp_published_languages as $key => $name ) : ?>
            <option value="<?php echo esc_attr( $key ); ?>" <?php echo $key === $trp_selected_language ? 'selected' : ''; ?> >
                <?php echo esc_html( $name ); ?>
            </option>
        <?php endforeach; ?>
        </select>
    </td>
</tr>
