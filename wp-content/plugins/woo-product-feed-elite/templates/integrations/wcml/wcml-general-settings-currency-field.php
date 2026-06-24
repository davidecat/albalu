<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! empty( $wcml_currencies ) ) : ?>
    <tr>
        <td><span><?php esc_html_e( 'Currency (WCML):', 'woo-product-feed-elite' ); ?></span></td>
        <td>
            <select name="WCML">
            <?php foreach ( $wcml_currencies as $value ) : ?>
                <option value=<?php echo esc_attr( $value ); ?> <?php echo $value === $wcml_selected_currency ? 'selected' : ''; ?> >
                    <?php echo esc_html( $value ); ?>
                </option>
            <?php endforeach; ?>
            </select>
        </td>
    </tr>
<?php endif; ?>
