<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! empty( $aelia_enabled_currencies ) ) : ?>
    <tr>
        <td><span><?php esc_html_e( 'Currency (Aelia):', 'woo-product-feed-elite' ); ?></span></td>
        <td>
            <select name="AELIA" class="aelia_switch">
            <?php foreach ( $aelia_enabled_currencies as $currency ) : ?>
                <option value=<?php echo esc_attr( $currency ); ?> <?php echo $aelia_selected_currency === $currency ? 'selected' : ''; ?> >
                    <?php echo esc_html( $currency ); ?>
                </option>
            <?php endforeach; ?>
            </select>
        </td>
    </tr>
<?php endif; ?>
