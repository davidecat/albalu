<?php
/**
 * Plugin Name: Albalu - Checkout Week Selector
 * Description: Adds a week selector dropdown to WooCommerce checkout (next 12 months). Saved as ACF order field.
 * Version: 1.0.0
 * Author: Albalu
 * Requires Plugins: woocommerce, advanced-custom-fields-pro
 * Text Domain: albalu-week-selector
 */

defined( 'ABSPATH' ) || exit;

/**
 * Generate the list of weeks from current date to 12 months ahead.
 * Each week: Monday to Sunday.
 * Format: "Settimana {week_number}, {year}, {dd/mm} - {dd/mm}"
 */
function albalu_ws_get_weeks_options() {
	$options = array( '' => __( 'Scegli settimana di spedizione', 'albalu-week-selector' ) );

	// Start from the coming Monday (or today if Monday)
	$today = new DateTime( 'now', wp_timezone() );
	$start = clone $today;
	if ( (int) $start->format( 'N' ) !== 1 ) {
		$start->modify( 'next monday' );
	}

	$end = ( clone $today )->modify( '+12 months' );

	$current = clone $start;
	while ( $current < $end ) {
		$monday = clone $current;
		$sunday = ( clone $current )->modify( '+6 days' );

		// Skip weeks that overlap with closure periods:
		// - 23 December to 6 January (Christmas/New Year)
		// - All of August
		$skip = false;
		$check = clone $monday;
		for ( $d = 0; $d < 7; $d++ ) {
			$m = (int) $check->format( 'n' );
			$day = (int) $check->format( 'j' );
			// August
			if ( $m === 8 ) {
				$skip = true;
				break;
			}
			// 23 Dec - 31 Dec
			if ( $m === 12 && $day >= 23 ) {
				$skip = true;
				break;
			}
			// 1 Jan - 6 Jan
			if ( $m === 1 && $day <= 6 ) {
				$skip = true;
				break;
			}
			$check->modify( '+1 day' );
		}

		if ( ! $skip ) {
			$week_number = (int) $monday->format( 'W' );
			$year        = $monday->format( 'o' ); // ISO year matching week number

			$label = sprintf(
				'Settimana %d, %s, dal %s al %s',
				$week_number,
				$year,
				$monday->format( 'd/m' ),
				$sunday->format( 'd/m' )
			);

			$value = sprintf( '%s-W%02d', $year, $week_number );

			$options[ $value ] = $label;
		}

		$current->modify( '+7 days' );
	}

	return $options;
}

/**
 * Add week selector field to checkout.
 */
add_action( 'woocommerce_before_order_notes', 'albalu_ws_checkout_field' );
function albalu_ws_checkout_field( $checkout ) {
	$options = albalu_ws_get_weeks_options();

	echo '<div id="albalu_week_selector_field">';
	woocommerce_form_field( 'albalu_delivery_week', array(
		'type'     => 'select',
		'class'    => array( 'form-row-wide' ),
		'label'    => __( 'Scegli quando ricevere l\'ordine', 'albalu-week-selector' ),
		'required' => true,
		'options'  => $options,
	), $checkout->get_value( 'albalu_delivery_week' ) );
	echo '</div>';
}

/**
 * Validate the field.
 */
add_action( 'woocommerce_checkout_process', 'albalu_ws_validate_field' );
function albalu_ws_validate_field() {
	if ( empty( $_POST['albalu_delivery_week'] ) ) {
		wc_add_notice( __( 'Scegli settimana di spedizione.', 'albalu-week-selector' ), 'error' );
	}
}

/**
 * Save the field to order meta.
 */
add_action( 'woocommerce_checkout_update_order_meta', 'albalu_ws_save_field' );
function albalu_ws_save_field( $order_id ) {
	if ( ! empty( $_POST['albalu_delivery_week'] ) ) {
		$value = sanitize_text_field( wp_unslash( $_POST['albalu_delivery_week'] ) );
		update_post_meta( $order_id, 'albalu_delivery_week', $value );

		// Also save the human-readable label
		$options = albalu_ws_get_weeks_options();
		if ( isset( $options[ $value ] ) ) {
			update_post_meta( $order_id, '_albalu_delivery_week_label', $options[ $value ] );
		}
	}
}

/**
 * HPOS compatibility: save to order meta for WooCommerce HPOS.
 */
add_action( 'woocommerce_checkout_create_order', 'albalu_ws_save_field_hpos', 10, 2 );
function albalu_ws_save_field_hpos( $order, $data ) {
	if ( ! empty( $_POST['albalu_delivery_week'] ) ) {
		$value = sanitize_text_field( wp_unslash( $_POST['albalu_delivery_week'] ) );
		$order->update_meta_data( 'albalu_delivery_week', $value );

		$options = albalu_ws_get_weeks_options();
		if ( isset( $options[ $value ] ) ) {
			$order->update_meta_data( '_albalu_delivery_week_label', $options[ $value ] );
		}
	}
}

/**
 * Display the field in admin order details.
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'albalu_ws_display_admin_order' );
function albalu_ws_display_admin_order( $order ) {
	$label = $order->get_meta( '_albalu_delivery_week_label' );
	if ( $label ) {
		echo '<p><strong>' . esc_html__( 'Settimana di consegna', 'albalu-week-selector' ) . ':</strong> ' . esc_html( $label ) . '</p>';
	}
}

/**
 * Display the field in order emails.
 */
add_action( 'woocommerce_email_after_order_table', 'albalu_ws_display_email', 10, 4 );
function albalu_ws_display_email( $order, $sent_to_admin, $plain_text, $email ) {
	$label = $order->get_meta( '_albalu_delivery_week_label' );
	if ( ! $label ) return;

	if ( $plain_text ) {
		echo "\n" . esc_html__( 'Settimana di consegna', 'albalu-week-selector' ) . ': ' . $label . "\n";
	} else {
		echo '<p><strong>' . esc_html__( 'Settimana di consegna', 'albalu-week-selector' ) . ':</strong> ' . esc_html( $label ) . '</p>';
	}
}

/**
 * Display on thank you page and order details.
 */
add_action( 'woocommerce_order_details_after_order_table', 'albalu_ws_display_thankyou' );
function albalu_ws_display_thankyou( $order ) {
	$label = $order->get_meta( '_albalu_delivery_week_label' );
	if ( $label ) {
		echo '<p><strong>' . esc_html__( 'Settimana di consegna', 'albalu-week-selector' ) . ':</strong> ' . esc_html( $label ) . '</p>';
	}
}

/**
 * Register ACF field group for orders (if ACF is active).
 */
add_action( 'acf/init', 'albalu_ws_register_acf_field' );
function albalu_ws_register_acf_field() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	acf_add_local_field_group( array(
		'key'      => 'group_albalu_delivery_week',
		'title'    => 'Settimana di Consegna',
		'fields'   => array(
			array(
				'key'       => 'field_albalu_delivery_week',
				'label'     => 'Settimana di consegna',
				'name'      => 'albalu_delivery_week',
				'type'      => 'text',
				'readonly'  => 1,
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'shop_order',
				),
			),
		),
		'position'     => 'side',
		'style'        => 'default',
		'label_placement' => 'top',
	) );
}

/**
 * Add column to orders list in admin.
 */
add_filter( 'manage_edit-shop_order_columns', 'albalu_ws_admin_order_column' );
add_filter( 'manage_woocommerce_page_wc-orders_columns', 'albalu_ws_admin_order_column' );
function albalu_ws_admin_order_column( $columns ) {
	$new_columns = array();
	foreach ( $columns as $key => $column ) {
		$new_columns[ $key ] = $column;
		if ( $key === 'order_date' ) {
			$new_columns['albalu_delivery_week'] = __( 'Settimana consegna', 'albalu-week-selector' );
		}
	}
	return $new_columns;
}

add_action( 'manage_shop_order_posts_custom_column', 'albalu_ws_admin_order_column_content', 10, 2 );
function albalu_ws_admin_order_column_content( $column, $post_id ) {
	if ( $column === 'albalu_delivery_week' ) {
		$order = wc_get_order( $post_id );
		$label = $order ? $order->get_meta( '_albalu_delivery_week_label' ) : '';
		echo $label ? esc_html( $label ) : '—';
	}
}

add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'albalu_ws_admin_order_column_content_hpos', 10, 2 );
function albalu_ws_admin_order_column_content_hpos( $column, $order ) {
	if ( $column === 'albalu_delivery_week' ) {
		$label = $order->get_meta( '_albalu_delivery_week_label' );
		echo $label ? esc_html( $label ) : '—';
	}
}
