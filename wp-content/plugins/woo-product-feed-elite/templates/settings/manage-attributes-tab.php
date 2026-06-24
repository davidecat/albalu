<?php
/**
 * Manage attributes tab.
 *
 * @package AdTribes\PFE\Templates\Settings
 */

use AdTribes\PFE\Classes\Extra_Attributes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$field_definitions = Extra_Attributes::instance()->get_field_definitions();
$extra_attributes  = get_option( 'adt_extra_attributes', array() );

foreach ( $field_definitions as $key => $value ) :
?>
<div class="adt-tw-grid adt-tw-grid-cols-1 md:adt-tw-grid-cols-2 adt-tw-gap-6 adt-tw-w-full">
    <div class="adt-tw-min-w-0 adt-tw-overflow-hidden">
        <h2 class="adt-tw-text-xl adt-tw-font-semibold adt-tw-mb-2"><?php echo esc_html( $value['title'] ); ?></h2>
        <p class="adt-tw-text-sm adt-tw-text-gray-500 adt-tw-mt-0 adt-tw-mb-4"><?php echo esc_html( $value['description'] ); ?></p>
        <table class="woo-product-feed-pro-table adt-tw-w-full adt-tw-table-auto">
            <tr>
                <td><strong><?php esc_html_e( 'Attribute name', 'woo-product-feed-elite' ); ?></strong></td>
                <td style="text-align: right;"></strong></td>
            </tr>
            <?php
            foreach ( $value['fields'] as $key => $field ) :
                // Trim spaces before and after.
                $field_key = 'custom_attributes_' . $field['id'];
                $checked   = isset( $extra_attributes[ $field_key ] ) && array_key_exists( $field_key, $extra_attributes ) ? 'checked' : '';
            ?>
            <tr id="<?php echo esc_attr( $field_key ); ?>">
                <td><span><?php echo esc_html( $field['label'] ); ?></span></td>
                <td style="text-align: right;">
                    <label class="woo-product-feed-pro-switch">
                        <input type="hidden" name="manage_attribute" value="<?php echo esc_attr( $field_key ); ?>">
                        <input type="checkbox" id="attribute_active" name="<?php echo esc_attr( $field_key ); ?>" class="checkbox-field" value="<?php echo esc_attr( $field_key ); ?>" <?php echo esc_attr( $checked ); ?>>
                        <div class="woo-product-feed-pro-slider round"></div>
                    </label>
                </td>
            </tr>
            <?php
            endforeach;
            ?>
        </table>
    </div>
</div>
<?php
endforeach;
?>
