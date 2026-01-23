<?php

namespace PaymentPlugins\WooCommerce\PPCP\Traits;

use PaymentPlugins\WooCommerce\PPCP\Admin\Settings\AdvancedSettings;

trait FeaturesTrait {

	public function init_supports( $supports = [] ) {
		/**
		 * @var AdvancedSettings $advanced_settings
		 */
		$advanced_settings = wc_ppcp_get_container()->get( AdvancedSettings::class );
		$vault_enabled     = $advanced_settings->is_vault_enabled();

		// Core supported features for all gateways
		$supports = array_merge(
			$supports,
			[
				'products',
				'default_credit_card_form',
				'refunds',
			]
		);

		$traits = \class_uses( \get_class( $this ) );

		foreach ( $traits as $trait ) {
			switch ( $trait ) {

				case 'PaymentPlugins\WooCommerce\PPCP\Traits\ThreeDSecureTrait':
					$supports[] = '3ds';
					break;
				case 'PaymentPlugins\WooCommerce\PPCP\Traits\VaultTokenTrait':
					$supports[] = 'vault';
					break;
				case 'PaymentPlugins\WooCommerce\PPCP\Traits\BillingAgreementTrait':
					if ( ! $vault_enabled ) {
						$supports[] = 'billing_agreement';
					}
					break;
				case 'PaymentPlugins\PPCP\WooCommerceSubscriptions\Traits\SubscriptionTrait':
					$supports = \array_merge(
						$supports,
						[
							'subscriptions',
							'subscription_cancellation',
							'multiple_subscriptions',
							'subscription_amount_changes',
							'subscription_date_changes',
							'subscription_payment_method_change_admin',
							'subscription_reactivation',
							'subscription_suspension',
							'subscription_payment_method_change_customer',
						]
					);
					break;
				case 'PaymentPlugins\PPCP\WooCommercePreOrders\Traits\PreOrdersTrait':
					$supports[] = 'pre-orders';
					break;
			}
		}

		if ( $this->id === 'ppcp_applepay' ) {
			$supports = array_diff( $supports, [ 'subscription_payment_method_change_customer' ] );
		}

		if ( \in_array( 'billing_agreement', $supports ) ) {
			unset( $supports[ array_search( 'vault', $supports ) ] );
		}
		// If vault is supported, then add tokenization and add_payment_method
		if ( \in_array( 'vault', $supports ) ) {
			$supports[] = 'tokenization';
			$supports[] = 'add_payment_method';
		}

		/**
		 * Allow external packages to add payment gateway features.
		 *
		 * @param array $supports Array of feature strings to add.
		 * @param string $gateway_id The payment gateway ID.
		 * @param object $gateway The payment gateway instance.
		 *
		 * @since 1.0.0
		 */
		$supports = apply_filters(
			'wc_ppcp_payment_gateway_features',
			$supports,
			$this->id,
			$this
		);

		$this->supports = $supports;
	}

}