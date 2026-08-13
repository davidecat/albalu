<?php
/**
 * Functions for conditions in the back end
 * @since 3.20.0
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return options for the true/false select field for Log In Status
 * @since 3.20.0
 */
function pewc_get_logged_in_status_options( $value, $label_only=false ) {

	$field_options = array(
		'true'	=> __( 'True', 'pewc' ),
		'false'	=> __( 'False', 'pewc' ),
	);
	$options = array();
	foreach( $field_options as $key=>$label ) {
		if ( $label_only ) {
			// 4.4.0, return only the label for AJAX conditions
			if ( $value == $key ) {
				return $label;
			}
		} else {
			$selected = selected( $value, $key, false );
			$options[] = sprintf(
				'<option %s value="%s">%s</option>',
				$selected,
				esc_attr( $key ),
				esc_attr( $label )
			);
		}
	}

	return join( ' ', $options );
}

/**
 * Return the options for the User Role group condition
 * @since 3.22.0
 */
function pewc_get_user_role_options( $value, $label_only=false ) {

	$all_roles_array = pewc_get_all_roles();
	$options = array();
	if ( ! empty( $all_roles_array ) ) {
		foreach ( $all_roles_array as $key => $label ) {
			if ( $label_only ) {
				// 4.4.0, return only the label for AJAX conditions
				if ( $value == $key ) {
					return $label;
				}
			} else {
				$selected = selected( $value, $key, false );
				$options[] = sprintf(
					'<option %s value="%s">%s</option>',
					$selected,
					esc_attr( $key ),
					esc_attr( $label )
				);
			}
		}
	}

	return join( ' ', $options );

}

/**
 * Get all the product extra fields for a product or group
 * Use this to populate the field parameter in conditionals (admin only)
 * @param $group 		Group data
 * @param $is_ajax		Are we loading add-ons via AJAX?
 * @param $product_id	Only passed from product page
 * @return Array
 * 
 * Moved here since 4.4.0
 */
function pewc_get_all_fields( $group=false, $is_ajax=false, $product_id=false ) {

	$fields = array( 'not-selected' => __( ' -- Select a field -- ', 'pewc' ) );

	if( $is_ajax || ( isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'product' ) ) {

		// Product
		if( ! $product_id ) {
			$product_id = $_GET['post'];
		}

		$groups = pewc_get_extra_fields( $product_id );

		// 4.4.0, if AJAX conditions is enabled, also retrieve data from global groups used by this product
		if ( pewc_use_ajax_conditions() ) {
			$groups = apply_filters( 'pewc_filter_product_extra_groups', $groups, $product_id );
		}

		if( $groups ) {
			foreach( $groups as $group ) {
				if( ! empty( $group['items'] ) ) {
					foreach( $group['items'] as $item ) {
						//$label = ! empty( $item['field_label'] ) ? $item['field_label'] : __( '[no label]', 'pewc' );
						$label = ! empty( $item['field_admin_label'] ) ? $item['field_admin_label'] : ( ! empty( $item['field_label'] ) ? $item['field_label'] : __( '[no label]', 'pewc' ) );
						if( ! empty( $item['id'] ) ) {
							$fields[$item['id']] = $label;
						}
					}
				}
			}
		}

	} else if( isset( $_GET['post'] ) && get_post_type( $_GET['post'] ) == 'pewc_group' && ! pewc_use_ajax_conditions() ) {

		// Group
		if( ! empty( $group ) ) {
			foreach( $group as $item ) {
				//$label = ! empty( $item['field_label'] ) ? $item['field_label'] : __( '[no label]', 'pewc' );
				$label = ! empty( $item['field_admin_label'] ) ? $item['field_admin_label'] : ( ! empty( $item['field_label'] ) ? $item['field_label'] : __( '[no label]', 'pewc' ) );
				$fields[$item['id']] = $label;
			}
		}

	} else if( $group ) {

		// If $group is passed, we are on the global extras
		// @since 2.2.3 use all global fields
		// 4.4.0, if pewc_use_ajax_conditions() is enabled, use this instead so that fields from other global groups are accessible?

		// Check if we've migrated @since 3.0
		if( ! pewc_has_migrated() ) {
			// Pre 3.0
			$globals = get_option( 'pewc_global_extras' );
			if( $globals ) {
				foreach( $globals as $group ) {
					foreach( $group['items'] as $item_key=>$item ) {
						//$label = ! empty( $item['field_label'] ) ? $item['field_label'] : __( '[no label]', 'pewc' );
						$label = ! empty( $item['field_admin_label'] ) ? $item['field_admin_label'] : ( ! empty( $item['field_label'] ) ? $item['field_label'] : __( '[no label]', 'pewc' ) );
						$fields[$item['id']] = $label;
					}
				}
			}
		} else {

			// Post 3.0
			$group_order = pewc_get_global_group_order();
			if( $group_order ) {
				// pewc_display_product_groups expects an array with the group_id as the key
				$new_order = explode( ',', $group_order );
				$combined_order = array_combine( $new_order, $new_order );
				foreach( $combined_order as $group_id ) {
					$group['items'] = pewc_get_group_fields( $group_id );
					foreach( $group['items'] as $item_key=>$item ) {
						//$label = ! empty( $item['field_label'] ) ? $item['field_label'] : __( '[no label]', 'pewc' );
						$label = ! empty( $item['field_admin_label'] ) ? $item['field_admin_label'] : ( ! empty( $item['field_label'] ) ? $item['field_label'] : __( '[no label]', 'pewc' ) );
						$fields[$item['id']] = $label;
					}
				}
			}

		}

	}

	$fields['cost'] = __( 'Cost', 'pewc' );
	$fields['quantity'] = __( 'Quantity', 'pewc' );
	$fields['log-in-status'] = __( 'Logged In', 'pewc' ); // 4.4.0, changed from 'Status' to 'Logged In'
	$fields['user-role'] = __( 'User Role', 'pewc' ); // 3.22.0

	return apply_filters( 'pewc_conditional_fields', $fields );

}

/**
 * Check if AJAX conditions is enabled
 * @since 4.4.0
 */
function pewc_use_ajax_conditions() {
	return apply_filters( 'pewc_ajax_conditions', 'yes' === get_option( 'pewc_ajax_conditions', 'no' ) );
}

/**
 * Enqueue AJAX conditions script only if it is enabled
 * @since 4.4.0
 */
function pewc_enqueue_ajax_conditions_script() {

	if ( pewc_use_ajax_conditions() ) {
		$version = defined( 'PEWC_SCRIPT_DEBUG' ) && PEWC_SCRIPT_DEBUG ? time() : PEWC_PLUGIN_VERSION;
		wp_register_script( 'pewc-admin-ajax-conditions', trailingslashit( PEWC_PLUGIN_URL ) . 'assets/js/admin-ajax-conditions.js', array( 'pewc-admin-script' ), $version, true );
		wp_enqueue_script( 'pewc-admin-ajax-conditions' );
	}

}
add_action( 'admin_enqueue_scripts', 'pewc_enqueue_ajax_conditions_script', 100 ); // run after AOU admin-fields.js

/**
 * Outputs other global groups as option groups in new-conditional-row.php
 * @since 4.4.0
 */
function pewc_output_other_global_groups( $group_id ) {

	$group_order = pewc_get_global_group_order();
	if( $group_order ) {
		$global_groups = explode( ',', $group_order );
		foreach ( $global_groups as $global_group_id ) {
			if ( $global_group_id == $group_id ) {
				continue;
			}
			$fields = pewc_get_group_fields( $global_group_id );
			if ( $fields ) {
				echo '<optgroup label="' . esc_attr( '(' . __( 'Global', 'pewc' ) . ') ' . pewc_get_group_title( $global_group_id, array(), true ) . ' #' . $global_group_id ) . '" data-group-id="' . esc_attr( $global_group_id ) . '" class="pewc-global-group-optgroup">';
				echo "\n";

				foreach( $fields as $field_id => $field ) {

					$field_label = ! empty( $field['field_admin_label'] ) ? $field['field_admin_label'] : ( ! empty( $field['field_label'] ) ? $field['field_label'] : '' );
					if ( ! empty( $field['field_id'] ) ) {
						$field_label .= ' [#' . $field['field_id'] . ']';
					}
					$classes = array( 'pewc-other-global-fields' );
					if ( pewc_field_allows_multiple( $field ) ) {
						$classes[] = 'pewc-has-multiple';
					}

					printf(
						'<option data-type="%s" value="%s" class="%s">%s</option>',
						esc_attr( $field['field_type'] ),
						esc_attr( $field['id'] ),
						esc_attr( implode( ' ', $classes ) ),
						esc_attr( $field_label ),
					);
					echo "\n";

				}

				echo '</optgroup>';
			}
		}
	}

}

/**
 * Outputs global groups used by a product as option groups in new-conditional-row.php
 * @since 4.4.0
 */
function pewc_output_global_groups_for_product( $product_id ) {

	// get the fields for this product
	$product_extra_groups = pewc_get_extra_fields( $product_id );
	// pewc_get_extra_fields() bypasses the filter below if we're in the admin, but we need it to get the global groups, so apply here
	$product_extra_groups = apply_filters( 'pewc_filter_product_extra_groups', $product_extra_groups, $product_id );
	if ( $product_extra_groups ) {
		foreach ( $product_extra_groups as $global_group_id => $fields ) {
			if ( 'pewc_group' !== get_post_type( $global_group_id ) || 0 !== wp_get_post_parent_id( $global_group_id ) || empty( $fields['items'] ) ) {
				// this is not a global group, skip
				continue;
			}

			// this is a global group
			echo '<optgroup label="' . esc_attr( '(' . __( 'Global', 'pewc' ) . ') ' . pewc_get_group_title( $global_group_id, array(), true ) . ' #' . $global_group_id ) . '" data-group-id="' . esc_attr( $global_group_id ) . '" class="pewc-global-group-optgroup">';
			echo "\n";

			foreach( $fields['items'] as $field_id => $field ) {

				$field_label = ! empty( $field['field_admin_label'] ) ? $field['field_admin_label'] : ( ! empty( $field['field_label'] ) ? $field['field_label'] : '' );
				if ( ! empty( $field['field_id'] ) ) {
					$field_label .= ' [#' . $field['field_id'] . ']';
				}
				$classes = array( 'pewc-other-global-fields' );
				if ( pewc_field_allows_multiple( $field ) ) {
					$classes[] = 'pewc-has-multiple';
				}

				printf(
					'<option data-type="%s" value="%s" class="%s">%s</option>',
					esc_attr( $field['field_type'] ),
					esc_attr( $field['id'] ),
					esc_attr( implode( ' ', $classes ) ),
					esc_attr( $field_label ),
				);
				echo "\n";

			}

			echo '</optgroup>';
		}
	}

}

/**
 * Check if field allows multiple selections. Similar to pewc_has_multiple() in admin-fields.js
 * @since 4.4.0
 */
function pewc_field_allows_multiple( $field ) {

	if( $field['field_type'] == 'products' || $field['field_type'] == 'product-categories' ) {
		if ( $field['products_layout'] == 'checkboxes' || $field['products_layout'] == 'checkboxes-list' || $field['products_layout'] == 'column' ) {
			return true;
		}
	} else if ( $field['field_type'] == 'checkbox_group' ) {
		return true;
	} else if( $field['field_type'] == 'image_swatch' ) {
		if( ! empty( $field['allow_multiple'] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Get field_options for a field, to be used in conditions
 * @since 4.4.0
 */
function pewc_ajax_add_condition_value_options() {

	if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'pewc_ajax_condition_nonce' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid nonce. Please refresh page then try again.', 'pewc' )
			),
			401
		);
		exit;
	}
	$options = array();
	if ( ! empty( $_POST['group_id'] ) && ! empty( $_POST['field_id'] ) ) {
		$field_id = (int) $_POST['field_id'];
		$field = pewc_create_item_object( $field_id );
		if ( ! empty( $field['field_type'] ) ) {
			if ( 'products' === $field['field_type'] && ! empty( $field['child_products'] ) ) {
				// always have blank option?
				$options[] = '';
				foreach ( $field['child_products'] as $child_product_id ) {
					$options[] = $child_product_id;
				}
			} else if ( ! empty( $field['field_options'] ) ) {
				// always have blank option?
				$options[] = '';
				foreach ( $field['field_options'] as $index => $option ) {
					// only save values?
					$options[] = ! empty( $option['value'] ) ? $option['value'] : '';
				}
			}
		}
	}
	wp_send_json( $options );
	exit;

}
add_action( 'wp_ajax_pewc_ajax_add_condition_value_options', 'pewc_ajax_add_condition_value_options' );
