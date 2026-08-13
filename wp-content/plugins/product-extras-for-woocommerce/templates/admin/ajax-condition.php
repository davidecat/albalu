<?php
/**
 * The markup for an AJAX conditional row, i.e. one condition
 *
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<?php $style = 'style="display: none;"';
if( ! empty( $item['condition_field'] ) ) {
	$style = 'style="display: grid;"';
} ?>
<div class="product-extra-conditional-row product-extra-action-match-row pewc-ajax-condition-row" id="pewc_ajax_condition_field_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $item_key ); ?>" data-field-id="<?php echo esc_attr( $item_key ); ?>" <?php echo $style; ?>>

	<div class="pewc-ajax-condition-display">
		<?php $actions = pewc_get_actions();
		$action = '';
		if( isset( $item['condition_action'] ) ) {
			$action = $item['condition_action'];
		}
		if( ! empty( $actions[$action] ) ) { ?>
			<span class="pewc-ajax-condition-action"><?php echo esc_html( $actions[$action] ); ?>: </span>
			<input type="hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $item_key ); ?>[condition_action]" value="<?php echo esc_attr( $action ); ?>">
		<?php } ?>
	</div>
	<div class="pewc-ajax-condition-edit pewc-ajax-condition-edit-action"></div>

	<div class="pewc-ajax-condition-display">
		<?php $matches = pewc_get_matches();
		$match = '';
		if( isset( $item['condition_match'] ) ) {
			$match = $item['condition_match'];
		}
		if( ! empty( $matches[$match] ) ) { ?>
			<span class="pewc-ajax-condition-match"><?php echo esc_html( $matches[$match] ); ?></span>
			<input type="hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $item_key ); ?>[condition_match]" value="<?php echo esc_attr( $match ); ?>">
		<?php } ?>
	</div>
	<div class="pewc-ajax-condition-edit pewc-ajax-condition-edit-match"></div>

	<div class="remove-condition-wrapper">
		<span class="undo-condition pewc-action"><span class="dashicons dashicons-undo"></span></span>
		<span class="update-condition pewc-action"><span class="dashicons dashicons-edit"></span></span>
	</div>
</div>

<?php
if( ! empty( $item['condition_field'] ) ) {

	$condition_count = 0;

	// 3.27.1
	$allow_multiple_layouts = array( 'checkboxes', 'checkboxes-list', 'column' );

	foreach( $item['condition_field'] as $condition ) { ?>

		<div class="product-extra-conditional-row product-extra-conditional-rule pewc-ajax-condition-row" id="pewc_ajax_condition_field_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $item_key ); ?>_<?php echo esc_attr( $condition_count ); ?>" data-field-id="<?php echo esc_attr( $item_key ); ?>" data-condition-count="<?php echo esc_attr( $condition_count ); ?>">

			<div class="pewc-ajax-condition-display">

				<?php
				if( ! isset( $group  ) ) {
					$group = pewc_get_group_fields( $group_id );
				}
				$is_ajax = pewc_enable_ajax_load_addons();
				$fields = pewc_get_all_fields( $group, $is_ajax, $post_id );

				if ( ! isset( $_GET['post'] ) || 'product' !== get_post_type( $_GET['post'] ) ) {
					$all_attributes = wc_get_attribute_taxonomies();
					if ( ! empty( $all_attributes ) ) {
						foreach ( $all_attributes as $cond_attribute ) {
							$key = 'pa_' . $cond_attribute->attribute_name;
							if ( ! isset( $fields[$key] ) ) {
								$fields[$key] = $cond_attribute->attribute_label;
							}
						}
					}
				} else if ( $product && $product->is_type( 'variable' ) ) {
					// for variable products, get only the attributes for this product
					$product_attributes = $product->get_attributes();
					if ( ! empty( $product_attributes ) ) {
						foreach ( $product_attributes as $cond_key => $cond_attribute ) {
							$fields[$cond_key] = wc_attribute_label( $cond_key, $product );
						}
					}
				}

				$id = 'pewc_group_' . $group_id . '_' . $item_key;
				unset( $fields[$id] );
				$field = '';

				if( isset( $item['condition_field'][$condition_count] ) ) {
					$field = $item['condition_field'][$condition_count];
				}

				// Get the field type of the selected field
				$cond_group_id = pewc_get_group_id( $field );
				$cond_field_id = pewc_get_field_id( $field );

				$condition_field_type = '';
				if ( 'pa_' === substr( $field, 0, 3 ) ) {
					$condition_field_type = 'attribute';
				} else if( $field == 'cost' ) {
					$condition_field_type = 'cost';
				} else if( $field == 'quantity' ) {
					$condition_field_type = 'quantity';
				} else if( $field == 'log-in-status' ) {
					$condition_field_type = 'log-in-status';
				} else if( ! empty( $groups[$cond_group_id]['items'][$cond_field_id]['field_type'] ) ) {
					// Pre 3.0
					$condition_field_type = $groups[$cond_group_id]['items'][$cond_field_id]['field_type'];
				} else {
					// 3.0+
					$condition_field_type = get_post_meta( $cond_field_id, 'field_type', true );
				}

				if( ! empty( $fields ) ) { ?>
					<span class="pewc-ajax-condition-field"><?php
					if ( ! empty( $fields[$field] ) ) {
						echo esc_html( $fields[$field] );
						if ( $cond_field_id && 'pewc_group_' === substr( $field, 0, 11 ) ) {
							echo esc_html( ' [#' . $cond_field_id . ']' );
						}
					} else {
						echo esc_html( $field );
					}
					?></span>
					<input type="hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $item_key ); ?>[condition_field][<?php echo esc_attr( $condition_count ); ?>]" value="<?php echo esc_attr( $field ); ?>">
					<input type="hidden" class="pewc-hidden-field-type" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $item_key ); ?>[condition_field_type][<?php echo esc_attr( $condition_count ); ?>]" id="condition_field_type_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $item_key ); ?>_<?php echo esc_attr( $condition_count ); ?>" data-original-value="<?php echo esc_attr( $condition_field_type ); ?>" value="<?php echo esc_attr( $condition_field_type ); ?>">
				<?php } ?>
			</div>
			<div class="pewc-ajax-condition-edit pewc-ajax-condition-edit-field"></div>

			<div class="pewc-ajax-condition-display">
				<?php
				$rules = pewc_get_rules();
				$rule = 'not-selected';
				if( isset( $item['condition_rule'][$condition_count] ) ) {
					$rule = $item['condition_rule'][$condition_count];
				}
				if( ! empty( $rules[$rule] ) ) { ?>
					<span class="pewc-ajax-condition-rule"><?php echo esc_html( $rules[$rule] ); ?></span>
					<input type="hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $item_key ); ?>[condition_rule][<?php echo esc_attr( $condition_count ); ?>]" value="<?php echo esc_attr( $rule ); ?>">
				<?php } ?>
			</div>
			<div class="pewc-ajax-condition-edit pewc-ajax-condition-edit-rule"></div>

			<div class="pewc-ajax-condition-display">
				<?php $display_value = $value = '';
				if( isset( $item['condition_value'][$condition_count] ) ) {
					$value = $item['condition_value'][$condition_count];
					$display_value = $value;
				}

				if( $condition_field_type == 'text' || $condition_field_type == 'advanced-preview' || ( $condition_field_type == 'attribute' && ( $rule == 'contains' || $rule == 'does-not-contain' ) ) || $condition_field_type == 'product-categories' ) {
					$display_value = $value;
				} else if( $condition_field_type == 'number' || $condition_field_type == 'cost' || $condition_field_type == 'quantity' || $condition_field_type == 'calculation' || $condition_field_type == 'upload' ) {
					$display_value = (float) $value;
				} else if( $condition_field_type == 'select' || $condition_field_type == 'calendar-list' || $condition_field_type == 'select-box' || $condition_field_type == 'radio' || $condition_field_type == 'image_swatch' || $condition_field_type == 'products' || $condition_field_type == 'checkbox_group' || $condition_field_type == 'log-in-status' || ( $condition_field_type == 'attribute' && ( $rule == 'is' || $rule == 'is-not' ) ) ) {

					if( $condition_field_type == 'products' ) {
						$field_options = false;
						if( ! pewc_has_migrated() && ! empty( $groups[$cond_group_id]['items'][$cond_field_id]['child_products'] ) ) {
							// Pre 3.0
							$field_options = $groups[$cond_group_id]['items'][$cond_field_id]['child_products'];
						} else {
							// 3.0+
							$field_options = get_post_meta( $cond_field_id, 'child_products', true );
						}
						if( $field_options ) {
							foreach( $field_options as $option ) {
								if( $option == $value ) {
									$display_value = $option;
								}
							}
						}
					} if( $condition_field_type == 'log-in-status' ) {
						$display_value = pewc_get_logged_in_status_options( $value, true );
					} else {
						$field_options = array( '' );
						if( ! pewc_has_migrated() && ! empty( $groups[$cond_group_id]['items'][$cond_field_id]['field_options'] ) ) {
							// Pre 3.0
							$field_options = $groups[$cond_group_id]['items'][$cond_field_id]['field_options'];
						} else {
							// 3.0+
							if( $condition_field_type == 'calendar-list' ) {
								$field_options = get_post_meta( $cond_field_id, 'field_cl_options', true );
							} else {
								$field_options = get_post_meta( $cond_field_id, 'field_options', true );
							}
						}
						if( $field_options ) {
							foreach( $field_options as $option ) {
								if( $value == $option['value'] ) {
									$display_value = $option['value'];
								}
							}
						}
					}
				} else if( $condition_field_type == 'checkbox' ) {
					$display_value = __( 'Checked', 'pewc' );
				} ?>

				<span class="pewc-ajax-condition-value"><?php echo esc_html( $display_value ); ?></span>
				<input type="hidden" class="pewc-condition-value-hidden" name="_product_extra_groups_<?php echo esc_attr( $group_id ); ?>_<?php echo esc_attr( $item_key ); ?>[condition_value][<?php echo esc_attr( $condition_count ); ?>]" value="<?php echo esc_attr( $value ); ?>">
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
<p><a href="#" class="button add_new_condition"><?php _e( 'Add Condition', 'pewc' ); ?></a></p>
