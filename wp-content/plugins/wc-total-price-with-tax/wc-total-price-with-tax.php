<?php
/**
 * Plugin Name: WC Total Price with Tax
 * Plugin URI: https://wordpress.org/plugins/wc-total-price-with-tax/
 * Description: Adds a Total Price column to the WooCommerce order admin screen includes Taxes for: Products, Shipping, and Fees.
 * Version: 1.5
 * Author: Headplus
 * Author URI: https://headplus.gr
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wc-total-price-with-tax
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Add the Total Price column header
function headplus_admin_order_item_headers($order) {
    echo '<th class="total_price sortable" data-sort="total_price">' . esc_html__('Total(icl.VAT)', 'wc-total-price-with-tax') . '</th>';
}
add_action('woocommerce_admin_order_item_headers', 'headplus_admin_order_item_headers');

// Display the Total Price value
function headplus_admin_order_item_values($product, $item, $item_id) {
    if ($product) {
        // Get the item's price and tax data from order item meta
        $item_price = wc_get_order_item_meta($item_id, '_line_total', true);
        $item_tax = wc_get_order_item_meta($item_id, '_line_tax', true);

        // Calculate the item's total price including taxes
        $total_price = $item_price + $item_tax;
    } elseif (in_array($item->get_type(), array('shipping', 'fee'))) {
        // For shipping and fee items, get the total and total tax directly
        $total_price = $item->get_total() + $item->get_total_tax();
    } else {
        $total_price = 0;
    }

    // Get the currency symbol from WooCommerce settings
    $currency_symbol = get_woocommerce_currency_symbol();

    echo '<td class="total_price">' . wc_price($total_price, array('currency' => ''))  . '</td>';
}
add_action('woocommerce_admin_order_item_values', 'headplus_admin_order_item_values', 10, 3);
