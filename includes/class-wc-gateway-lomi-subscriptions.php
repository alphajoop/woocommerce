<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Gateway_Lomi_Subscriptions
 */
class WC_Gateway_Lomi_Subscriptions extends WC_Gateway_Lomi {

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {

			add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );

		}
	}

	/**
	 * Process a trial subscription order with 0 total.
	 *
	 * @param int $order_id WC Order ID.
	 *
	 * @return array|void
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		// Check for trial subscription order with 0 total.
		if ( $this->order_contains_subscription( $order->get_id() ) && $order->get_total() == 0 ) {

			$order->payment_complete();

			$order->add_order_note(
				__( 'WooCommerce free trial completed locally ($0). No lomi checkout or lomi subscription was created — recurring billing after the trial requires a paid lomi checkout or lomi catalog trial configuration.', 'woo-lomi' )
			);

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);

		} else {

			return parent::process_payment( $order_id );

		}

	}

	/**
	 * Process a subscription renewal.
	 *
	 * @param float    $amount_to_charge Subscription payment amount.
	 * @param WC_Order $renewal_order Renewal Order.
	 */
	public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {

		$renewal_order->update_status(
			'on-hold',
			__( 'Renewal billing is handled by the lomi. subscription engine — not charged automatically in WooCommerce. Manage renewals in the lomi. customer portal or dashboard.', 'woo-lomi' )
		);

	}

	/**
	 * Process a subscription renewal payment.
	 *
	 * @param WC_Order $order  Subscription renewal order.
	 * @param float    $amount Subscription payment amount.
	 *
	 * @return bool|WP_Error
	 */
	public function process_subscription_payment( $order, $amount ) {

		return new WP_Error(
			'lomi_subscription_renewal',
			__( 'Automatic subscription renewals are not charged in WooCommerce with lomi. Recurring billing is handled by the lomi. subscription engine after the first payment.', 'woo-lomi' )
		);

	}

}
