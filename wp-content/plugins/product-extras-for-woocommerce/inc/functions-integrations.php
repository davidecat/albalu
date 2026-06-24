<?php
/**
 * Functions for integrating with various plugins
 * @since 2.2.0
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters the table row item data in the PIP plugin.
 *
 * @since 1.5.1
 * @param array $item_data The item data.
 * @param array $item WC_Order item meta.
 * @param WC_Product $product Product object.
 * @param int $order_id WC_Order ID.
 * @param string $document_type The document type.
 * @param \WC_PIP_Document $document The document object.
 */
function pewc_pip_document_table_row_item_data( $item_data, $item, $product, $order_id, $doc_type, $document ) {
	$data = $item->get_data();
	$meta_data = $data['meta_data'];
	foreach( $meta_data as $meta_item ) {
		if( $meta_item->key == 'product_extras' ) {
			$extras = $meta_item->value;
			if( isset( $extras['groups'] ) ) {
				foreach( $extras['groups'] as $group ) {
					foreach( $group as $item ) {
						$item_data['product'] .= $item['label'] . ': ' . $item['value'];
					}
				}
			}
		};
	}
	return $item_data;
}
add_filter( 'wc_pip_document_table_row_item_data', 'pewc_pip_document_table_row_item_data', 10, 6 );

function pewc_get_current_wpml_language() {
	if( ! defined( 'ICL_LANGUAGE_CODE' ) ) {
		return false;
	}
	return ICL_LANGUAGE_CODE;
}

function pewc_get_default_wpml_language() {
	if( ! defined( 'ICL_LANGUAGE_CODE' ) ) {
		return false;
	}
	global $sitepress;
	return $sitepress->get_default_language();
}

/**
 * Ensure image replacement works with different themes
 */
function pewc_product_img_wrap( $img_wrap ) {
	if( wp_get_theme()->template == 'porto' ) {
		$img_wrap = '.owl-item.active';
	} else if ( wp_get_theme()->template == 'blocksy' ) {
		// Blocksy uses ct-media-container instead of woocommerce-product-gallery__image
		$img_wrap = '.ct-media-container';
	}
	return $img_wrap;
}
add_filter( 'pewc_product_img_wrap', 'pewc_product_img_wrap' );

/**
 * Ensure product gallery selector works with different themes
 */
function pewc_product_gallery_wrap( $gallery ) {
	if ( wp_get_theme()->template == 'blocksy' ) {
		// Blocksy uses woocommerce-product-gallery instead of .images
		$gallery = '.woocommerce-product-gallery';
	}
	return $gallery;
}
add_filter( 'pewc_product_gallery', 'pewc_product_gallery_wrap' );

/**
 * Ensure image replacement / layering works with porto and blocksy
 * @since 3.21.4
 */
function pewc_layer_parent_porto( $class ) {
	if( wp_get_theme()->template == 'porto' ) {
		$class = 'product-image-slider .owl-item';
	} else if ( wp_get_theme()->template == 'blocksy' ) {
		$class = 'ct-product-gallery-container';
	}
	return $class;
}
add_filter( 'pewc_layer_parent', 'pewc_layer_parent_porto' );

/**
 * Add some data to the cart item for Aelia
 * @since 3.9.6
 */
function pewc_aelia_after_add_cart_item_data( $cart_item_data ) {

	// Save the selected currency
	$currency = pewc_aelia_get_selected_currency();
	$cart_item_data['product_extras']['aelia_currency'] = $currency;

	// We can revert to this data if the user switches back to this currency
	$cart_item_data['product_extras_' . $currency] = $cart_item_data['product_extras'];

	return $cart_item_data;

}
add_filter( 'pewc_after_add_cart_item_data', 'pewc_aelia_after_add_cart_item_data' );

/**
 * Filter the price for Aelia
 * @since 3.9.5
 */
function pewc_aelia_cs_convert( $amount, $item, $product=null, $from_currency='', $to_currency='' ) {

	if( ! $from_currency ) {
		$from_currency = pewc_aelia_get_from_currency();
	}
	if( ! $to_currency ) {
		$to_currency = pewc_aelia_get_to_currency();
	}

	return apply_filters( 'wc_aelia_cs_convert', $amount, $from_currency, $to_currency );

}
add_filter( 'pewc_filter_field_price', 'pewc_aelia_cs_convert', 10, 3 );

/**
 * Filter option prices for Aelia, skipping conversion for 'Value Only' options
 * (used in calculation fields where the price is a multiplier, not a currency amount)
 * @since 4.1.1
 */
function pewc_aelia_cs_convert_option_price( $amount, $item, $product=null, $from_currency='', $to_currency='' ) {

	// Don't convert option prices that are 'Value only'
	if( isset( $item['option_price_visibility'] ) && $item['option_price_visibility'] === 'value' ) {
		return $amount;
	}

	if( ! $from_currency ) {
		$from_currency = pewc_aelia_get_from_currency();
	}
	if( ! $to_currency ) {
		$to_currency = pewc_aelia_get_to_currency();
	}

	return pewc_aelia_cs_convert( $amount, $item, $product );
}
add_filter( 'pewc_filter_option_price', 'pewc_aelia_cs_convert_option_price', 10, 5 );

function pewc_aelia_get_from_currency() {
	return pewc_get_default_currency(); // 4.1.2, use the new function. Keep pewc_aelia_get_from_currency() for backwards compatibility?
}

function pewc_aelia_get_to_currency() {
	return pewc_get_current_currency(); // 4.1.2, use the new function. Keep pewc_aelia_get_to_currency() for backwards compatibility?
}

function pewc_aelia_get_selected_currency() {
	if( ! class_exists( 'WC_Aelia_CurrencySwitcher' ) ) return false;
	return WC_Aelia_CurrencySwitcher::instance()->get_selected_currency();
}

/**
 * Check if currency has changed in Aelia CS
 * If so, convert add-on prices
 * @since 3.9.5
 */
function pewc_aelia_get_cart_item_from_session( $cart_item, $values ) {

	if( ! class_exists( 'Aelia_WC_AFC_RequirementsChecks' ) ) {
		return $cart_item;
	}

	// Check if Aelia CS is active
	if( ! class_exists( 'WC_Aelia_CurrencySwitcher' ) ) {
		return $cart_item;
	}

	if( ! isset( $cart_item['product_extras']['aelia_currency'] ) ) {
		return $cart_item;
	}

	$selected_currency = pewc_aelia_get_selected_currency();
	$from_currency = pewc_aelia_get_from_currency();
	$to_currency = pewc_aelia_get_to_currency();

	// We have to convert add-on prices
	if( isset( $cart_item['product_extras_' . $to_currency] ) ) {

		// First, if add-on data for the 'to currency' already exists, then just switch back
		$cart_item['product_extras'] = $cart_item['product_extras_' . $to_currency];
		if( isset ( $cart_item['product_extras_' . $to_currency]['price_with_extras'] ) ) {
			$cart_item['product_extras']['price_with_extras'] = $cart_item['product_extras_' . $to_currency]['price_with_extras'];
		}
		if( isset ( $cart_item['product_extras_' . $to_currency]['original_price'] ) ) {
			$cart_item['product_extras']['original_price'] = $cart_item['product_extras_' . $to_currency]['original_price'];
		}

	} else if( isset( $cart_item['product_extras']['groups'] ) ) {

		// Ensure we are converting from the correct currency
		$from_currency = $cart_item['product_extras']['aelia_currency'];

		// We have to iterate through every price and convert to the new currency
		foreach( $cart_item['product_extras']['groups'] as $group_id=>$group ) {
			foreach( $group as $field_id=>$field ) {
				if( ! empty( $field['price'] ) ) {
					// Convert the add-on price
					$cart_item['product_extras']['groups'][$group_id][$field_id]['price'] = pewc_aelia_cs_convert( $field['price'], $field, null, $from_currency, $to_currency );
				}
			}
			
		}
		if( ! empty( $cart_item['product_extras']['price_with_extras'] ) ) {
			$cart_item['product_extras']['price_with_extras'] = pewc_aelia_cs_convert( $cart_item['product_extras']['price_with_extras'], $field, null, $from_currency, $to_currency );
		}
		if( ! empty( $cart_item['product_extras']['original_price'] ) ) {
			$cart_item['product_extras']['original_price'] = pewc_aelia_cs_convert( $cart_item['product_extras']['original_price'], $field, null, $from_currency, $to_currency );
		}
		
		// Save a copy at the end so that we can switch back
		if( ! isset( $cart_item['product_extras_' . $to_currency] ) ) {
			$cart_item['product_extras_' . $to_currency] = $cart_item['product_extras'];
			if( ! empty( $cart_item['product_extras']['price_with_extras'] ) ) {
				$cart_item['product_extras_' . $to_currency]['price_with_extras'] = $cart_item['product_extras']['price_with_extras'];
			}
			if( ! empty( $cart_item['product_extras']['original_price'] ) ) {
				$cart_item['product_extras_' . $to_currency]['original_price'] = $cart_item['product_extras']['original_price'];
			}
		}

		// Update our current currency
		$cart_item['product_extras']['aelia_currency'] = $to_currency;

	}

	return $cart_item;

}
add_filter( 'woocommerce_get_cart_item_from_session', 'pewc_aelia_get_cart_item_from_session', 10, 2 );

/**
 * Duplicates groups and fields for translated products in Polylang. Original product should have duplicated groups and fields by this time.
 * @since 3.12.2
 */
function pewc_pll_integration_product_duplicate( $duplicate, $product ) {
	if ( 'yes' != pewc_pll_integration_enabled() ) {
		return;
	}

	// check that this function exists
	if ( function_exists( 'pll_get_post_translations' ) ) {
			$new_id = $duplicate->get_id();
			$old_id = $product->get_id();
			$new_trs = pll_get_post_translations( $new_id );
			$old_trs = pll_get_post_translations( $old_id );

			// Are we duplicating groups and fields, i.e. cloning them and assigning new IDs
			$do_duplication = apply_filters( 'pewc_duplicate_fields', true, false );

			if ( ! empty( $new_trs ) && $do_duplication ) {
					foreach ( $new_trs as $lang => $id ) {
							// we only duplicate fields for the translated products
							if ( $id != $new_id ) {
									// default source is the original parent product
									$source = $product;
									if ( ! empty( $old_trs[$lang] ) && 'product' == get_post_type( $old_trs[$lang] ) ) {
											// the source of the add-ons would be the translated product
											$source = wc_get_product( $old_trs[$lang] );
									}
									pewc_duplicate_groups_and_fields( wc_get_product( $id ), $source, true );
									update_post_meta( $id, 'pewc_pll_duplicated_fields', 'yes' );
							}
					}
			}
	}
}
add_action( 'woocommerce_product_duplicate', 'pewc_pll_integration_product_duplicate', 11, 2 );

/**
 * When creating a new translation for a product using Polylang, the function below duplicates the add-on groups and fields
 * @since 3.12.2
 */
function pewc_pll_integration_duplicate_fields_on_new_translation( $from, $to, $lang ) {
	if ( 'yes' != pewc_pll_integration_enabled() ) {
		return;
	}

	if ( isset( $GLOBALS['pagenow'], $_GET['from_post'], $_GET['new_lang'] ) && 'post-new.php' === $GLOBALS['pagenow'] ) {
			// Are we duplicating groups and fields, i.e. cloning them and assigning new IDs
			$do_duplication = apply_filters( 'pewc_duplicate_fields', true, false );

			if( $do_duplication ) {
					pewc_duplicate_groups_and_fields( wc_get_product( $to ), wc_get_product( $from ), true );
					update_post_meta( $to, 'pewc_pll_duplicated_fields', 'yes' );
			}

			update_option( 'pewc_duplication_notice', 1 );
	}
}
add_action( 'pllwc_copy_product', 'pewc_pll_integration_duplicate_fields_on_new_translation', 11, 4 );

/**
 * When creating a new translation for a Global Group, duplicate groups and fields. Must use Global Groups (edit group as post type)
 * @since 3.12.2
 */
function pewc_pll_integration_duplicate_global_group_fields( $post_ID, $post, $update ) {
	if ( 'yes' != pewc_pll_integration_enabled() ) {
		return;
	}

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
		return;
	}

	if ( $post->post_type == 'pewc_group' && $post->post_parent == 0 && $GLOBALS['pagenow'] == 'post-new.php' && isset( $_GET['post_type'], $_GET['from_post'], $_GET['new_lang'] ) && $_GET['post_type'] == 'pewc_group' ) {
			// a new translation for a global group has been created, create new field IDs. Idea taken from pewc_duplicate_group()
			$old_group_id = (int) $_GET['from_post'];
			$old_group = get_post( $old_group_id );

			if ( $old_group->post_type == 'pewc_group' && $old_group->post_parent == 0 ) {
					// let's only do this for global groups
					// Check if the duplicated group has fields
					$duplicated_fields = get_post_meta( $old_group_id, 'field_ids', true );
					$mapped_fields = array();

					if( $duplicated_fields ) {

							// Duplicate each field
							foreach( $duplicated_fields as $old_field_id ) {

									$new_field_id = pewc_duplicate_field_by_id( $old_field_id );
									// Make an array to map old field IDs to their duplicated versions
									$mapped_fields[$old_field_id] = $new_field_id;

							}

					}

					$updated = update_post_meta( $post_ID, 'field_ids', array_values( $mapped_fields ) );
					update_post_meta( $post_ID, 'pewc_pll_duplicated_fields', 'yes' );

					if ( ! empty( $mapped_fields ) ) {
							$new_field_ids = array_values( $mapped_fields );
							// find calculation fields and update their formulas with new field IDs if needed
							foreach ( $new_field_ids as $fid ) {
									$ftype = get_post_meta( $fid, 'field_type', true );
									if ( 'calculation' === $ftype ) {
											// this is a calculation field, let's try to replace the fields in the formula if they exist
											$formula = get_post_meta ( $fid, 'formula', true );
											if ( false !== strpos( $formula, '{field_' ) ) {
													// the formula has fields that we can replace
													$new_formula = $formula;
													foreach ( $mapped_fields as $ofid => $nfid ) {
															$new_formula = str_replace( '{field_'.$ofid.'}', '{field_'.$nfid.'}', $new_formula );
															$new_formula = str_replace( '{field_'.$ofid.'_', '{field_'.$nfid.'_', $new_formula );
													}
													update_post_meta( $fid, 'formula', sanitize_text_field( $new_formula ) );
											}
									}
							}
					}
			}
	}
}
add_action( 'wp_insert_post', 'pewc_pll_integration_duplicate_global_group_fields', 100, 3 );

/**
 * Prevents field_ids from being copied to the new translated Global Groups
 * @since 3.12.2
 */
function pewc_pll_integration_prevent_post_metas( $keys, $sync, $from, $to, $lang ) {
	if ( 'yes' != pewc_pll_integration_enabled() ) {
		return $keys;
	}

	$new_keys = array();
	foreach ( $keys as $key ) {
			if ( $key != 'field_ids' ) {
					// don't copy field_ids because it overwrites our changes
					$new_keys[] = $key;
			}
	}

	return $new_keys;
}
add_filter( 'pll_copy_post_metas', 'pewc_pll_integration_prevent_post_metas', 100, 5 );

/**
 * Toggle Polylang compatibility. Disabled by default, so that users who are already using our snippets won't encounter issues.
 * @since 3.12.2
 */
function pewc_pll_integration_enabled() {
	return get_option( 'pewc_pll_integration_enable', 'no' );
}

/**
 * Validate child products against Min Max settings
 * @since 3.13.1
 */
function pewc_wcmmqo_validate_child_products( $passed, $post, $item ) {

	if ( ! apply_filters( 'pewc_wcmmqo_disable_validate_child_products', false ) && function_exists( 'wcmmqo_validate_item' ) && isset( $item['field_type'] ) && ( $item['field_type'] == 'products' || $item['field_type'] == 'product-categories' ) && ! empty( $item['child_products'] ) ) {

		$child_products = $item['child_products'];
		$id = $item['id'];

		foreach( $child_products as $key=>$child_product_id ) {

			if ( empty( $post[$id . '_child_product'] ) ) {
				continue;
			}

			if( ! is_array( $post[$id . '_child_product'] ) ) {
				$post[$id . '_child_product'] = array( $post[$id . '_child_product'] );
			}

			if( isset( $post[$id . '_child_product'] ) && in_array( $child_product_id, $post[$id . '_child_product'] ) ) {

				$child_product = wc_get_product( $child_product_id );
				$products_qty_in_cart = WC()->cart->get_cart_item_quantities();

				// Check the quantity
				if( $item['products_quantities'] == 'independent' ) {
					$item_quantity = isset( $post[$id . '_child_quantity_' . $child_product_id] ) ? $post[$id . '_child_quantity_' . $child_product_id] : 1;
				} else if( $item['products_quantities'] == 'linked' ) {
					$item_quantity = $post['quantity'];
				} else if( $item['products_quantities'] == 'one-only' ) {
					$item_quantity = 1;
				}

				if( isset( $products_qty_in_cart[$child_product_id] ) ) {
					$item_quantity += $products_qty_in_cart[$child_product_id];
				}

				$item_quantity = (int) $item_quantity; // ensure this is an integer

				// Get all item rules
				$item_rules = wcmmqo_get_item_rules();
				$items_total = 0; // This is the combined price of all items that have a rule
				$user_roles = wcmmqo_get_current_user_roles();
				$item_value = $child_product->get_price() * $item_quantity;

				// Check if this item has a rule applied
				$product_rules = wcmmqo_check_all_rules( $item_rules, $child_product, $user_roles );

				$passed = wcmmqo_validate_item( $passed, $product_rules, $child_product, $item_value, $item_quantity );

			}

		}

	}

	return $passed;
}
add_filter( 'pewc_filter_validate_cart_item_status', 'pewc_wcmmqo_validate_child_products', 10, 3 );

/**
 * Disable "wcmmqo_validate_item" in wcmmqo_before_calculate_totals if validation of Min Max on child products is disabled
 * @since 3.13.1
 */
function pewc_wcmmqo_disable_validate_item_before_calculate_totals( $disable, $cart_item_key, $cart_item_data ) {

	if ( apply_filters( 'pewc_wcmmqo_disable_validate_child_products', false ) && ! empty( $cart_item_data['product_extras']['products']['child_field'] ) ) {
		$disable = true;
	}

	return $disable;
}
add_filter( 'wcmmqo_disable_validate_item_before_calculate_totals', 'pewc_wcmmqo_disable_validate_item_before_calculate_totals', 10, 3 );

/**
 * For Min Max Quantity Order. Pass back to the product page the value of the quantity in the cart when editing add-ons
 * @since 3.24.2
 */
function pewc_wcmmqt_edit_default_quantity( $default_quantity, $product ) {

	if ( ! pewc_user_can_edit_products() || ! is_product() ) {
		return $default_quantity;
	}

	$cart_key = ! empty( $_GET['pewc_key'] ) ? $_GET['pewc_key'] : false;

	if ( false === $cart_key ) {
		return $default_quantity;
	}

	$cart = WC()->cart->cart_contents;

	if( isset( $cart[$cart_key]['quantity'] ) ) {
		// This product has already been added to the cart, so we're now editing it
		$default_quantity = $cart[$cart_key]['quantity'];
	}

	return $default_quantity;
}
add_filter( 'wcmmqt_default_quantity', 'pewc_wcmmqt_edit_default_quantity', 11, 2 );

/**
 * Add a class if a field has options. Used by WooCommerce Dynamic Pricing and Discount Rules
 * @since 3.21.4
 */
function pewc_wcfad_field_has_options( $classes, $item ) {

	if ( function_exists( 'wcfad_get_regular_price' ) && ! empty( $item['field_options'] ) ) {
		$classes[] = 'pewc-wcfad-has-options';
	}
	return $classes;
}
add_filter( 'pewc_filter_single_product_classes', 'pewc_wcfad_field_has_options', 11, 2 );

/**
 * Additional hidden fields for compatibility with Dynamic Pricing and Discount Rules
 * @since 3.21.4
 */
function pewc_wcfad_hidden_fields( $post_id, $product, $summary_panel ) {

	if ( function_exists( 'wcfad_get_regular_price' ) ) {
		echo '<input type="hidden" id="pewc-wcfad-total-child-product-quantity" value="0" data-qty-checked="0">';
	}

}
add_action( 'pewc_after_group_wrap', 'pewc_wcfad_hidden_fields', 10, 3 );

/**
 * Disable Dynamic Pricing and Discount Rules (bulk rules) on Add-On fields
 * @since 3.21.5
 */
function pewc_wcfad_get_disable_on_addons( $disable, $product_id ) {

	$disable = get_option( 'pewc_disable_wcfad_on_addons', 'no' ) === 'yes' ? true : false;

	return $disable;

}
add_filter( 'pewc_disable_wcfad_on_addons', 'pewc_wcfad_get_disable_on_addons', 1, 2 );

/**
 * Get default currency as set in WooCommerce
 * @since 4.1.2
 */
function pewc_get_default_currency() {
	return get_option( 'woocommerce_currency' );
}

/**
 * Get current currency, may be filtered by other plugins like Aelia or FOX
 * @since 4.1.2
 */
function pewc_get_current_currency() {
	return get_woocommerce_currency();
}

/**
 * Allow FOX Currency Switcher to filter our field prices on the frontend product pages
 * @since 4.1.2
 */
function pewc_woocs_filter_field_price( $amount, $item, $product=null, $from_currency='', $to_currency='' ) {

	$amount = apply_filters( 'woocs_exchange_value', $amount );
	return $amount;

}
add_filter( 'pewc_filter_field_price', 'pewc_woocs_filter_field_price', 11, 3 );

/**
 * Allow FOX Currency Switcher to filter our option prices on the frontend product pages
 * @since 4.1.2
 */
function pewc_woocs_filter_option_price( $amount, $item, $product=null, $from_currency='', $to_currency='' ) {

	$amount = apply_filters( 'woocs_exchange_value', $amount );
	return $amount;

}
add_filter( 'pewc_filter_option_price', 'pewc_woocs_filter_option_price', 11, 5 );

/**
 * Function to convert prices back to the default currency before saving to the cart. FOX Currency Switcher seems to assume that all prices in the cart are using the default currency
 * So if a customer adds a product in a different currency other than default, we convert them back first
 * @since 4.1.2
 */
function pewc_woocs_after_add_cart_item_data( $cart_item_data ) {

	global $WOOCS;
	if ( ! isset( $WOOCS ) ) {
		return $cart_item_data;
	}

	$current_currency = pewc_get_current_currency();
	$default_currency = pewc_get_default_currency();

	if ( $current_currency === $default_currency ) {
		// no need to convert?
		return $cart_item_data;
	}

	// We have to iterate through every price and convert to the default currency. WOOCS converts them to the current currency on display
	foreach( $cart_item_data['product_extras']['groups'] as $group_id=>$group ) {
		foreach( $group as $field_id=>$field ) {
			if( ! empty( $field['price'] ) ) {
				// Convert the add-on price
				$price = $cart_item_data['product_extras']['groups'][$group_id][$field_id]['price'];
				$cart_item_data['product_extras']['groups'][$group_id][$field_id]['price'] = apply_filters( 'woocs_back_convert_price', $price );
			}
		}
		
	}
	if( ! empty( $cart_item_data['product_extras']['price_with_extras'] ) ) {
		$price = $cart_item_data['product_extras']['price_with_extras'];
		$cart_item_data['product_extras']['price_with_extras'] = apply_filters( 'woocs_back_convert_price', $price );
	}
	if( ! empty( $cart_item_data['product_extras']['original_price'] ) ) {
		$price = $cart_item_data['product_extras']['original_price'];
		$cart_item_data['product_extras']['original_price'] = apply_filters( 'woocs_back_convert_price', $price );
	}

	return $cart_item_data;

}
add_filter( 'pewc_after_add_cart_item_data', 'pewc_woocs_after_add_cart_item_data', 11, 1 );


/**
 * @since   4.3.4
 * Filter container class for Image and Text Previews for non-standard themes
 */
function pewc_preview_gallery_container( $gallery_container, $post ) {

	if( wp_get_theme()->template == 'porto' ) {
		$gallery_container = 'product-images';
	} else if ( wp_get_theme()->template == 'shoptimizer' && defined( 'CGKIT_BASE_PATH' ) ) {
		// Updated because the cgkit-one-slider class is only applied by the CG Kit plugin
		$gallery_container = 'cgkit-one-slider';
	}
	return $gallery_container;
}
add_filter( 'aipaou_gallery_container', 'pewc_preview_gallery_container', 10, 2 );
add_filter( 'apaou_gallery_container', 'pewc_preview_gallery_container', 10, 2 );
add_filter( 'apaou_field_gallery_container', 'pewc_preview_gallery_container', 10, 2 );

/**
 * @since   1.0.0
 * Filter layer parent class for Image and Text Previews for non-standard themes
 */
function pewc_preview_layer_parent( $layer_parent, $post ) {

	if( wp_get_theme()->template == 'porto' ) {
		$layer_parent = 'product-image-slider';
	} else if ( wp_get_theme()->template == 'shoptimizer' && defined( 'CGKIT_BASE_PATH' ) ) {
		// Updated because the swiper-wrapper class is only applied by the CG Kit plugin
		$layer_parent = 'swiper-wrapper';
	} else if ( wp_get_theme()->template == 'blocksy' ) {
		// ct-product-gallery-container has position:relative and wraps both single and multi-image galleries
		$layer_parent = 'ct-product-gallery-container';
	}
	return $layer_parent;
}
add_filter( 'aipaou_layer_parent', 'pewc_preview_layer_parent', 10, 2 );
add_filter( 'apaou_layer_parent', 'pewc_preview_layer_parent', 10, 2 );
add_filter( 'apaou_field_layer_parent', 'pewc_preview_layer_parent', 10, 2 );

/**
 * Fix for issue with WooCommerce Paypal Payments where it continually triggers woocommerce_add_to_cart,
 * which means pewc_add_to_cart() and pewc_add_on_product() are called repeatedly. Child products are then added repeatedly
 * @since 4.3.5
 */
function pewc_wcpp_is_simulating_cart() {
	if ( apply_filters( 'woocommerce_paypal_payments_is_simulating_cart', false ) ) {
		// don't trigger our own action if WCPP is just simulating the cart
		remove_action( 'woocommerce_add_to_cart', 'pewc_add_to_cart', 10, 6 );
	}
}
add_action( 'woocommerce_add_to_cart', 'pewc_wcpp_is_simulating_cart', 1 );

/**
 * Flatsome theme includes its own tooltip library which conflicts with ours
 */
function pewc_dequeue_tooltips( $dequeue ) {
	if( wp_get_theme()->template == 'flatsome' ) {
		$dequeue = true;
	}
	return $dequeue;
}
add_filter( 'pewc_dequeue_tooltips', 'pewc_dequeue_tooltips' );
