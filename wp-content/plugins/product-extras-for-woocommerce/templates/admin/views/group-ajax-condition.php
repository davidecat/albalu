<?php
/**
 * The markup for the AJAX group condition
 *
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<?php
$action = pewc_get_group_condition_action( $group_id, $group );
$match = pewc_get_group_condition_match( $group_id );
$conditions = pewc_get_group_conditions( $group_id );

$style = 'style="display: none;"';

if( ! empty( $conditions ) ) {
	$style = 'style="display: grid;"';
} ?>

<div class="product-extra-conditional-row product-extra-action-match-row pewc-ajax-condition-row" id="pewc_ajax_condition_<?php echo esc_attr( $group_id ); ?>" <?php echo $style; ?>>

	<div class="pewc-ajax-condition-display">
		<?php $actions = pewc_get_actions();
		if( ! empty( $actions[$action] ) ) { ?>
			<span class="pewc-ajax-condition-action"><?php echo $actions[$action]; ?>: </span>
			<input type="hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>[condition_action]" value="<?php echo esc_attr( $action ); ?>">
		<?php } ?>
	</div>
	<div class="pewc-ajax-condition-display">
		<?php $matches = pewc_get_matches();
		if( ! empty( $matches[$match] ) ) { ?>
			<span class="pewc-ajax-condition-match"><?php echo $matches[$match]; ?></span>
			<input type="hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>[condition_match]" value="<?php echo esc_attr( $match ); ?>">
		<?php } ?>
	</div>
	<div class="pewc-ajax-condition-edit pewc-ajax-condition-edit-action"></div>
	<div class="pewc-ajax-condition-edit pewc-ajax-condition-edit-match"></div>

	<div class="remove-condition-wrapper">
		<span class="undo-condition pewc-action"><span class="dashicons dashicons-undo"></span></span>
		<span class="update-condition pewc-action"><span class="dashicons dashicons-edit"></span></span>
	</div>
</div>

<?php
if( ! empty( $conditions ) ) {

	$condition_count = 0;

	$is_ajax = pewc_enable_ajax_load_addons();
	$fields = pewc_get_all_fields( $group, $is_ajax, $post_id );
	$id = 'pewc_group_' . $group_id;
	unset( $fields[$id] );

	$rules = pewc_get_rules();

	foreach( $conditions as $index=>$condition ) { ?>

		<div class="product-extra-conditional-row product-extra-conditional-rule pewc-ajax-condition-row" id="pewc_ajax_condition_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $condition_count ); ?>" data-condition-count="<?php echo esc_attr( $condition_count ); ?>">

			<div class="pewc-ajax-condition-display">
				<?php

				$field = isset( $condition['field'] ) ? $condition['field'] : false;

				if( ! empty( $fields[$field] ) ) {
					// Get the field type of the selected field
					$cond_group_id = pewc_get_group_id( $field );
					$cond_field_id = pewc_get_field_id( $field );
					// $condition_field = $field;

					$condition_field_type = '';
					if ( 'pa_' === substr( $field, 0, 3 ) ) {
						$condition_field_type = 'attribute';
					} else if( $field == 'cost' ) {
						$condition_field_type = 'cost';
					} else if( $field == 'quantity' ) {
						$condition_field_type = 'quantity';
					} else if( $field == 'log-in-status' ) {
						$condition_field_type = 'log-in-status';
					} else if( $field == 'user-role' ) {
						$condition_field_type = 'user-role';
					} else if( ! empty( $groups[$cond_group_id]['items'][$cond_field_id]['field_type'] ) ) {
						// Pre 3.0
						$condition_field_type = $groups[$cond_group_id]['items'][$cond_field_id]['field_type'];
					} else {
						// 3.0+
						$condition_field_type = get_post_meta( $cond_field_id, 'field_type', true );
					} ?>
					<span class="pewc-ajax-condition-field"><?php echo esc_html( $fields[$field ] ); ?></span>
					<input type="hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>[condition_field][<?php echo esc_attr( $condition_count ); ?>]" value="<?php echo esc_attr( $field ); ?>">
					<input type="hidden" class="pewc-hidden-field-type" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>[condition_field_type][<?php echo esc_attr( $condition_count ); ?>]" id="condition_field_type_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $condition_count ); ?>" value="<?php echo $condition_field_type; ?>">
				<?php } ?>
			</div>
			<div class="pewc-ajax-condition-edit pewc-ajax-condition-edit-field"></div>

			<div class="pewc-ajax-condition-display">
				<?php
				$rule = isset( $condition['rule'] ) ? $condition['rule'] : 'not-selected';
				if ( ! empty( $rules[$rule] ) ) {
					$class = "pewc-condition-rule pewc-condition-select";
					
					$cond_field_item = pewc_create_item_object( $cond_field_id );
					$allow_multiple = ! empty( $cond_field_item['allow_multiple'] );
					if( $condition_field_type == 'products' && isset( $item['products_layout'] ) && ( $item['products_layout'] == 'checkboxes' || $item['products_layout'] == 'checkboxes-list' || $item['products_layout'] == 'column' ) ) {
						$allow_multiple = true;
					}
					if( ( $condition_field_type == 'image_swatch' && $allow_multiple ) || $condition_field_type == 'checkbox_group' || ( $condition_field_type == 'products' && $allow_multiple ) ) {
						$class .= ' pewc-has-multiple';
					} ?>
					<span class="pewc-ajax-condition-rule"><?php echo esc_html( $rules[$rule] ); ?></span>
					<input type="hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>[condition_rule][<?php echo esc_attr( $condition_count ); ?>]" value="<?php echo esc_attr( $rule ); ?>">
				<?php } ?>
			</div>
			<div class="pewc-ajax-condition-edit pewc-ajax-condition-edit-rule"></div>

			<div class="pewc-ajax-condition-display">
				<?php
				$display_value = $value = isset( $condition['value'] ) ? $condition['value'] : '';

				if( $condition_field_type == 'text' || ( $condition_field_type == 'attribute' && ( $rule == 'contains' || $rule == 'does-not-contain' ) ) ) { 
					$display_value = $value;
				 } else if( $condition_field_type == 'number' || $condition_field_type == 'cost' || $condition_field_type == 'quantity' || $condition_field_type == 'calculation' || $condition_field_type == 'upload' ) {
					$display_value = (float) $value; // force number?
				 } else if( $condition_field_type == 'select' || $condition_field_type == 'select-box' || $condition_field_type == 'radio' || $condition_field_type == 'image_swatch' || $condition_field_type == 'products' || $condition_field_type == 'checkbox_group' || $condition_field_type == 'log-in-status' || $condition_field_type == 'user-role' || ( $condition_field_type == 'attribute' && ( $rule == 'is' || $rule == 'is-not' ) ) ) {
					if( $condition_field_type == 'products' || $condition_field_type == 'product-categories' ) {
						$field_options = false;

						if( $condition_field_type == 'products' ) { //Product field type
							
							if( ! pewc_has_migrated() && ! empty( $groups[$cond_group_id]['items'][$cond_field_id]['child_products'] ) ) {
								// Pre 3.0
								$field_options = $groups[$cond_group_id]['items'][$cond_field_id]['child_products'];
							} else {
								// 3.0+
								$field_options = get_post_meta( $cond_field_id, 'child_products', true );
							}
						} else {  //Product Categories field type

							// 3.9.7+
							$field_options = get_post_meta( $cond_field_id, 'child_categories', true );
						}

						if( $field_options ) {
							foreach( $field_options as $option ) {
								if ( $option == $value ) {
									$display_value = $option;
								}
							}
						}
					} if( $condition_field_type == 'log-in-status' ) {

						$display_value = pewc_get_logged_in_status_options( $value, true );

					} else if ( $condition_field_type == 'user-role' ) {

						$display_value = pewc_get_user_role_options( $value, true );

					} else {
						$field_options = array( '' );
						if( ! pewc_has_migrated() && ! empty( $groups[$cond_group_id]['items'][$cond_field_id]['field_options'] ) ) {
							// Pre 3.0
							$field_options = $groups[$cond_group_id]['items'][$cond_field_id]['field_options'];
							
						} else {
							// 3.0+
							$field_options = get_post_meta( $cond_field_id, 'field_options', true );
						}

						if( $field_options ) {
							foreach( $field_options as $option ) {
								if ( $value == $option['value'] ) {
									$display_value = $option['value'];
								}
							}
						}
					}
				} else if( $condition_field_type == 'checkbox' ) {
					$display_value = 'Checked';
				} ?>

				<span class="pewc-ajax-condition-value"><?php echo esc_html( $display_value ); ?></span>
				<input type="hidden" class="pewc-condition-value-hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>[condition_value][<?php echo esc_attr( $condition_count ); ?>]" value="<?php echo esc_attr( $value ); ?>">
			</div>
			<div class="pewc-ajax-condition-edit pewc-condition-value-field"></div>

			<div class="remove-condition-wrapper">
				<span class="undo-condition pewc-action"><span class="dashicons dashicons-undo"></span></span>
				<span class="update-condition pewc-action"><span class="dashicons dashicons-edit"></span></span>
				<span class="remove-condition pewc-action"><span class="dashicons dashicons-trash"></span></span>
			</div>

		</div><!-- .product-extra-conditional-row -->
	<?php $condition_count++;
	}
}
?>
<p>
	<a href="#" class="button add_new_group_condition"><?php _e( 'Add Condition', 'pewc' ); ?></a>
	<!--<a href="#" class="button cancel_ajax_group_condition" style="display:none;"><?php _e( 'Cancel', 'pewc' ); ?></a>-->
</p>
<!--<p><a href="#" class="button manage_ajax_group_condition" style="<?php echo ( empty( $conditions ) ) ? 'display:none;' : ''; ?>"><?php _e( 'Manage Conditions', 'pewc' ); ?></a></p>-->
