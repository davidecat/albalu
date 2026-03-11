<?php
/**
 * Checkout Form
 *
 * Overrides bootscore/woocommerce/checkout/form-checkout.php
 * Left column (66%): billing/shipping + order details + payment
 * Right column (34%): order totals recap + features
 *
 * @package Albalu
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout albalu-checkout-grid" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<div class="checkout-left" id="customer_details">
			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
			<?php do_action( 'woocommerce_checkout_billing' ); ?>
			<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
		</div>

	<?php endif; ?>

	<div class="checkout-order-details">
		<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
		<h3><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php do_action( 'woocommerce_checkout_order_review' ); ?>
		</div>
		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
	</div>

	<div class="checkout-right">
		<h3><?php esc_html_e( 'Order total', 'woocommerce' ); ?></h3>
		<table class="shop_table checkout-totals-table">
			<tbody>
				<tr class="cart-subtotal">
					<th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
					<td><?php wc_cart_totals_subtotal_html(); ?></td>
				</tr>

				<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
					<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
						<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
					<?php wc_cart_totals_shipping_html(); ?>
				<?php endif; ?>

				<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
					<tr class="fee">
						<th><?php echo esc_html( $fee->name ); ?></th>
						<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
					<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
						<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
							<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
								<th><?php echo esc_html( $tax->label ); ?></th>
								<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr class="tax-total">
							<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
							<td><?php wc_cart_totals_taxes_total_html(); ?></td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>

				<tr class="order-total">
					<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
					<td><?php wc_cart_totals_order_total_html(); ?></td>
				</tr>
			</tbody>
		</table>

		<?php
		// Features from Chi Siamo page
		$features = function_exists( 'albalu_get_default_features' ) ? albalu_get_default_features() : array();
		if ( ! empty( $features ) ) : ?>
			<div class="checkout-features features-section mt-4">
				<?php foreach ( $features as $item ) : ?>
					<div class="d-flex align-items-start p-3 border-start mb-3">
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<img src="<?php echo esc_url( $item['icon'] ); ?>" alt="<?php echo esc_attr( $item['title'] ?? '' ); ?>" class="me-3 flex-shrink-0" width="50" height="50">
						<?php endif; ?>
						<div>
							<?php if ( ! empty( $item['title'] ) ) : ?>
								<h5 class="fw-medium mb-1 text-uppercase"><?php echo esc_html( $item['title'] ); ?></h5>
							<?php endif; ?>
							<?php if ( ! empty( $item['description'] ) ) : ?>
								<p class="text-secondary mb-0 small"><?php echo esc_html( $item['description'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php $base = esc_url( get_stylesheet_directory_uri() . '/assets/img' ); ?>
		<div class="albalu-purchase-benefits mt-4">
			<!-- <div class="d-flex align-items-center item border-top border-bottom"><img class="benefit-icon" src="<?php echo $base; ?>/truck.svg" alt="Spedizione"><p>Realizziamo e spediamo il tuo ordine in <strong>7/13 giorni lavorativi</strong>.</p></div> -->
			<div class="d-flex align-items-center item"><img class="benefit-icon" src="<?php echo $base; ?>/paypal.svg" alt="Pagamenti PayPal"><p>Pagamenti sicuri con <strong>PayPal e Carte di Credito</strong>.</p></div>
			<div class="d-flex align-items-center item"><img class="benefit-icon" src="<?php echo $base; ?>/klarna.svg" alt="Klarna 3 rate"><p>Pagamento in <strong>3 rate senza interessi</strong> con Klarna.</p></div>
			<div class="d-flex align-items-center item"><img class="benefit-icon" src="<?php echo $base; ?>/consegna.svg" alt="Contrassegno"><p>Pagamento in <strong>contrassegno</strong> alla consegna.</p></div>
			<div class="d-flex align-items-center item border-bottom"><img class="benefit-icon" src="<?php echo $base; ?>/bancario.svg" alt="Bonifico bancario"><p>Pagamento con <strong>bonifico bancario</strong>.</p></div>
		</div>
	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
