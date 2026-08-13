<?php
/**
 * The markup for the 'Uploads' tab's settings
 *
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 4.4.0, moved here so that it can be used in ajax-condition.php
$product = wc_get_product( $post_id );
?>

<div class="pewc-fields-wrapper pewc-fields-conditionals">

	<div class="product-extra-field">	
		<div class="product-extra-field-inner">
			<label><?php _e( 'Conditions', 'pewc' ); ?></label>
		</div>
		<div class="product-extra-field-inner">
			<?php
			if ( pewc_use_ajax_conditions() && ! empty( $item_key) ) {
				// 4.4.0, only load this on field conditions. $item_key is empty if this is .new-field-list
				include( PEWC_DIRNAME . '/templates/admin/ajax-condition.php' );
			} else {
				include( PEWC_DIRNAME . '/templates/admin/condition.php' );
			}
			?>
		</div>
	</div>

</div><!-- .pewc-fields-wrapper -->

<?php
if( $product && $product->is_type( 'variable' ) ) { ?>
	<div class="pewc-fields-wrapper pewc-fields-variations show_if_variable">
		<div class="product-extra-field">	
			<div class="product-extra-field-inner">
				<label><?php _e( 'Variations', 'pewc' ); ?></label>
			</div>
			<div class="product-extra-field-inner">
				<?php $variations = $product->get_children();
				include( PEWC_DIRNAME . '/templates/admin/variation.php' );
				printf(
					'<p>%s</p>',
					__( 'If you only want to show this field for certain variations, enter the variation IDs below. Leave empty to show for all variations.', 'pewc' )
				); ?>
			</div>
		</div>	
	</div><!-- .pewc-fields-wrapper -->
<?php
}

do_action( 'pewc_end_conditions_settings_section', $base_name, $group_id, $item_key, $field_type, $field_label, $admin_label );