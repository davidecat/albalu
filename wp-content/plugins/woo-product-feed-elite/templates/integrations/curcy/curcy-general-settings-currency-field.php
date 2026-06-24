<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! empty( $curcy_currencies ) ) : ?>
    <tr>
        <td><span><?php esc_html_e( 'Currency (CURCY):', 'woo-product-feed-elite' ); ?></span></td>
        <td>
            <select name="CURCY" class="curcy_switch">
            <?php foreach ( $curcy_currencies as $value ) : ?>
                <option value=<?php echo esc_attr( $value ); ?> <?php echo $value === $curcy_selected_currency ? 'selected' : ''; ?> >
                    <?php echo esc_html( $value ); ?>
                </option>
            <?php endforeach; ?>
            </select>
        </td>
    </tr>
<?php endif; ?>
