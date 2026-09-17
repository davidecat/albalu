<?php
/**
 * A products field template for the variable-select layout
 *
 * Displays variable child products in a select field. When a variable product is
 * chosen, a second select field below is populated with that product's variations.
 * When a variation is selected, its thumbnail / price / stock / short description
 * are shown, according to the field settings.
 *
 * @since	4.4.4
 * @version	4.4.4
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! pewc_is_pro() ) {
	return;
}

$products_quantities = ! empty( $item['products_quantities'] ) ? $item['products_quantities'] : '';
$post_id             = ! empty( $post_id ) ? $post_id : ( ! empty( $product ) && is_object( $product ) ? $product->get_id() : 0 );

// Display options. Each defaults to enabled when the setting has never been saved.
$vs_show = array();
foreach( array( 'vs_show_thumbnail', 'vs_show_price', 'vs_show_stock', 'vs_show_description' ) as $vs_key ) {
	$vs_show[ $vs_key ] = ( ! isset( $item[ $vs_key ] ) || '' === $item[ $vs_key ] ) ? true : ! empty( $item[ $vs_key ] );
}
$vs_show = apply_filters( 'pewc_variable_select_display_options', $vs_show, $item, $post_id );

$child_product_wrapper_class = array( 'child-product-wrapper', 'pewc-variable-select-wrapper' );
if( $products_quantities ) {
	$child_product_wrapper_class[] = 'products-quantities-' . $products_quantities;
}

// Work out which variable product / variation is currently selected (e.g. when editing a cart item)
$selected_product_id   = 0;
$selected_variation_id = 0;
if( ! empty( $value ) ) {
	$selected_value   = is_array( $value ) ? reset( $value ) : $value;
	$selected_product = wc_get_product( $selected_value );
	if( $selected_product ) {
		if( $selected_product->is_type( 'variation' ) ) {
			$selected_variation_id = $selected_product->get_id();
			$selected_product_id   = $selected_product->get_parent_id();
		} else if( $selected_product->is_type( 'variable' ) ) {
			$selected_product_id = $selected_product->get_id();
		}
	}
}

/**
 * Build the list of variation options for a variable product.
 * Returns an array of option data used to render the second select and the details panel.
 */
$pewc_build_variation_options = function( $variable_product ) use ( $item, $post_id, $vs_show ) {

	$options = array();

	foreach( $variable_product->get_available_variations( 'objects' ) as $variation ) {

		if( ! is_a( $variation, 'WC_Product_Variation' ) ) {
			continue;
		}

		$variation_price = pewc_maybe_include_tax( $variation, $variation->get_price() );

		if( ! empty( $item['child_discount'] ) && ! empty( $item['discount_type'] ) ) {
			$variation_price = pewc_get_discounted_child_price( $variation_price, $item['child_discount'], $item['discount_type'] );
		}

		$variation_price = apply_filters( 'pewc_child_product_option_cost', $variation_price, $item, $variation, $post_id );

		$disabled = ( ! $variation->is_purchasable() || ( ! $variation->is_in_stock() && ! $variation->backorders_allowed() ) );

		$available_stock = '';
		if( $variation->managing_stock() ) {
			$available_stock = $variation->get_stock_quantity();
		}
		$available_stock = apply_filters( 'pewc_child_product_available_stock', $available_stock, $variation, $item, $post_id );

		// get_formatted_name() embeds the price with HTML entities (e.g. &#36;); decode them
		// so the select option text reads "$10.00" rather than "&#36;10.00"
		$label = apply_filters( 'pewc_child_product_title', $variation->get_formatted_name(), $variation, $variation_price, $item );
		$label = html_entity_decode( wp_strip_all_tags( $label ), ENT_QUOTES, get_bloginfo( 'charset' ) );

		// Details for the panel shown when a variation is selected
		$image_html = '';
		if( ! empty( $vs_show['vs_show_thumbnail'] ) ) {
			$image_size = apply_filters( 'pewc_child_product_image_size', 'woocommerce_thumbnail', $variation->get_id() );
			$image_id   = $variation->get_image_id();
			if( ! $image_id ) {
				// Fall back to the parent variable product's image
				$image_id = $variable_product->get_image_id();
			}
			$image_html = $image_id ? wp_get_attachment_image( $image_id, $image_size ) : wc_placeholder_img( $image_size );
		}

		$price_html = '';
		if( ! empty( $vs_show['vs_show_price'] ) ) {
			if( ! empty( $item['child_discount'] ) && ! empty( $item['discount_type'] ) ) {
				$regular = pewc_maybe_include_tax( $variation, $variation->get_price() );
				$price_html = wc_format_sale_price( $regular, pewc_maybe_include_tax( $variation, $variation_price ) );
			} else {
				$price_html = $variation->get_price_html();
			}
		}

		$stock_html = '';
		if( ! empty( $vs_show['vs_show_stock'] ) ) {
			// wc_get_stock_html() is often empty for in-stock products that don't manage stock,
			// so build a status string that always reflects the variation's availability
			$stock_status = $variation->get_stock_status();
			if( 'outofstock' === $stock_status ) {
				$stock_text  = __( 'Out of stock', 'woocommerce' );
				$stock_class = 'out-of-stock';
			} else if( 'onbackorder' === $stock_status ) {
				$stock_text  = __( 'Available on backorder', 'woocommerce' );
				$stock_class = 'available-on-backorder';
			} else if( $variation->managing_stock() && null !== $variation->get_stock_quantity() ) {
				$stock_text  = sprintf( _n( '%s in stock', '%s in stock', $variation->get_stock_quantity(), 'woocommerce' ), wc_format_stock_quantity_for_display( $variation->get_stock_quantity(), $variation ) );
				$stock_class = 'in-stock';
			} else {
				$stock_text  = __( 'In stock', 'woocommerce' );
				$stock_class = 'in-stock';
			}
			$stock_html = sprintf(
				'<p class="stock %s">%s</p>',
				esc_attr( $stock_class ),
				esc_html( apply_filters( 'pewc_variable_select_stock_text', $stock_text, $variation, $item ) )
			);
		}

		$description_html = '';
		if( ! empty( $vs_show['vs_show_description'] ) ) {
			$description_text = $variation->get_description();
			if( '' === trim( $description_text ) ) {
				// Fall back to the parent variable product's short description
				$description_text = $variable_product->get_short_description();
			}
			if( '' !== trim( $description_text ) ) {
				$description_html = wc_format_content( $description_text );
			}
		}

		$options[] = array(
			'id'          => $variation->get_id(),
			'label'       => wp_strip_all_tags( $label ),
			'cost'        => (float) apply_filters( 'pewc_option_price', $variation_price, $item ),
			'stock'       => $available_stock,
			'disabled'    => $disabled,
			'image'       => $image_html,
			'price'       => $price_html,
			'stock_html'  => $stock_html,
			'description' => $description_html,
		);

	}

	return apply_filters( 'pewc_variable_select_variation_options', $options, $variable_product, $item, $post_id );

}; ?>

<div class="<?php echo esc_attr( join( ' ', $child_product_wrapper_class ) ); ?>" data-products-quantities="<?php echo esc_attr( $products_quantities ); ?>">

	<?php
	// Build the variation data for every variable child product once
	$vs_all_variations     = array();
	$vs_product_options    = array();
	foreach( $item['child_products'] as $child_product_id ) {

		$child_product = wc_get_product( $child_product_id );
		if( ! is_object( $child_product ) || ! $child_product->is_type( 'variable' ) || $child_product->get_status() !== 'publish' ) {
			// This layout only supports variable products
			continue;
		}

		$variation_options = $pewc_build_variation_options( $child_product );
		if( empty( $variation_options ) ) {
			continue;
		}

		$vs_all_variations[ $child_product_id ]  = $variation_options;
		$vs_product_options[ $child_product_id ] = apply_filters( 'pewc_child_product_title', get_the_title( $child_product_id ), $child_product );

	}

	$has_variable_products = ! empty( $vs_all_variations );

	if( $has_variable_products ) { ?>

		<script type="application/json" class="pewc-variable-select-data" data-for="<?php echo esc_attr( $id ); ?>"><?php
			// JSON_HEX_TAG prevents a stray </script> in variation content from breaking out
			echo wp_json_encode( $vs_all_variations, JSON_HEX_TAG );
		?></script>

		<div class="pewc-select-wrapper pewc-variable-select-product-wrapper">
			<select class="pewc-form-field pewc-variable-select-product" id="<?php echo esc_attr( $id ); ?>_variable_product" data-target="<?php echo esc_attr( $id ); ?>">

				<?php
				$placeholder = ! empty( $item['select_placeholder'] ) ? $item['select_placeholder'] : __( 'Choose a product', 'pewc' );
				echo '<option value="">' . esc_html( $placeholder ) . '</option>';

				foreach( $vs_product_options as $child_product_id => $child_product_title ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $child_product_id ),
						selected( $selected_product_id, $child_product_id, false ),
						esc_html( $child_product_title )
					);
				} ?>

			</select>
		</div>

		<?php
		// Pre-render the options for the currently selected product so the field works without JS running.
		$selected_variation_data = array();
		$variation_options       = ( $selected_product_id && isset( $vs_all_variations[ $selected_product_id ] ) ) ? $vs_all_variations[ $selected_product_id ] : array();

		// The first variation is selected automatically when no other variation is stored
		if( ! $selected_variation_id && ! empty( $variation_options ) ) {
			$selected_variation_id = $variation_options[0]['id'];
		}

		// Resolve the selected variation's data for server-side rendering
		foreach( $variation_options as $option ) {
			if( $selected_variation_id == $option['id'] ) {
				$selected_variation_data = $option;
				break;
			}
		} ?>

			<div class="pewc-variable-select-details"<?php echo empty( $variation_options ) ? ' style="display:none;"' : ''; ?>>

				<?php if( ! empty( $vs_show['vs_show_thumbnail'] ) ) { ?>
					<div class="pewc-variable-select-detail pewc-variable-select-thumbnail"><?php echo ! empty( $selected_variation_data['image'] ) ? wp_kses_post( $selected_variation_data['image'] ) : ''; ?></div>
				<?php } ?>

				<div class="pewc-variable-select-info">

					<div class="pewc-select-wrapper pewc-variable-select-variation-wrapper">
						<select class="pewc-form-field pewc-child-select-field pewc-variable-select-variation" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>_child_product" data-selected-variation="<?php echo esc_attr( $selected_variation_id ); ?>">

							<?php
							foreach( $variation_options as $option ) {
								printf(
									'<option data-option-cost="%s" data-field-value="%s" data-stock="%s" value="%s" %s %s>%s</option>',
									esc_attr( $option['cost'] ),
									esc_attr( $option['label'] ),
									esc_attr( $option['stock'] ),
									esc_attr( $option['id'] ),
									selected( $selected_variation_id, $option['id'], false ),
									$option['disabled'] ? 'disabled' : '',
									esc_html( $option['label'] )
								);
							} ?>

						</select>
					</div>

					<?php if( ! empty( $vs_show['vs_show_price'] ) ) { ?>
						<div class="pewc-variable-select-detail pewc-variable-select-price"><?php echo ! empty( $selected_variation_data['price'] ) ? wp_kses_post( $selected_variation_data['price'] ) : ''; ?></div>
					<?php } ?>
					<?php if( ! empty( $vs_show['vs_show_stock'] ) ) { ?>
						<div class="pewc-variable-select-detail pewc-variable-select-stock"><?php echo ! empty( $selected_variation_data['stock_html'] ) ? wp_kses_post( $selected_variation_data['stock_html'] ) : ''; ?></div>
					<?php } ?>
					<?php if( ! empty( $vs_show['vs_show_description'] ) ) { ?>
						<div class="pewc-variable-select-detail pewc-variable-select-description"><?php echo ! empty( $selected_variation_data['description'] ) ? wp_kses_post( $selected_variation_data['description'] ) : ''; ?></div>
					<?php } ?>

				</div><!-- .pewc-variable-select-info -->

			</div><!-- .pewc-variable-select-details -->

			<?php
			// Show the field's own description (the 'Description' setting) below the select.
			// pewc_suppress_variable_select_description() stops the standard hook adding it again.
			if( ! empty( $item['field_description'] ) ) {
				printf(
					'<div class="pewc-variable-select-field-description">%s</div>',
					wp_kses_post( wpautop( $item['field_description'] ) )
				);
			}

			// Independent quantities: render a quantity field (mirrors the Select layout).
			// The quantity is auto-set to 1 by JS when a variation is chosen.
			if( $products_quantities == 'independent' ) {
				$quantity_field_values = ! empty( $quantity_field_values ) ? $quantity_field_values : array();
				pewc_child_product_independent_quantity_field( $quantity_field_values, $selected_variation_id, $id, $item );
			}
			?>

	<?php } ?>

</div><!-- .child-product-wrapper -->
