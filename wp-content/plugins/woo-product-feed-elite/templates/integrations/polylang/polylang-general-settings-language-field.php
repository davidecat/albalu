<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr>
    <td><span><?php esc_html_e( 'Polylang language:', 'woo-product-feed-elite' ); ?></span></td>
    <td>
        <select name="polylang">
        <?php foreach ( $polylang_active_languages as $lang ) : ?>
            <option value="<?php echo esc_attr( $lang->slug ); ?>" <?php echo $lang->slug === $polylang_selected_language ? 'selected' : ''; ?> >
                <?php echo esc_html( $lang->name ); ?>
            </option>
        <?php endforeach; ?>
        </select>
    </td>
</tr>
