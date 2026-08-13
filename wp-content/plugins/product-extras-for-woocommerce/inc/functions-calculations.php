<?php
/**
 * Functions for calculations
 * @since 3.5.0
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set our look up tables for calculation fields
 */
function pewc_calculation_look_up_tables() {

	if( ! is_product() && apply_filters( 'pewc_calculation_look_up_tables_product_page_only', true ) ) {
		return;
	}

	$tables = apply_filters( 'pewc_calculation_look_up_tables', array() );
	$fields = apply_filters( 'pewc_calculation_look_up_fields', array() ); ?>

		<script>
		var pewc_look_up_tables = <?php echo json_encode( $tables ); ?>;
		var pewc_look_up_fields = <?php echo json_encode( $fields ); ?>;
		</script>

	<?php

}
add_action( 'wp_head', 'pewc_calculation_look_up_tables' );

/**
 * Add additional values like formula for calculation fields
 * @since 3.11.4
 */
function pewc_update_cart_item_data_for_calc_fields( $cart_item_data, $item, $group_id, $field_id, $value ) {

	if ( ! pewc_enabled_calc_in_cart() ) return $cart_item_data;

	if ( $item['field_type'] == 'calculation' && ! empty( $item['formula'] ) && false !== strpos( $item['formula'], '{' ) && false !== strpos( $item['formula'], '}' ) ) {
		// add the field's formula to the cart item, in case we need to update price
		$cart_item_data['product_extras']['groups'][$group_id][$field_id]['formula'] = $item['formula'];
		$cart_item_data['product_extras']['groups'][$group_id][$field_id]['formula_action'] = $item['formula_action'];
		$cart_item_data['product_extras']['groups'][$group_id][$field_id]['decimal_places'] = $item['decimal_places'];

		// from templates/frontend/calculation.php
		preg_match_all( "|{field_(.*)}|U", $item['formula'], $all_fields, PREG_PATTERN_ORDER );
		if( ! empty( $all_fields[1] ) ) {
			$cart_item_data['product_extras']['groups'][$group_id][$field_id]['all_fields'] = $all_fields[1]; // array?
		}
	
		if ( '{look_up_table}' === $item['formula'] ) {
			// add the axes and source table
			$cart_item_data['product_extras']['groups'][$group_id][$field_id]['x_input'] = ! empty( $item['x_input'] ) ? $item['x_input'] : '';
			$cart_item_data['product_extras']['groups'][$group_id][$field_id]['y_input'] = ! empty( $item['y_input'] ) ? $item['y_input'] : '';
			$cart_item_data['product_extras']['groups'][$group_id][$field_id]['look_up_table'] = ! empty( $item['look_up_table'] ) ? $item['look_up_table'] : '';
		}

		if ( false !== strpos( $item['formula'], '{quantity}' ) ) {
			// we'll use this so that we know if an item has a calculation that needs updating
			if ( isset( $cart_item_data['product_extras']['quantity_dependent_calc_fields'] ) ) {
				$qdcf = $cart_item_data['product_extras']['quantity_dependent_calc_fields'];
			} else {
				$qdcf = array();
			}
			$qdcf[] = $field_id;
			$cart_item_data['product_extras']['quantity_dependent_calc_fields'] = $qdcf;
		}

	}

	return $cart_item_data;
}
add_filter( 'pewc_filter_end_add_cart_item_data', 'pewc_update_cart_item_data_for_calc_fields', 10, 5);

/**
 * If quantity is updated, recalculate calculation fields that depend on the quantity
 * @since 3.11.4
 */
function pewc_recalculate_calculation_fields_in_cart( $cart_item_key, $quantity, $old_quantity ) {

	if ( ! pewc_enabled_calc_in_cart() ) return; // do nothing

	$cart = WC()->cart->get_cart();

	if( empty( $cart[$cart_item_key]['product_extras']['quantity_dependent_calc_fields'] ) || $quantity == $old_quantity || $quantity < 1 ) {
		return; // do nothing
	}

	$cart_item = $cart[$cart_item_key];
	$product_extras = $cart_item['product_extras'];

	if ( empty( $product_extras['groups'] ) || ! is_array( $product_extras['groups'] ) ) {
		return; // product extras groups are empty, so we have nothing to loop through
	}

	// we need to recalculate the price because a calculation field depends on the quantity
	// in pewc.js, field values are replaced first (e.g. field_123, field_123_option_price, etc), then those inside brackets {}

	$groups = $product_extras['groups'];
	$original_price = $product_extras['original_price'];
	$add_on_price = 0;
	// quantity dependent calc fields is an array of field_ids
	$qdcf = $product_extras['quantity_dependent_calc_fields'];

	$other_values = array(); // save other add-on field values here (i.e. non-calc, global vars, etc)

	// set up global values
	$pewc_global_values = array(
		'quantity' => $quantity
	);

	// product values
	$product = $cart_item['data'];
	$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
	$pewc_global_values['product_price'] = $original_price;
	$pewc_global_values['product_width'] = $product->get_width();
	$pewc_global_values['product_length'] = $product->get_length();
	$pewc_global_values['product_height'] = $product->get_height();
	$pewc_global_values['product_weight'] = $product->get_weight();

	// AOU vars
	$pewc_global_values['variable_1'] = get_option( 'pewc_variable_1', 0 );
	$pewc_global_values['variable_2'] = get_option( 'pewc_variable_2', 0 );
	$pewc_global_values['variable_3'] = get_option( 'pewc_variable_3', 0 );

	// AOU global calc vars (custom vars by customer)
	$global_calc_vars = apply_filters( 'pewc_calculation_global_calculation_vars', false );
	if ( ! empty( $global_calc_vars ) && is_array( $global_calc_vars ) && count( $global_calc_vars ) > 1 ) {
		$pewc_global_values['global_calc_vars'] = $global_calc_vars;
	}

	// 4.4.0, allow Advanced Calculations to add ACF fields support
	$pewc_global_values = apply_filters( 'pewc_filter_calculation_global_values', $pewc_global_values, $product_id );

	// save global values inside $other_values so that we can pass them inside our function
	$other_values['pewc_global_values'] = $pewc_global_values;

	// save all fields in a "flat" array so that a field can access the value of another field in a different group
	// also, get all calculation components, as inspired by pewc_get_all_calculation_components()
	// we cannot use pewc_get_all_calculation_components() because the array structure is different
	$fields = array();
	$calc_components = array();
	foreach ( $groups as $group_id => $field ) {
		foreach ( $field as $field_id => $item ) {
			$fields[$field_id] = $item;

			// let's try to get calculation components
			if ( $item['type'] == 'calculation' ) {
				if ( '{look_up_table}' === $item['formula'] ) {
					if ( ! empty( $item['x_input'] ) ) {
						$component_id = $item['x_input'];
						if ( isset( $calc_components[$component_id] ) ) {
							$calc_components[$component_id][] = $field_id;
						}
						else {
							$calc_components[$component_id] = array( $field_id );
						}
					}
					if ( ! empty( $item['y_input'] ) ) {
						$component_id = $item['y_input'];
						if ( isset( $calc_components[$component_id] ) ) {
							$calc_components[$component_id][] = $field_id;
						}
						else {
							$calc_components[$component_id] = array( $field_id );
						}
					}
				}
				else if ( ! empty( $item['all_fields']) && is_array( $item['all_fields'] ) ) {
					foreach ( $item['all_fields'] as $component_id ) {
						$component_id = str_replace( array( '_option_price', '_field_price' ), '', $component_id );
						if ( isset( $calc_components[$component_id] ) ) {
							$calc_components[$component_id][] = $field_id;
						}
						else {
							$calc_components[$component_id] = array( $field_id );
						}
					}
				}
			}
		}
	}

	$all_replaced = false;
	//$counter = 0;

	// Loop through only our quantity-dependent calc fields, then use the calc components array to find other fields affected by the change
	// pewc_evaluate_calc_field_formula() is recursive
	foreach ( $qdcf as $field_id ) {
		if ( isset( $fields[$field_id] ) ) {
			if ( ! empty( $calc_components[$field_id] ) ) {
				// this calc field triggers another calc field, so add that to our array
				$fields[$field_id]['triggers'] = $calc_components[$field_id];
			}
			list( $fields, $other_values, $all_replaced ) = pewc_evaluate_calc_field_formula( $fields, $field_id, $other_values );
		}
	}

	if ( ! $all_replaced && function_exists('pewc_error_log') ) {
		// let's log this for now in case we need to debug
		$error_log = print_r($fields, true);
		pewc_error_log( $error_log );
	}

	// even if not all formulas have been replaced, save the updated $groups and get the updated price as well
	$calc_set_price = 0;
	foreach ( $fields as $field_id => $item ) {
		if ( ! empty( $item['price'] ) ) {
			if ( 'calculation' === $item['type'] && 'price' === $item['formula_action'] ) {
				$calc_set_price = $item['price'];
			}
			else {
				$add_on_price += $item['price'];
			}
		}
		if ( isset( $item['evaluated_formula'] ) ) {
			// unset this for future updating of cart
			unset( $item['evaluated_formula'] );
		}

		// save the updated group
		$groups[$item['group_id']][$field_id] = $item;
	}

	// save updated groups
	$product_extras['groups'] = $groups;

	if ( ! empty( $product_extras['use_calc_set_price'] ) ) {
		$product_extras['price_with_extras'] = $calc_set_price;
	}
	else {
		$product_extras['price_with_extras'] = $original_price + $add_on_price;
	}

	// update product_extras in cart
	WC()->cart->cart_contents[$cart_item_key]['product_extras'] = $product_extras;

}
add_action( 'woocommerce_after_cart_item_quantity_update', 'pewc_recalculate_calculation_fields_in_cart', 10, 3 );

/**
 * Validate that cart item quantities match the server-side result of any qty-action calculation fields.
 * Prevents a tampered quantity from reaching checkout.
 * @since 4.3.5
 */
function pewc_validate_qty_calculation_fields() {

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

		// Quick check: does this item have a qty calculation field at all?
		if ( ! pewc_cart_item_has_qty_calc_field( $cart_item ) ) {
			continue;
		}

		$expected_qty = pewc_get_qty_calculation_expected( $cart_item );
		$actual_qty   = (int) $cart_item['quantity'];

		if ( false !== $expected_qty && $actual_qty !== $expected_qty ) {
			wc_add_notice(
				sprintf(
					__( 'The quantity for &ldquo;%s&rdquo; is not valid and has been corrected.', 'pewc' ),
					$cart_item['data']->get_name()
				),
				'error'
			);
			WC()->cart->set_quantity( $cart_item_key, $expected_qty, false );
		}
	}
}
add_action( 'woocommerce_check_cart_items', 'pewc_validate_qty_calculation_fields' );
add_action( 'woocommerce_checkout_process', 'pewc_validate_qty_calculation_fields' );

/**
 * Blocks a cart quantity update before it is written to the session when the
 * item's quantity is controlled by a calculation field formula.  Without this,
 * a POST request crafted in the browser console can bypass the after-the-fact
 * correction in pewc_validate_qty_calculation_fields and set an arbitrary qty.
 * @since 4.3.7
 */
function pewc_block_qty_calculation_cart_update( $valid, $cart_item_key, $values, $quantity ) {
	if ( pewc_cart_item_has_qty_calc_field( $values ) ) {
		// Quantity is formula-controlled; reject any external change request.
		return false;
	}
	return $valid;
}
add_filter( 'woocommerce_update_cart_validation', 'pewc_block_qty_calculation_cart_update', 10, 4 );

/**
 * Returns true if the cart item's product has a qty-action calculation field,
 * or false otherwise.  Quantity-controlled items must not allow arbitrary changes.
 *
 * Resolves the product's own groups plus matching global groups directly via
 * pewc_filter_product_extra_groups(), bypassing the rest of the 'pewc_filter_product_extra_groups'
 * filter chain (see 4.3.18 note below) so that login/role-based conditional display rules
 * cannot hide the calculation field from this check.
 *
 * @param array $cart_item
 * @return bool
 * @since 4.3.7
 */
function pewc_cart_item_has_qty_calc_field( $cart_item ) {

	if ( empty( $cart_item['data'] ) || empty( $cart_item['product_extras']['groups'] ) ) {
		return false;
	}

	$product    = $cart_item['data'];
	$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

	// Per-product static cache (one check per product per request).
	static $cache = array();
	if ( isset( $cache[ $product_id ] ) ) {
		return $cache[ $product_id ];
	}

	// 4.3.18, resolve this product's own groups plus only the global groups whose targeting
	// rules/status/language actually match this product, instead of every global group on the
	// site. Previously a qty-action calc field in a global group that wasn't even applied to
	// this product would still block quantity updates for it.
	// We call pewc_filter_product_extra_groups() directly (the function, not apply_filters())
	// so we get that correct global group matching from pewc_filter_product_extra_groups() in
	// inc/functions-global-extras.php, but WITHOUT running the rest of the
	// 'pewc_filter_product_extra_groups' filter chain - specifically pewc_hide_groups_by_condition()
	// in inc/functions-conditionals.php, which hides groups/fields based on the current visitor's
	// login status or user role. If that ran here, a qty calc field could disappear from this
	// check for a visitor who doesn't currently meet its login/role condition, letting a crafted
	// quantity bypass the block. Calling the function directly keeps that hole closed while still
	// fixing the global-group targeting bug.
	if ( pewc_has_migrated() ) {
		$product_extra_groups = pewc_get_pewc_groups( $product_id );
	} else {
		$product_extra_groups = get_post_meta( $product_id, '_product_extra_groups', true );
	}

	$all_groups = pewc_filter_product_extra_groups( $product_extra_groups, $product_id );

	if ( empty( $all_groups ) || ! is_array( $all_groups ) ) {
		$cache[ $product_id ] = false;
		return false;
	}

	foreach ( $all_groups as $group ) {
		if ( empty( $group['items'] ) || ! is_array( $group['items'] ) ) {
			continue;
		}
		foreach ( $group['items'] as $item ) {
			if (
				! empty( $item['field_type'] ) && $item['field_type'] === 'calculation' &&
				! empty( $item['formula_action'] ) && $item['formula_action'] === 'qty'
			) {
				$cache[ $product_id ] = true;
				return true;
			}
		}
	}

	$cache[ $product_id ] = false;
	return false;
}

/**
 * Resolves the formula-computed quantity for a cart item, or returns false if the item
 * has no qty-action calculation field or the formula cannot be evaluated.
 *
 * @param array $cart_item
 * @return int|false
 * @since 4.3.7
 */
function pewc_get_qty_calculation_expected( $cart_item ) {

	if ( empty( $cart_item['data'] ) || empty( $cart_item['product_extras']['groups'] ) ) {
		return false;
	}

	$product    = $cart_item['data'];
	$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

	$all_groups = pewc_get_extra_fields( $product_id );
	if ( empty( $all_groups ) ) {
		return false;
	}

	// Build a flat fields array from the cart item's stored values (for formula resolution).
	$fields = array();
	foreach ( $cart_item['product_extras']['groups'] as $group_id => $group_fields ) {
		foreach ( $group_fields as $field_id => $item ) {
			$fields[ $field_id ] = $item;
		}
	}

	$original_price = isset( $cart_item['product_extras']['original_price'] ) ? $cart_item['product_extras']['original_price'] : 0;

	$pewc_global_values = array(
		'quantity'       => $cart_item['quantity'],
		'product_price'  => $original_price,
		'product_width'  => $product->get_width(),
		'product_length' => $product->get_length(),
		'product_height' => $product->get_height(),
		'product_weight' => $product->get_weight(),
		'variable_1'     => get_option( 'pewc_variable_1', 0 ),
		'variable_2'     => get_option( 'pewc_variable_2', 0 ),
		'variable_3'     => get_option( 'pewc_variable_3', 0 ),
	);

	// 4.4.0, allow Advanced Calculations to add ACF fields support
	$pewc_global_values = apply_filters( 'pewc_filter_calculation_global_values', $pewc_global_values, $product_id );

	$other_values = array(
		'pewc_global_values' => $pewc_global_values,
	);

	foreach ( $all_groups as $group ) {
		if ( empty( $group['items'] ) ) {
			continue;
		}
		foreach ( $group['items'] as $field_id => $item ) {
			if ( empty( $item['field_type'] ) || $item['field_type'] !== 'calculation' ) {
				continue;
			}
			if ( empty( $item['formula_action'] ) || $item['formula_action'] !== 'qty' ) {
				continue;
			}
			if ( empty( $item['formula'] ) ) {
				continue;
			}

			preg_match_all( '|{field_(.*)}|U', $item['formula'], $matched, PREG_PATTERN_ORDER );
			$fields[ $field_id ] = array(
				'type'          => 'calculation',
				'formula'       => $item['formula'],
				'formula_action'=> 'qty',
				'formula_round'	=> $item['formula_round'] ?? '', // 4.3.21
				'decimal_places'=> $item['decimal_places'] ?? '',
				'value'         => $item['value'] ?? '',
				'all_fields'    => $matched[1] ?? array(),
			);

			list( $fields, $other_values ) = pewc_evaluate_calc_field_formula( $fields, $field_id, $other_values );

			$eval_value = isset( $fields[ $field_id ]['value'] ) && is_numeric( $fields[ $field_id ]['value'] )
				? (float) $fields[ $field_id ]['value']
				: false;

			if ( false === $eval_value || $eval_value <= 0 ) {
				continue;
			}

			$expected_qty = (int) round( $eval_value );
			return apply_filters( 'pewc_validate_qty_calculation_expected', $expected_qty, $cart_item, $field_id );
		}
	}

	return false;
}

/**
 * Lock the Store API quantity limits for items controlled by a qty calculation field.
 * Sets min=max=current_qty so the Store API rejects any change request.
 * Falls back to the formula-evaluated quantity when available.
 * @since 4.3.7
 */
function pewc_store_api_qty_calculation_minimum( $value, $product, $cart_item ) {
	if ( empty( $cart_item ) ) {
		return $value;
	}
	$expected = pewc_get_qty_calculation_expected( $cart_item );
	if ( false !== $expected ) {
		return $expected;
	}
	// Formula couldn't be evaluated, but field is still qty-controlled: lock to current qty.
	if ( pewc_cart_item_has_qty_calc_field( $cart_item ) && ! empty( $cart_item['quantity'] ) ) {
		return (int) $cart_item['quantity'];
	}
	return $value;
}
add_filter( 'woocommerce_store_api_product_quantity_minimum', 'pewc_store_api_qty_calculation_minimum', 10, 3 );

/**
 * Lock the Store API maximum quantity to the formula result (or current qty as fallback).
 * @since 4.3.7
 */
function pewc_store_api_qty_calculation_maximum( $value, $product, $cart_item ) {
	if ( empty( $cart_item ) ) {
		return $value;
	}
	$expected = pewc_get_qty_calculation_expected( $cart_item );
	if ( false !== $expected ) {
		return $expected;
	}
	if ( pewc_cart_item_has_qty_calc_field( $cart_item ) && ! empty( $cart_item['quantity'] ) ) {
		return (int) $cart_item['quantity'];
	}
	return $value;
}
add_filter( 'woocommerce_store_api_product_quantity_maximum', 'pewc_store_api_qty_calculation_maximum', 10, 3 );

/**
 * Evaluates a single calc field or field with formulas in prices, recursive
 * @since 3.11.4
 */
function pewc_evaluate_calc_field_formula( $fields, $field_id, $other_values, $all_replaced = true, $traversed = array() ) {

	$item = $fields[$field_id];

	if ( empty( $item['evaluated_formula'] ) ) {
		$item['evaluated_formula'] = ''; // start with blank
		if ( 'calculation' === $item['type'] ) {
			$item['evaluated_formula'] = ! empty( $item['formula'] ) ? $item['formula'] : '';
		} else {
			// 4.4.0
			if ( ! empty( $item['field_price'] ) && pewc_price_has_formula( $item['field_price'] ) ) {
				$item['evaluated_formula'] = $item['field_price'];
			}
			if ( ! empty( $item['field_options'] ) ) {
				$all_fields = ! empty( $item['all_fields'] ) ? $item['all_fields'] : array();
				// check if each option price has a formula?
				foreach ( $item['field_options'] as $option_index => $option ) {
					//if ( pewc_price_has_formula( $option['price'] ) ) {
						// add this option to the field's entire formula, but we need the option's calculated price in case some used field_123_option_price
						// we also add this option even if it doesn't have a formula in case the field has a formula in its price?
						if ( empty( $item['evaluated_formula'] ) ) {
							$item['evaluated_formula'] = ! empty( $item['field_price'] ) ? $item['field_price'] : 0;
						}
						// ;;;pewc;;; maybe a unique splitter?
						$item['evaluated_formula'] .= ';;;pewc;;;' . $option['price'];
						// add this option's all_fields
						if ( ! empty( $option['all_fields'] ) ) {
							foreach( $option['all_fields'] as $opall ) {
								if ( ! in_array( $opall, $all_fields ) ) {
									$all_fields[] = $opall;
								}
							}
						}
					//}
				}
				$item['all_fields'] = $all_fields;
			}
		}
	}

	if ( pewc_formula_is_evaluated( $item['evaluated_formula'] ) ) {
		return array( $fields, $other_values, $traversed ); // the formula has been evaluated thoroughly, so we can skip this now
	}
	else if ( count( $traversed ) > 2 ) {
		return array( $fields, $other_values, $traversed ); // prevent infinite loop?
	}

	if ( ! pewc_formula_is_evaluated( $item['evaluated_formula'] ) && ! empty( $item['formula'] ) && '{look_up_table}' === $item['formula'] ) {
		// evaluate look up table... this should return evaluated_formula with the value found in the look up table
		// all_fields and pewc_global_values should be skipped, and we go directly to the bottom part of this functiom
		$item = pewc_evaluate_look_up_table( $item, $fields );
	}

	if ( ! empty( $item['all_fields'] ) && is_array( $item['all_fields'] ) ) {
		foreach ( $item['all_fields'] as $field_id2 ) {

			// reset vars
			$the_value = false;

			if ( isset( $other_values[$field_id2] ) ) {
				// a value has been set already for this, use that value. can be 0
				$the_value = $other_values[$field_id2];
			}
			else {
				// a value has not been set yet, get the value directly from $groups, and also save it in $other_values
				if ( strpos( $field_id2, '_option_value' ) !== false ) {

					// 4.4.0
					$clean_field_id = str_replace( '_option_value', '', $field_id2 );
					// we use field_option_price to determine that this is a valid field to use _option_value on?
					if ( ! empty( $fields[$clean_field_id]['field_option_price'] ) && is_numeric( $fields[$clean_field_id]['value'] ) ) {
						$the_value = $fields[$clean_field_id]['value'];
					}
					else $the_value = 0;

				} else if ( strpos( $field_id2, '_option_price' ) !== false ) {

					$clean_field_id = str_replace( '_option_price', '', $field_id2 );
					//if ( ! empty( $fields[$clean_field_id]['value_without_price']) ) {
						// value_without_price is usually used by fields with options, because 'value' would contain an HTML price
					//	$the_value = $fields[$clean_field_id]['value_without_price'];
					//}
					// 4.4.0, value_without_price could be text, causing error in WC_Eval_Math
					if ( ! empty( $fields[$clean_field_id]['field_option_price'] ) ) {
						$the_value = $fields[$clean_field_id]['field_option_price'];
					}
					else $the_value = 0;

				} else if ( strpos( $field_id2, '_field_price' ) !== false ) {

					$clean_field_id = str_replace( '_field_price', '', $field_id2 );
					if ( ! empty( $fields[$clean_field_id]['price'] ) ) {
						$the_value = $fields[$clean_field_id]['price'];
						if ( ! empty( $fields[$clean_field_id]['multiply'] ) && ! empty( $fields[$clean_field_id]['value'] ) && ( 'number' === $fields[$clean_field_id]['type'] || 'name_price' === $fields[$clean_field_id]['type'] ) ) {
							// 4.4.0, respect the multiply setting
							$the_value *= (float) $fields[$clean_field_id]['value'];
						}
					}
					else $the_value = 0;

				} else if ( strpos( $field_id2, '_number_uploads' ) !== false ) {

					$clean_field_id = str_replace( '_number_uploads', '', $field_id2 );
					$the_value = 0; // default to 0
					if ( isset( $fields[$clean_field_id] ) ) {
						$tmp_field = $fields[$clean_field_id];
						if ( $tmp_field['type'] == 'upload' && ! empty( $tmp_field['files'] ) && is_array( $tmp_field['files'] ) ) {
							$the_value = count( $tmp_field['files'] );
						}
					}

				} else if ( strpos( $field_id2, '_pdf_count' ) !== false ) {

					$clean_field_id = str_replace( '_pdf_count', '', $field_id2 );
					$the_value = 0; // default to 0
					if ( isset( $fields[$clean_field_id] ) ) {
						$tmp_field = $fields[$clean_field_id];
						if ( $tmp_field['type'] == 'upload' ) {
							if ( isset( WC()->session ) ){
								$uploaded_files = WC()->session->get( 'uploaded_files_' . $clean_field_id );
								if ( ! empty( $uploaded_files ) ) {
									$uploaded_files = json_decode( $uploaded_files, true );
									$pdf_count = 0;
									foreach( $uploaded_files as $index => $file ) {
										if ( ! empty( $file['pdf_count'] ) ) {
											$pdf_count += (int) $file['pdf_count'];
										}
									}
									$the_value = $pdf_count;
								}
							}
						}
					}

				} else if ( isset( $fields[$field_id2] ) ) {

					$tmp_field = $fields[$field_id2];
					if ( $tmp_field['type'] == 'calculation' && ( empty( $tmp_field['evaluated_formula'] ) || ! pewc_formula_is_evaluated( $tmp_field['evaluated_formula'] ) ) ) {
						// this is a calc field whose value hasn't been evaluated yet, so go deeper?
						if ( ! in_array( $field_id, $traversed ) ) {
							// we haven't passed this field, so ok to go deeper?
							list( $fields, $other_values, $all_replaced ) = pewc_evaluate_calc_field_formula( $fields, $field_id2, $other_values, $all_replaced, array_merge( $traversed, array( $field_id ) ) );
							// try again?
							$tmp_field = $fields[$field_id2];
							if ( ! empty( $tmp_field['evaluated_formula'] ) && pewc_formula_is_evaluated( $tmp_field['evaluated_formula'] ) ) {
								$the_value = $tmp_field['value']; // this is fully evaluated
							} else {
								// don't know what to do yet, but prevent infinite loop
							}
						} else {
							// don't know what to do yet, but prevent infinite loop
						}
					}
					else if ( ! empty( $tmp_field['value'] ) && is_numeric( $tmp_field['value'] ) ) {
						// this is a non-calc field, so safe to get the current value
						$the_value = $tmp_field['value'];
					}

				} else {
					// field was not found, default to zero
					$the_value = 0;
				}

				if ( false !== $the_value ) {
					$other_values[$field_id2] = $the_value; // save for later use
				}
			}

			if ( false !== $the_value ) {
				// replace it on the formula
				$item['evaluated_formula'] = str_replace( '{field_'.$field_id2.'}', $the_value, $item['evaluated_formula'] );
			} else {
				// value is still false
			}
		}
	}

	// now replace global values like quantity
	if ( is_array( $other_values['pewc_global_values'] ) && ! pewc_formula_is_evaluated( $item['evaluated_formula'] ) ) {
		foreach ( $other_values['pewc_global_values'] as $gkey => $gvalue ) {
			if ( ! is_array( $gvalue ) ) {
				$item['evaluated_formula'] = str_replace( '{'.$gkey.'}', $gvalue, $item['evaluated_formula'] );

			} else if ( $gkey == 'global_calc_vars' ) {
				// loop through the global_calc_vars, custom vars by customer using the filter "pewc_calculation_global_calculation_vars"
				foreach ( $gvalue as $gkey2 => $gvalue2 ) {
					$item['evaluated_formula'] = str_replace( '{'.$gkey2.'}', $gvalue2, $item['evaluated_formula'] );
				}
			}
		}
	}

	// check if evaluated formula still has unreplaced values
	if ( ! pewc_formula_is_evaluated( $item['evaluated_formula'] ) ) {
		$all_replaced = false; // set this to false
	}
	else {
		// all values in the formula has been replaced, update value?
		// to-do: what if this calc field only references number fields, could be a waste of computation
		include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';
		$eval_value = 0;
		$evaluated_formula = explode( ';;;pewc;;;', $item['evaluated_formula'] );
		foreach ( $evaluated_formula as $eval_index => $eval_for ) {
			// suppress PHP deprecated warning, WC_Eval_Math doesn't like parentheses in their formulas as of WC 10.8.1
			$evald = ! is_numeric( $eval_for ) ? @WC_Eval_Math::evaluate( $eval_for ) : $eval_for;
			$eval_value += $evald;
			//$evaluated_formula[$eval_index] = '(' . $evald . ')'; // we save it because we will pass it later?
		}

		if ( 'calculation' === $item['type'] && ! empty( $item['formula_action'] ) ) {
			// 4.3.21, round the value if needed to match the value passed from the frontend
			if ( ! empty( $item['formula_round'] ) ) {
				if ( 'ceil' === $item['formula_round'] ) {
					$eval_value = ceil( $eval_value );
				} else if ( 'floor' === $item['formula_round'] ) {
					$eval_value = floor( $eval_value );
				}
			}
			// set decimal place if it exists
			if ( ! empty( $item['decimal_places'] ) ) {
				$eval_value = wc_format_decimal( $eval_value, $item['decimal_places'] );
			}
			if ( $item['formula_action'] == 'cost' || $item['formula_action'] == 'price' ) {
				$item['price'] = $eval_value;
			}
			// Update Quantity needs this
			$item['value'] = $eval_value; // moved here so that we consider the changes by Calc fields
		} else {
			// for formulas in prices, we consider the decimal place from WC setting
			// we do result.toFixed( parseFloat( decimals ) ) in evaluate_formula in pewc.js, so we do something similar in PHP to avoid differences
			// number_format rounds numbers, so 4.155 becomes 4.16, but in pewc.js 4.155 becomes 4.15
			$eval_value = sprintf( '%.' . wc_get_price_decimals() . 'f', $eval_value );
		}

		$item['evaluated_formula'] = $eval_value; // used in comparing values for formulas in prices
		$other_values[$field_id] = $eval_value; // save this new value

		if ( ! empty( $item['triggers'] ) ) {
			// save this now so that the triggered fields can get the updated values
			$fields[$field_id] = $item;
			// this field triggers another field, so update those too...
			foreach ( $item['triggers'] as $field_id2 ) {
				if ( ! in_array( $field_id, $traversed ) ) {
					// we haven't passed this field, so ok to go deeper?
					list( $fields, $other_values, $all_replaced ) = pewc_evaluate_calc_field_formula( $fields, $field_id2, $other_values, $all_replaced, array_merge( $traversed, array( $field_id ) ) );
				} else {
					// don't know what to do yet, but prevent infinite loop
				}
			}
		}
	}

	$fields[$field_id] = $item;

	return array( $fields, $other_values, $all_replaced );
}

/**
 * Evaluate a look up table calculation field
 * @since 3.11.4
 */
function pewc_evaluate_look_up_table( $item, $fields ) {

	$null_signifier = apply_filters( 'pewc_look_up_table_null_signifier', '*' );
	$x_input = ! empty( $item['x_input'] ) ? $item['x_input'] : '';
	$y_input = ! empty( $item['y_input'] ) ? $item['y_input'] : '';
	$look_up_table = ! empty( $item['look_up_table'] ) ? $item['look_up_table'] : '';

	if ( empty ( $look_up_table ) ) {
		return $item;
	}

	$tables = apply_filters( 'pewc_calculation_look_up_tables', array() );

	if ( empty( $tables ) || ! isset( $tables[$look_up_table] ) || ! is_array( $tables[$look_up_table] ) ) {
		// table was not found, just go back
		return $item;
	}

	$table = $tables[$look_up_table];

	$x_field = isset( $fields[$x_input] ) ? $fields[$x_input] : false;
	$y_field = isset( $fields[$y_input] ) ? $fields[$y_input] : false;

	// to-do: maybe do recursive function again if x or y are not evaluated yet?
	if ( $x_field && $x_field['type'] == 'calculation' && ( empty( $x_field['evaluated_formula'] ) || ( ! empty( $x_field['evaluated_formula'] ) && ! pewc_formula_is_evaluated( $x_field['evaluated_formula'] ) ) ) ) {
		// the source field for x is not evaluated yet, so go back for now?
		return $item;
	}
	if ( $y_field && $y_field['type'] == 'calculation' && ( empty( $y_field['evaluated_formula'] ) || ( ! empty( $y_field['evaluated_formula'] ) && ! pewc_formula_is_evaluated( $y_field['evaluated_formula'] ) ) ) ) {
		// the source field for y is not evaluated yet, so go back for now?
		return $item;
	}
	// 4.4.0, added ! empty( $item['value'] )
	if ( $x_field && $y_field && $x_field['type'] != 'calculation' && $y_field['type'] != 'calculation' && ! empty( $item['value'] ) ) {
		// these look up table's axes are not referencing any calc fields, so maybe no chance the value has changed, so just evaluate to the current value?
		$item['evaluated_formula'] = $item['value'];
		return $item;
	}

	// default values for x and y, in case we can't find a value?
	$x_value = 0;
	$y_value = 0;

	if ( $x_field && isset( $x_field['value'] ) ) {
		$x_value = $x_field['value'];
	}
	if ( $y_field && isset( $y_field['value'] ) ) {
		$y_value = $y_field['value'];
	}

	if ( isset( $table[$x_value][$y_value] ) ) {
		// we already found our value, so update already and we don't need to proceed with the others anymore
		$item['value'] = $table[$x_value][$y_value];
		$item['evaluated_formula'] = $item['value'];
		return $item;
	}

	if ( isset( $table[$x_value] ) ) {
		// x was found in the table
		$x_axis = $table[$x_value];
	} else {
		// x was not found in the table
		$x_index = pewc_find_nearest_index_look_up_table( $x_value, $table );
		if ( isset( $table[$x_index] ) ) {
			$x_axis = $table[$x_index];
		}
	}

	if ( ! isset( $x_axis ) ) {
		// we can't find an x-axis for this somehow, so log for now then return
		pewc_error_log( $error_log );
		return $item;
	}

	if ( isset( $x_axis[$y_value] ) ) {
		// y was found on the x-axis, so we have a value
		$look_up_value = $x_axis[$y_value];
	} else {
		// y was not found on the x-axis
		$y_index = pewc_find_nearest_index_look_up_table( $y_value, $x_axis );
		if ( isset( $x_axis[$y_index] ) ) {
			$look_up_value = $x_axis[$y_index];
		}
	}

	if ( ! isset( $look_up_value ) || $look_up_value == $null_signifier ) {
		// just return again for now?
		return $item;
	}

	// now evaluate
	$item['evaluated_formula'] = $look_up_value;

	return $item;
}

/**
 * Find the nearest index for the look up table. Inspired by the one in pewc.js
 * @since 3.11.4
 */
function pewc_find_nearest_index_look_up_table( $value, $array ) {
	$keys = array_keys( $array );

	// do we need to sort the keys?

	if ( $value <= $keys[0] ) {
		$x_index = $keys[0];
	} else {
		// find the index
		for ( $i = 0; $i < count($keys); $i++ ) {
			if ( $value > $keys[$i] && isset( $keys[$i+1] ) && $value <= $keys[$i+1] ) {
				// Find the first key that is greater than the value passed in
				return $keys[$i+1];
			}
		}
		if ( $keys[count($keys)-1] == 'max') {
			return 'max';
		}
		// fallback?
		$x_index = $keys[count($keys)-1];
	}

	return $x_index;
}

/**
 * Check if the formula is fully evaluated, that is, no more variables in brackets
 * @since 3.11.4
 */
function pewc_formula_is_evaluated( $formula ) {
	if ( strpos( $formula, '{' ) === false && strpos( $formula, '}' ) === false) 
		return true;
	else return false;
}

/**
 * Check if calculation in the cart (if quantity is updated) is enabled. We do this for now while we're in beta?
 * @since 3.11.4
 */
function pewc_enabled_calc_in_cart() {
	return apply_filters( 'pewc_enable_calc_in_cart', 'yes' === get_option( 'pewc_recalculate_cart', 'no' ) );
}

/**
 * Check if we want to validate calculation fields and prices with formulas before they are added to the cart
 * @since 4.4.0
 */
function pewc_enabled_validate_formulas() {
	$enable_validate = 'yes' === get_option( 'pewc_validate_calculation_fields', 'no' ) && ! pewc_missing_requirements_validate_calculation_fields();
	return apply_filters( 'pewc_enable_validate_formulas', $enable_validate );
}

/**
 * Save some data into session for validation of formulas later
 * @since 4.4.0
 */
function pewc_save_data_for_formulas( $passed, $posted, $item, $product_id, $quantity, $variation_id=null ) {

	// if pewc_formulas_in_prices_enabled() is false, pewc_get_field_price() won't return the correct value
	// commented out pewc_formulas_in_prices_enabled() because data won't be saved if it's disabled, then validation won't run
	if ( ! isset( WC()->session ) || ! WC()->session->has_session() /*|| ! pewc_formulas_in_prices_enabled()*/ || ! pewc_enabled_validate_formulas() ) {
		return $passed;
	}

	if ( ! $passed ) {
		// this has already failed validation, don't continue
		WC()->session->__unset( 'pewc_data_for_formulas' );
		return $passed;
	}

	$product = wc_get_product( $product_id );
	if ( ! is_a( $product, 'WC_Product' ) ) {
		WC()->session->__unset( 'pewc_data_for_formulas' );
		return $passed;
	}

	if (
		empty( $item['id'] ) || 
		empty( $posted[$item['id']] ) || 
		empty( $item['field_type'] ) || 
		(
			'upload' === $item['field_type'] && 
			empty( $_FILES ) && 
			empty( $posted['pewc_file_data'][$item['field_id']] )
		) 
	) {
		// this field does not have a value, this means it can't affect calculation values, right?
		// don't clear the session because we may need them for other fields
		return $passed;
	}

	$data_for_formulas = WC()->session->get( 'pewc_data_for_formulas' );
	$has_formulas = false;
	$field = array();
	//$field['value'] = ! empty( $posted[$item['id']] ) ? $posted[$item['id']] : '';
	$field['value'] = $posted[$item['id']];

	if ( 'calculation' === $item['field_type'] && ! empty( $item['formula'] ) ) {
		$has_formulas = true;
		$field = $item; // I think we need everything for Calculation fields to be safe
		preg_match_all( "|{field_(.*)}|U", $item['formula'], $all_fields, PREG_PATTERN_ORDER );
		if( ! empty( $all_fields[1] ) ) {
			$field['all_fields'] = $all_fields[1]; // array?
		}
	} else {
		// either calculated price or prices with taxes?
		$field['price'] = (float) pewc_get_field_price( $item, wc_get_product( $product_id ), true );

		// check if fields have formulas in field prices or option prices or both
		if ( pewc_price_has_formula( $item['field_price'] ) ) {
			$has_formulas = true;
			preg_match_all( "|{field_(.*)}|U", $item['field_price'], $all_fields, PREG_PATTERN_ORDER );
			if( ! empty( $all_fields[1] ) ) {
				$field['all_fields'] = $all_fields[1]; // array?
			}
		}
		$field['field_price'] = $item['field_price'];

		if ( ! empty( $item['field_options'] ) ) {
			$option_value = ! empty( $posted[$item['id']] ) ? $posted[$item['id']] : '';
			if ( is_array( $option_value ) ) {
				$option_value = pewc_stripslashes_from_options( $option_value );
			} else {
				$option_value = stripslashes( $option_value );
			}
			$field_options = array();
			foreach ( $item['field_options'] as $option_index => $option ) {
				if ( ! empty( $option['price'] ) && 
					(
						( is_array( $option_value ) && in_array( $option['value'], $option_value ) ) || 
						( ! is_array( $option_value ) && $option['value'] == $option_value )
					)
				) {
					// this is a selected option, save all settings?
					$field_options[$option_index] = $option;
					// get option price. If this option has a formula, the calculated formula from $_POST is returned
					$option_price = pewc_get_option_price( $option, $item, $product, true, $option_index );
					// add to field price
					$field['price'] += $option_price;

					if ( 'select' === $item['field_type'] || 'radio' === $item['field_type'] ) {
						$field['field_option_price'] = $option_price;
						$field['field_option_price_original'] = $option['price']; // this could have a formula
					}

					$field_options[$option_index]['option_price'] = $option_price;
					if ( pewc_price_has_formula( $option['price'] ) ) {
						if ( ! $has_formulas ) $has_formulas = true;
						preg_match_all( "|{field_(.*)}|U", $option['price'], $all_fields, PREG_PATTERN_ORDER );
						if( ! empty( $all_fields[1] ) ) {
							$field_options[$option_index]['all_fields'] = $all_fields[1]; // array
						}
					}
				}
			}
			if ( ! empty( $field_options ) ) {
				$field['field_options'] = $field_options;
			}
		}

		// for uploads
		if ( 'upload' === $item['field_type'] ) {
			// we save it as 'files' so that it the evaluate function can count it for _number_uploads
			if ( 'yes' === pewc_enable_ajax_upload() && ! empty( $posted['pewc_file_data'][$item['field_id']] ) ) {
				$pewc_file_data = stripslashes( $posted['pewc_file_data'][$item['field_id']] );
				$field['files'] = json_decode( $pewc_file_data, true );
			} else if ( ! empty( $_FILES ) ) {
				$field['files'] = $_FILES;
			}
		}
	}

	if ( $has_formulas ) {
		if ( ! isset( $data_for_formulas['has_formulas'] ) ) {
			$data_for_formulas['has_formulas'] = true;
		}
		$field['has_formula'] = true;
		$field['field_label'] = ! empty( $item['field_label'] ) ? $item['field_label'] : $item['id'];
	}
	// fields list
	if ( ! empty( $data_for_formulas['fields'] ) ) {
		$fields = $data_for_formulas['fields'];
	} else {
		$fields = array();
	}
	$field['id'] = $item['id'];
	$field['type'] = $item['field_type'];
	$field['multiply'] = ! empty( $item['multiply'] ) ? $item['multiply'] : false;

	$fields[$item['field_id']] = $field;
	$data_for_formulas['fields'] = $fields;

	$data_for_formulas = apply_filters( 'pewc_filter_data_for_formulas', $data_for_formulas, $posted, $item, $product_id, $quantity, $variation_id );

	WC()->session->set( 'pewc_data_for_formulas', $data_for_formulas );

	return $passed;
}
add_filter( 'pewc_filter_validate_cart_item_status', 'pewc_save_data_for_formulas', 99, 6 );

/**
 * Validate all fields with formulas
 * @since 4.4.0
 */
function pewc_validate_cart_item_formulas( $passed, $product_id, $quantity, $variation_id=null, $cart_item_data=array() ) {

	if ( ! isset( WC()->session ) || ! WC()->session->has_session() /*|| ! pewc_formulas_in_prices_enabled()*/ || ! pewc_enabled_validate_formulas() ) {
		return $passed;
	}

	if ( ! $passed ) {
		// this has already failed validation, don't continue
		WC()->session->__unset( 'pewc_data_for_formulas' );
		return $passed;
	}

	$data_for_formulas = WC()->session->get( 'pewc_data_for_formulas' );
	if ( ! isset( $data_for_formulas['has_formulas'] ) || empty( $data_for_formulas['fields'] ) ) {
		WC()->session->__unset( 'pewc_data_for_formulas' );
		return $passed;
	}

	$product = wc_get_product( $product_id );
	if ( ! is_a( $product, 'WC_Product' ) ) {
		WC()->session->__unset( 'pewc_data_for_formulas' );
		return $passed;
	}

	$other_values = array(); // save other add-on field values here (i.e. non-calc, global vars, etc)

	// set up global values
	$pewc_global_values = array(
		'quantity' => $quantity
	);

	$original_price = $product->get_price();

	$pewc_global_values = array(
		'quantity'       => $quantity,
		'product_price'  => $original_price,
		'product_width'  => $product->get_width(),
		'product_length' => $product->get_length(),
		'product_height' => $product->get_height(),
		'product_weight' => $product->get_weight(),
		'variable_1'     => get_option( 'pewc_variable_1', 0 ),
		'variable_2'     => get_option( 'pewc_variable_2', 0 ),
		'variable_3'     => get_option( 'pewc_variable_3', 0 ),
	);

	// 4.4.0, allow Advanced Calculations to add ACF fields support. Also filtered by Bookings and Better Variations to process their own tags
	$pewc_global_values = apply_filters( 'pewc_filter_calculation_global_values', $pewc_global_values, $product_id );

	$other_values = array(
		'pewc_global_values' => $pewc_global_values,
	);

	// AOU global calc vars (custom vars by customer)
	$global_calc_vars = apply_filters( 'pewc_calculation_global_calculation_vars', false );
	if ( ! empty( $global_calc_vars ) && is_array( $global_calc_vars ) && count( $global_calc_vars ) > 1 ) {
		$other_values['pewc_global_values']['global_calc_vars'] = $global_calc_vars;
	}

	$fields = $data_for_formulas['fields'];
	$all_replaced = false;
	foreach ( $fields as $field_id => $field ) {
		if ( ! empty( $field['has_formula'] ) ) {
			list( $fields, $other_values, $all_replaced ) = pewc_evaluate_calc_field_formula( $fields, $field_id, $other_values );
		}
	}
	if ( $all_replaced ) {
		// check if all fields with formuls and calculation fields matches?
		foreach ( $fields as $field_id => $field ) {
			if ( 'calculation' === $field['type'] ) {
				if ( ! empty( $field['formula_action'] ) && ( 'cost' === $field['formula_action'] || 'price' === $field['formula_action'] ) ) {
					if ( empty( $_POST[$field['id']] ) || $_POST[$field['id']] != $field['price'] ) {
						// evaluated price is not the same as the posted price?
						pewc_add_calculation_notice( $field_id, $fields, $product );
						// clean up before returning
						//WC()->session->__unset( 'pewc_data_for_formulas' );
						// fail immediately
						//return false;
						$passed = false;
					}
				}
			} else if ( ! empty( $field['has_formula'] ) ) {
				// field or option price has formula
				if ( $field['evaluated_formula'] != $field['price'] ) {
					// evaluated price is not the same as the posted price?
					pewc_add_calculation_notice( $field_id, $fields, $product );
					// clean up before returning
					//WC()->session->__unset( 'pewc_data_for_formulas' );
					// fail immediately
					//return false;
					$passed = false;
				}
			}
		}
	} else {
		// find the fields that have unevaluated formulas and add an error message
		foreach ( $fields as $field_id => $field ) {
			if ( ! empty( $field['evaluated_formula'] ) && ! pewc_formula_is_evaluated( $field['evaluated_formula'] ) ) {
				pewc_add_calculation_notice( $field_id, $fields, $product );
				$passed = false;
			}
		}
	}

	// clean up before returning
	WC()->session->__unset( 'pewc_data_for_formulas' );

	return $passed;

}
add_filter( 'woocommerce_add_to_cart_validation', 'pewc_validate_cart_item_formulas', 99, 5 );

/**
 * Add an error notice if a calculation field or a field with formulas in prices fail validation
 * @since 4.4.0
 */
function pewc_add_calculation_notice( $field_id, $fields, $product ) {

	$field = $fields[$field_id];
	$message = apply_filters( 'pewc_filter_formula_validation_error_text',
		sprintf(
			__("There was an error in calculating the price for %s. Please contact the website administrator about the issue.", 'pewc' ),
			$field['field_label']
		),
		$field,
		$product
	);
	if ( ! wc_has_notice( $message, 'error' ) ) {
		wc_add_notice(
			$message,
			'error',
			array( 'pewc_field_id' => $field_id )
		);
		// log error for debugging
		pewc_error_log( 'Error in evaluating formulas in prices. Field:' . $field_id . ', All Fields:' . print_r($fields, true) . ', $_POST:' . print_r($_POST, true) );
	}

}

/**
 * Disable Validate Calculation fields checkbox if they need to update some plugins to the latest version
 * @since 4.4.0
 */
function pewc_maybe_disable_validate_calculation_fields( $settings ) {

	if ( ! empty( $settings['pewc_validate_calculation_fields'] ) ) {
		$update = pewc_update_requirements_validate_calculation_fields();

		if ( ! empty( $update ) ) {
			$settings['pewc_validate_calculation_fields']['desc'] .= __( ' The following plugin(s) need to be updated to the latest version in order to use this feature: ', 'pewc' );
			$settings['pewc_validate_calculation_fields']['desc'] .= implode( ', ', $update );
			$custom_attributes = ! empty( $settings['pewc_validate_calculation_fields']['custom_attributes'] ) ? $settings['pewc_validate_calculation_fields']['custom_attributes'] : array();
			if ( ! isset( $custom_attributes['disabled'] ) || $custom_attributes['disabled'] != 'disabled' ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			$settings['pewc_validate_calculation_fields']['custom_attributes'] = $custom_attributes;
		}
	}
	return $settings;

}
add_filter( 'pewc_calculations_settings', 'pewc_maybe_disable_validate_calculation_fields', 10, 1 );

/**
 * Check if we have plugins that need to be updated
 * @since 4.4.0
 */
function pewc_missing_requirements_validate_calculation_fields() {

	$update = pewc_update_requirements_validate_calculation_fields();
	
	return ! empty( $update );

}

/**
 * Get a list of installed plugins that are missing the required functions
 * @since 4.4.0
 */
function pewc_update_requirements_validate_calculation_fields() {

	$update = array();

	if ( defined( 'ACAOU_PLUGIN_VERSION' ) && ! function_exists( 'acaou_pewc_add_acf_fields' ) ) {
		$update[] = 'WooCommerce Product Add-Ons Ultimate Advanced Calculations';
	}
	if ( defined( 'WCPAUAU_PLUGIN_VERSION' ) && ! function_exists( 'wcpauau_save_pdf_page_count_to_session' ) ) {
		$update[] = 'WooCommerce Product Add-Ons Ultimate Advanced Uploads';
	}
	if ( defined( 'WCBVP_PLUGIN_VERSION' ) && ! function_exists( 'wcbvp_pewc_filter_calculation_global_values' ) ) {
		$update[] = 'WooCommerce Better Variations';
	}
	if ( defined( 'BFWC_PLUGIN_VERSION' ) && ! function_exists( 'bfwc_pewc_calculation_global_values' ) ) {
		$update[] = 'Bookings for WooCommerce';
	}

	return $update;

}
