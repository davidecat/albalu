<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use AdTribes\PFE\Helpers\Product_Feed_Helper;
use AdTribes\PFE\Helpers\Helper;
use AdTribes\PFE\Classes\Product_Feed_Attributes;
use AdTribes\PFE\Factories\Admin_Notice;

// Create product attribute object.
$product_feed_attributes = new Product_Feed_Attributes();
$attributes              = $product_feed_attributes->get_attributes();

$feed      = null;
$feed_id   = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : ''; // phpcs:ignore
$edit_feed = false;
if ( $feed_id ) {
    $feed = Product_Feed_Helper::get_product_feed( $feed_id );
    if ( $feed ) {
        $channel_data   = $feed->channel;
        $manage_project = 'yes';

        $channel_hash = $feed->channel_hash;
        $project_hash = $feed->legacy_project_hash;

        $field_manipulation = $feed->field_manipulation;

        $edit_feed = true;
    }
} else {
    $feed               = get_option( ADT_OPTION_TEMP_PRODUCT_FEED, array() );
    $channel_hash       = $feed['channel_hash'] ?? '';
    $project_hash       = $feed['project_hash'] ?? '';
    $channel_data       = Product_Feed_Helper::get_channel_from_legacy_channel_hash( $channel_hash );
    $field_manipulation = $feed['field_manipulation'] ?? array();
}
?>
<div class="woo-product-feed-pro-form-style-2">
    <?php
    // Display info message notice.
    $admin_notice = new Admin_Notice(
        sprintf(
            // translators: %s = URL to blog post.
            __(
                '<p>Manipulate your product data to improve the quality of your product feeds and online marketing campaigns. Manipulating your product data is an extremely powerfull feature.</p>
                <p>Check out an example we have created in our blog post: <b><u><a href="%s" target="_blank">Manipulating product data</a></u></b></p>',
                'woo-product-feed-elite'
            ),
            esc_url( Helper::get_utm_url( 'feature-product-data-manipulation', 'pfe', 'productdatamanipulationnotice', 'manipulatingproductdata' ) )
        ),
        'info',
        false,
        true,
    );
    $admin_notice->run();
    ?>

    <form class="adt-edit-feed-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="data_manipulation" method="post">
        <?php wp_nonce_field( 'woosea_ajax_nonce' ); ?>
        <input type="hidden" id="feed_id" name="feed_id" value="<?php echo esc_attr( $feed->id ?? 0 ); ?>">
        <input type="hidden" name="action" value="edit_feed_form_process" />
        <input type="hidden" name="active_tab" value="data_manipulation" />
        <table class="woo-product-feed-pro-table" id="woosea-ajax-table" border="1">
            <thead>
                <tr>
                    <th style="width: 1%;"></th>
                    <th><?php esc_html_e( 'Product type', 'woo-product-feed-elite' ); ?></th>
                    <th><?php esc_html_e( 'Field', 'woo-product-feed-elite' ); ?></th>
                    <th><?php esc_html_e( 'Becomes', 'woo-product-feed-elite' ); ?></th>
                    <th></th>
                </tr>
            </thead>

            <tbody class="woo-product-feed-pro-body">
                <?php
                if ( ! empty( $field_manipulation ) ) {
                    $product_types = array(
                        'all'      => 'Simple and variable',
                        'simple'   => 'Simple',
                        'variable' => 'Variable',
                    );

                    foreach ( $field_manipulation as $manipulation_key => $manipulation_array ) {
                ?>
                        <tr class="rowCount">
                            <td valign="top">
                                <input type="hidden" name="field_manipulation[<?php echo esc_html( $manipulation_key ); ?>][rowCount]" value="<?php echo esc_html( $manipulation_key ); ?>">
                                <input type="checkbox" name="record" class="checkbox-field">
                            </td>
                            <td valign="top">
                                <select name="field_manipulation[<?php echo esc_html( $manipulation_key ); ?>][product_type]" class="select-field woo-sea-select2">
                                    <?php
                                    foreach ( $product_types as $k => $v ) :
                                        if (
                                            isset( $field_manipulation[ $manipulation_key ]['product_type'] ) &&
                                            ( $field_manipulation[ $manipulation_key ]['product_type'] === $k )
                                        ) :
                                        ?>
                                            <option value="<?php echo esc_attr( $k ); ?>" selected><?php echo esc_html( $v ); ?></option>
                                        <?php else : ?>
                                            <option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v ); ?></option>
                                        <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </select>
                            </td>
                            <td valign="top">
                                <select name="field_manipulation[<?php echo esc_html( $manipulation_key ); ?>][attribute]" class="select-field woo-sea-select2">
                                    <?php
                                    if ( ! empty( $attributes ) ) :
                                        foreach ( $attributes as $group_name => $attribute ) :
                                        ?>
                                            <optgroup label='<?php echo esc_html( $group_name ); ?>'>
                                            <?php
                                            if ( ! empty( $attribute ) ) :
                                                foreach ( $attribute as $attr => $attr_label ) :
                                                ?>
                                                    <option 
                                                        value="<?php echo esc_attr( $attr ); ?>"
                                                        <?php
                                                            echo isset( $field_manipulation[ $manipulation_key ]['attribute'] ) &&
                                                                $field_manipulation[ $manipulation_key ]['attribute'] === $attr
                                                                ? 'selected'
                                                                : '';
                                                        ?>
                                                    >
                                                        <?php echo esc_html( $attr_label ); ?>
                                                    </option>
                                                    <?php
                                                endforeach;
                                            endif;
                                        endforeach;
                                    endif;
                                    ?>
                                </select>
                            </td>
                            <td valign="top" class="becomes_fields_<?php echo esc_html( $manipulation_key ); ?>">
                                <?php
                                $becomes_count = count( $field_manipulation[ $manipulation_key ]['becomes'] );
                                foreach ( $field_manipulation[ $manipulation_key ]['becomes'] as $k => $v ) :
                                ?>
                                    <div class="becomes-field-wrapper" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                        <select name="field_manipulation[<?php echo esc_attr( $manipulation_key ); ?>][becomes][<?php echo esc_attr( $k ); ?>][attribute]" class="select_field woo-sea-select2" style="flex: 1;">
                                        <?php
                                        if ( ! empty( $attributes ) ) :
                                            foreach ( $attributes as $group_name => $attribute ) :
                                            ?>
                                                <optgroup label='<?php echo esc_html( $group_name ); ?>'>
                                                <?php
                                                if ( ! empty( $attribute ) ) :
                                                    foreach ( $attribute as $attr => $attr_label ) :
                                                    ?>
                                                        <option
                                                            value="<?php echo esc_attr( $attr ); ?>"
                                                            <?php
                                                                echo isset( $field_manipulation[ $manipulation_key ]['becomes'][ $k ]['attribute'] ) &&
                                                                    $field_manipulation[ $manipulation_key ]['becomes'][ $k ]['attribute'] === $attr
                                                                    ? 'selected'
                                                                    : '';
                                                            ?>
                                                        >
                                                            <?php echo esc_html( $attr_label ); ?>
                                                        </option>
                                                        <?php
                                                    endforeach;
                                                endif;
                                            endforeach;
                                        endif;
                                        ?>
                                        </select>
                                        <span class="dashicons dashicons-no-alt remove-becomes-field" style="cursor: pointer; color: #dc3232;<?php echo 1 === $becomes_count ? ' display: none;' : ''; ?>" title="<?php esc_attr_e( 'Remove this attribute', 'woo-product-feed-elite' ); ?>"></span>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <span
                                    class="dashicons dashicons-plus field_extra field_manipulation_extra_<?php echo esc_html( $manipulation_key ); ?>"
                                    style="display: inline-block;"
                                    title="Add an attribute to this field"
                                ></span>
                            </td>
                        </tr>
                <?php
                    }
                }
                ?>
            </tbody>

            <tbody>

                <tr class="rules-buttons">
                    <td colspan="8">
                        <div class="adt-edit-feed-form-buttons adt-tw-flex adt-tw-gap-2 adt-tw-items-center">
                            <input type="hidden" id="channel_hash" name="channel_hash" value="<?php echo esc_attr( $channel_hash ); ?>">
                            <input type="hidden" name="project_hash" value="<?php echo esc_attr( $project_hash ); ?>">
                            <input type="hidden" name="woosea_page" value="field_manipulation">
                            <button type="button" class="adt-button adt-button-sm delete-row">- <?php esc_attr_e( 'Delete', 'woo-product-feed-elite' ); ?></button>
                            <button type="button" class="adt-button adt-button-sm add-field-manipulation">+ <?php esc_attr_e( 'Add field manipulation', 'woo-product-feed-elite' ); ?></button>
                            <button type="submit" id="savebutton" class="adt-button adt-button-sm adt-button-primary"><?php esc_attr_e( 'Save Changes', 'woo-product-feed-elite' ); ?></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
