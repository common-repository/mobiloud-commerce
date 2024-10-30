<?php
/**
 * Contains logic to handle WooCommerce Checkout page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

use \MLWoo\Ecommerce\WooCommerce\Endpoints\Base;

/**
 * Methods to register endpoint and templates for the
 * Checkout MobiLoud endpoint.
 */
class Checkout extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ), 99999, 1 );

		if ( MLWOO_IS_APP ) {
			add_filter( 'woocommerce_locate_template', array( $this, 'load_custom_override_template' ), 999, 3 );
			add_action( 'wp_print_scripts', array( $this, 'remove_specific_scripts_loaded_by_woocommerce' ) );
			add_action( 'wp_ajax_woocommerce_checkout', array( '\WC_AJAX', 'checkout' ) );
			add_action( 'wp_ajax_nopriv_woocommerce_checkout', array( '\WC_AJAX', 'checkout' ) );
		}
	}

	/**
	 * Registers the endpoint to view an orders
	 *
	 * checkout: ml-api/v2/ecommerce/checkout
	 */
	public function register_endpoint() {
		$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );
		add_rewrite_rule( 'ml-api/v2/ecommerce/checkout', "index.php?is_ml_page=true&ml_page_type=checkout&page_id={$checkout_page_id}" );
	}

	/**
	 * Custom template for viewing the checkout page.
	 */
	public function load_custom_template( $template ) {
		if ( 'checkout' !== self::$page_type ) {
			return $template;
		}

		return MLWOO_TEMPLATE_PATH . 'checkout.php';
	}

	/**
	 * Overrides certain WooCommerce templates necessary for the checkout page.
	 */
	public function load_custom_override_template( $template, $template_name, $template_path ) {
		if ( 'checkout' !== self::$page_type && ! MLWOO_IS_APP ) {
			return $template;
		}

		$basename = basename( $template );

		if ( 'payment.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/payment.php';
		}

		if ( 'form-login.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/form-login.php';
		}

		return $template;
	}

	/**
	 * @todo Maybe remove this?
	 */
	public function override_woocommerce_checkout_order_processed( $order_id, $posted_data, $order ) {
		if ( ! MLWOO_IS_APP ) {
			return;
		}

		$this->process_order_payment( $order_id, $posted_data['payment_method'] );
	}

	/**
	 * @todo Maybe remove this?
	 */
	public function process_order_payment( $order_id, $payment_method ) {
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( ! isset( $available_gateways[ $payment_method ] ) ) {
			return;
		}

		// Store Order ID in session so it can be re-used after payment failure.
		WC()->session->set( 'order_awaiting_payment', $order_id );

		// Process Payment.
		$result = $available_gateways[ $payment_method ]->process_payment( $order_id );

		if ( isset( $result['result'] ) && 'success' === $result['result'] ) {
			$result = apply_filters( 'woocommerce_payment_successful_result', $result, $order_id );

			wp_send_json_success(
				array( 'redirectUrl' => $result['redirect'] )
			);
		}
	}

	/**
	 * Remove the SelectWoo script loaded by WooCommerce.
	 * We don't need this in the app.
	 */
	public function remove_specific_scripts_loaded_by_woocommerce() {
		if ( 'checkout' === self::$page_type ) {
			wp_deregister_script( 'selectWoo' );
			wp_dequeue_script( 'selectWoo' );
		}
	}
}
