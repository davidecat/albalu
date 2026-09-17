<?php
/**
 * The markup for the 'Calculations' tab's settings
 *
 * @package WooCommerce Product Add-Ons Ultimate
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="pewc-fields-wrapper pewc-hide-if-not-pro">

	<div class="product-extra-field pewc-products-extras">
		<div class="product-extra-field-inner">
			<?php $field_price = isset( $item['field_price'] ) ? $item['field_price'] : ''; ?>
			<label>
				<?php _e( 'Child Products', 'pewc' ); ?>
				<?php echo wc_help_tip( 'Select which products you\'d like to associate with this field', 'pewc' ); ?>
			</label>
		</div>
		<div class="product-extra-field-inner pewc-child-products-inner">
			<?php // $simple_products = pewc_get_simple_products();
			$child_products = ! empty( $item['child_products'] ) ? $item['child_products'] : array();
			$child_product_method = pewc_child_products_method( $post_id, $item_key, $item );
			// The unrestricted method, so the admin JS can restore it when switching away from the Variable Select layout
			$default_child_product_method = pewc_child_products_method( $post_id, $item_key, array() );
			$products_layout = isset( $item['products_layout'] ) ? $item['products_layout'] : '';
			// The Variable Select layout only accepts variable products
			$restrict_to_variable = ( 'variable-select' === $products_layout );
			if( $child_product_method != 'variable_subscriptions' ) {
				// Use the standard WooCommerce AJAX methods to search for child products and/or child variations ?>
				<select class="pewc-field-item wc-product-search pewc-field-child_products pewc-data-options" data-options="" multiple="multiple" style="width: 100%;" name="<?php echo esc_attr( $base_name ); ?>[child_products][]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Choose child products', 'pewc' ); ?>" data-action="<?php echo esc_attr( $child_product_method ); ?>" data-default-action="<?php echo esc_attr( $default_child_product_method ); ?>" data-include="" data-exclude="<?php echo intval( $post_id ); ?>" data-field-name="child_products">
					<?php
					foreach( $child_products as $product_id ) {
						$product = wc_get_product( $product_id );
						// if( is_object( $product ) && $product->is_type( 'simple' ) ) {
						if( is_object( $product ) ) {
							if( $restrict_to_variable && ! $product->is_type( 'variable' ) ) {
								// The Variable Select layout only accepts variable products
								continue;
							}
							echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
						}
					} ?>
				</select>
			<?php } else {
				// Populate field with subscription variations ?>
				<select class="pewc-field-item pewc-variation-field pewc-field-child_products pewc-data-options" data-options="" multiple="multiple" style="width: 100%;" name="<?php echo esc_attr( $base_name ); ?>[child_products][]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Choose the child subscription variations', 'pewc' ); ?>" data-field-name="child_products">
					<?php
					$subscription_variations = pewc_get_subscription_variations();
					$child_products = ! empty( $item['child_products'] ) ? $item['child_products'] : array();
					foreach( $subscription_variations as $variation_id=>$variation_name ) {
						// $product = wc_get_product( $product_id );
						$selected = ( is_array( $child_products ) && in_array( $variation_id, $child_products ) ) ? 'selected' : '';
						echo '<option value="' . esc_attr( $variation_id ) . '"' . $selected . '>' . wp_kses_post( $variation_name ) . '</option>';
					} ?>
				</select>
			<?php } ?>
			<small class="pewc-variable-select-note"<?php echo ! $restrict_to_variable ? ' style="display:none;"' : ''; ?>>
				<?php _e( 'The Variable Select layout only accepts variable products.', 'pewc' ); ?>
			</small>
		</div>
	</div>

	<div class="pewc-product-categories-extras">

		<div class="product-extra-field">
			<div class="product-extra-field-inner">
				<?php $field_price = isset( $item['field_price'] ) ? $item['field_price'] : ''; ?>
				<label>
					<?php _e( 'Product Categories', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Select which product categories you\'d like to autopopulate this field', 'pewc' ); ?>
				</label>
			</div>
			<div class="product-extra-field-inner">
				<?php $product_categories = pewc_get_product_categories(); ?>
				<select class="pewc-field-item wc-category-search pewc-field-child_categories pewc-data-options" data-options="" multiple="multiple" style="width: 100%;" name="<?php echo esc_attr( $base_name ); ?>[child_categories][]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Select product categories', 'pewc' ); ?>" data-action="json_search_categories" data-include="" data-exclude="" data-field-name="child_categories">
					<?php
					if( ! empty( $item['child_categories'] ) ) {
						$child_categories = $item['child_categories'];
						foreach( $child_categories as $category_name ) {
							$term = get_term_by('slug', $category_name, 'product_cat');
							$cat_id = is_object($term) && $term->term_id ? $term->term_id : false;
							if( $cat_id && $category_name && $category_name !== '' ) {
								echo '<option value="' . esc_attr( $category_name ) . '"' . selected( true, true, false ) . '>' . esc_html( $term->name ) . '</option>';
							}
						}
					} ?>
				</select>
			</div>
		</div>
	
	</div><!-- pewc-product-categories-extras -->
	
</div><!-- pewc-products-extras -->


<?php if( apply_filters( 'pewc_show_products_params', true, $item, $post_id ) ) { ?>

	<div class="pewc-fields-wrapper pewc-products-extras pewc-product-categories-extras">

		<div class="product-extra-field pewc-products-layout">
			<div class="product-extra-field-inner">
				
				<label>
					<?php _e( 'Products Layout', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Choose how child products will be displayed.', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $products_layout = isset( $item['products_layout'] ) ? $item['products_layout'] : ''; ?>
				<select class="pewc-field-item pewc-field-products_layout" name="<?php echo esc_attr( $base_name ); ?>[products_layout]" data-field-name="products_layout">
					<option value="checkboxes" <?php selected( $products_layout, 'checkboxes', true ); ?>><?php _e( 'Checkboxes Images', 'pewc' ); ?></option>
					<option value="checkboxes-list" <?php selected( $products_layout, 'checkboxes-list', true ); ?>><?php _e( 'Checkboxes List', 'pewc' ); ?></option>
					<option value="column" <?php selected( $products_layout, 'column', true ); ?>><?php _e( 'Column', 'pewc' ); ?></option>
					<option value="components" <?php selected( $products_layout, 'components', true ); ?>><?php _e( 'Components List', 'pewc' ); ?></option>
					<option value="radio" <?php selected( $products_layout, 'radio', true ); ?>><?php _e( 'Radio Images', 'pewc' ); ?></option>
					<option value="radio-list" <?php selected( $products_layout, 'radio-list', true ); ?>><?php _e( 'Radio List', 'pewc' ); ?></option>
					<option value="select" <?php selected( $products_layout, 'select', true ); ?>><?php _e( 'Select', 'pewc' ); ?></option>
					<option value="swatches" <?php selected( $products_layout, 'swatches', true ); ?>><?php _e( 'Swatches', 'pewc' ); ?></option>
					<option value="variable-select" <?php selected( $products_layout, 'variable-select', true ); ?>><?php _e( 'Variable Select', 'pewc' ); ?></option>
					<option value="grid" <?php selected( $products_layout, 'grid', true ); ?>><?php _e( 'Variations Grid', 'pewc' ); ?></option>
				</select>

				<small>
					<?php printf(
						'<a href="%s" target="_blank">%s</a>',
						'https://pluginrepublic.com/woocommerce-child-products/',
						__( 'This article explains the different layouts', 'pewc' )
					); ?>
				</small>

			</div>
		</div>

		<div class="product-extra-field pewc-products-select-placeholder">
			<div class="product-extra-field-inner">
				
				<label>
					<?php _e( 'Select Field Placeholder', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Enter instructional text in here to appear as the first option in the select field.', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $placeholder = ( ! empty( $item['select_placeholder'] ) ) ? $item['select_placeholder'] : ''; ?>
				<input type="text" class="pewc-field-item pewc-field-select_placeholder" name="<?php echo esc_attr( $base_name ); ?>[select_placeholder]" value="<?php echo esc_attr( $placeholder ); ?>" data-field-name="select_placeholder">

			</div>
		</div>

		<!-- Removed allow_none parameter -->

		<div class="product-extra-field pewc-products-quantities">
			<div class="product-extra-field-inner">
				
				<label>
					<?php _e( 'Products Quantities', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Choose whether to link the quantities of the parent product and child product so that they are always the same, or to limit the quantity of the child product to one only.', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $products_quantities = isset( $item['products_quantities'] ) ? $item['products_quantities'] : ''; ?>
				<select class="pewc-field-item pewc-field-products_quantities" name="<?php echo esc_attr( $base_name ); ?>[products_quantities]" data-field-name="products_quantities">
					<option value="independent" <?php selected( $products_quantities, 'independent', true ); ?>><?php _e( 'Independent', 'pewc' ); ?></option>
					<option value="linked" <?php selected( $products_quantities, 'linked', true ); ?>><?php _e( 'Linked', 'pewc' ); ?></option>
					<option value="one-only" <?php selected( $products_quantities, 'one-only', true ); ?>><?php _e( 'One only', 'pewc' ); ?></option>
				</select>

			</div>
		</div>

	</div>

	<div class="pewc-fields-wrapper pewc-products-extras pewc-select-all-extras split-half no-gap">

		<div class="product-extra-field">
			<div class="product-extra-field-inner">

				<label class="pewc-checkbox-field-label" for="<?php echo esc_attr( $base_name ); ?>_select_all_enabled">
					<?php _e( 'Enable Select All Option', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Display an additional checkbox that lets the customer select all child products at once.', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $select_all_enabled = ! empty( $item['select_all_enabled'] ); ?>
				<?php pewc_checkbox_toggle( 'select_all_enabled', $select_all_enabled, $group_id, $item_key, 'pewc-select-all-enabled' ); ?>

			</div>
		</div>

		<div class="product-extra-field">
			<div class="product-extra-field-inner">

				<label>
					<?php _e( 'Select All Label', 'pewc' ); ?>
					<?php echo wc_help_tip( 'The label shown next to the Select All checkbox.', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $select_all_label = isset( $item['select_all_label'] ) ? $item['select_all_label'] : ''; ?>
				<input type="text" class="pewc-field-item pewc-field-select_all_label" name="<?php echo esc_attr( $base_name ); ?>[select_all_label]" value="<?php echo esc_attr( $select_all_label ); ?>" placeholder="<?php esc_attr_e( 'Select All', 'pewc' ); ?>" data-field-name="select_all_label">

			</div>
		</div>

	</div>

	<div class="pewc-fields-wrapper pewc-products-extras pewc-select-all-option-extras split-half">

		<div class="product-extra-field">
			<div class="product-extra-field-inner">

				<label>
					<?php _e( 'Select All Price Adjustment', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Choose how the Select All price is calculated: as a percentage or fixed amount off the total of all child products, or as a flat set price.', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $select_all_price_type = isset( $item['select_all_price_type'] ) ? $item['select_all_price_type'] : 'percentage'; ?>
				<select class="pewc-field-item pewc-field-select_all_price_type" name="<?php echo esc_attr( $base_name ); ?>[select_all_price_type]" data-field-name="select_all_price_type">
					<option value="percentage" <?php selected( $select_all_price_type, 'percentage', true ); ?>><?php _e( 'Percentage', 'pewc' ); ?></option>
					<option value="fixed" <?php selected( $select_all_price_type, 'fixed', true ); ?>><?php _e( 'Fixed', 'pewc' ); ?></option>
					<option value="set" <?php selected( $select_all_price_type, 'set', true ); ?>><?php _e( 'Set', 'pewc' ); ?></option>
				</select>

			</div>
		</div>

		<div class="product-extra-field">
			<div class="product-extra-field-inner">

				<label>
					<?php _e( 'Select All Price', 'pewc' ); ?>
					<?php echo wc_help_tip( 'The percentage or amount to discount off the total of all child products, or the flat set price to charge when Select All is chosen.', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $select_all_price = isset( $item['select_all_price'] ) ? $item['select_all_price'] : ''; ?>
				<input type="text" class="pewc-field-item pewc-field-select_all_price" name="<?php echo esc_attr( $base_name ); ?>[select_all_price]" value="<?php echo esc_attr( $select_all_price ); ?>" data-field-name="select_all_price">

			</div>
		</div>

	</div>

	<?php
	// 'Variable Select' layout display options. Each defaults to enabled.
	// A hidden companion input ensures an explicit 0 is submitted when the toggle is off.
	$vs_display_options = array(
		'vs_show_thumbnail'   => array( __( 'Show Thumbnail', 'pewc' ), __( 'Show the selected variation\'s image', 'pewc' ) ),
		'vs_show_price'       => array( __( 'Show Price', 'pewc' ), __( 'Show the selected variation\'s price', 'pewc' ) ),
		'vs_show_stock'       => array( __( 'Show Stock', 'pewc' ), __( 'Show the selected variation\'s stock status', 'pewc' ) ),
		'vs_show_description' => array( __( 'Show Short Description', 'pewc' ), __( 'Show the selected variation\'s description', 'pewc' ) ),
	); ?>

	<div class="pewc-fields-wrapper pewc-variable-select-extras split-half">

		<?php foreach( $vs_display_options as $vs_key => $vs_labels ) {
			// Default enabled: true when the setting has never been saved
			$vs_checked = ( ! isset( $item[ $vs_key ] ) || '' === $item[ $vs_key ] ) ? true : ! empty( $item[ $vs_key ] ); ?>
			<div class="product-extra-field">
				<div class="product-extra-field-inner">
					<label class="pewc-checkbox-field-label" for="<?php echo esc_attr( $base_name ); ?>_<?php echo esc_attr( $vs_key ); ?>">
						<?php echo esc_html( $vs_labels[0] ); ?>
						<?php echo wc_help_tip( $vs_labels[1] ); ?>
					</label>
				</div>
				<div class="product-extra-field-inner">
					<input type="hidden" name="<?php echo esc_attr( $base_name ); ?>[<?php echo esc_attr( $vs_key ); ?>]" value="0">
					<?php pewc_checkbox_toggle( $vs_key, $vs_checked, $group_id, $item_key ); ?>
				</div>
			</div>
		<?php } ?>

	</div>

	<div class="pewc-fields-wrapper pewc-child-product-min-max-extras split-half no-gap">

		<div class="product-extra-field">
			<div class="product-extra-field-inner">

				<label>
					<?php _e( 'Min Child Products', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Specify a minimum number of products the user must choose from this field', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $min_products = ( isset( $item['min_products'] ) ) ? intval( $item['min_products'] ) : ''; ?>
				<input type="number" class="pewc-field-item pewc-min-child-products" name="<?php echo esc_attr( $base_name ); ?>[min_products]" value="<?php echo esc_attr( $min_products ); ?>" min="0" max="" step="1" data-field-name="min_products">

			</div>
		</div>

		<div class="product-extra-field">
			<div class="product-extra-field-inner">
				
				<label>
					<?php _e( 'Max Child Products', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Specify a maximum number of products the user must choose from this field', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $max_products = ( isset( $item['max_products'] ) ) ? intval( $item['max_products'] ) : ''; ?>
				<input type="number" class="pewc-field-item pewc-max-child-products" name="<?php echo esc_attr( $base_name ); ?>[max_products]" value="<?php echo esc_attr( $max_products ); ?>" min="0" max="" step="1" data-field-name="max_products">

			</div>
		</div>

	</div>
	<div class="pewc-fields-wrapper pewc-child-product-min-max-extras split-half">

		<div class="product-extra-field">
			<div class="product-extra-field-inner">
				
				<label>
					<?php _e( 'Default Quantity', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Specify a default quantity if you wish', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $default_quantity = ( isset( $item['default_quantity'] ) ) ? intval( $item['default_quantity'] ) : ''; ?>
				<input type="number" class="pewc-field-item pewc-default-quantity" name="<?php echo esc_attr( $base_name ); ?>[default_quantity]" value="<?php echo esc_attr( $default_quantity ); ?>" min="0" max="" step="1" data-field-name="default_quantity">

			</div>
		</div>

		<div class="product-extra-field pewc-components-only">
			<div class="product-extra-field-inner">
				
				<label class="pewc-checkbox-field-label" for="<?php echo esc_attr( $base_name ); ?>_force_quantity">
					<?php _e( 'Force Quantity?', 'pewc' ); ?>
					<?php echo wc_help_tip( 'Enable this option to prevent the user from changing the default quantity', 'pewc' ); ?>
				</label>

			</div>
			<div class="product-extra-field-inner">

				<?php $checked = ! empty( $item['force_quantity'] ); ?>
				<?php pewc_checkbox_toggle( 'force_quantity', $checked, $group_id, $item_key, 'pewc-force-quantity' ); ?>

			</div>
		</div>

	</div>

<?php }

do_action( 'pewc_before_end_products_settings', $item, $group_id, $item_key ); // 4.0.3, used by Bookings

do_action( 'pewc_end_products_settings_section', $base_name, $group_id, $item_key, $field_type, $field_label, $admin_label );