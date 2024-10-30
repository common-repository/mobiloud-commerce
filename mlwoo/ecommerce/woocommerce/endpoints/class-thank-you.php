<?php
/**
 * Contains logic to handle WooCommerce Thank you page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

/**
 * Methods to register endpoint and templates for the
 * Thank you page MobiLoud endpoint.
 */
class Thank_You extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ) );

		if ( MLWOO_IS_APP || ( isset( $_GET['ml_is_app'] ) && 'true' === $_GET['ml_is_app'] ) ) {
			add_filter( 'woocommerce_get_return_url', array( $this, 'custom_thankyou_url' ), 10, 2 );
		}

		if ( ( isset( $_GET['ml_is_app'] ) && 'true' === $_GET['ml_is_app'] ) ) {
			add_filter( 'woocommerce_locate_template', array( $this, 'load_custom_override_template' ), 9999, 3 );
		}
	}

	/**
	 * Registers the endpoint for thank you page.
	 *
	 * thank you page: ml-api/v2/ecommerce/thankyou
	 */
	public function register_endpoint() {
		$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );
		add_rewrite_rule( 'ml-api/v2/ecommerce/thankyou', "index.php?page_id={$checkout_page_id}&ml_page_type=thank-you&is_ml_page=true" );
	}

	/**
	 * Custom template for viewing a thank you page.
	 */
	public function load_custom_template( $template ) {
		$is_cart_clear = filter_input( INPUT_GET, 'clear-cart', FILTER_SANITIZE_STRING );

		if ( 'thank-you' === self::$page_type ) {
			return MLWOO_TEMPLATE_PATH . 'order-received.php';
		}

		return $template;
	}

	/**
	 * Overrides certain WooCommerce templates necessary for thank you page.
	 */
	public function load_custom_override_template( $template, $template_name, $template_path ) {
		if ( 'thank-you' === self::$page_type ) {
			return $template;
		}

		$basename = basename( $template );

		if ( 'thankyou.php' === $basename ) {
			$template = MLWOO_TEMPLATE_PATH . 'overrides/thankyou.php';
		}

		if ( 'order-details-item.php' === $basename ) {
			$template = MLWOO_TEMPLATE_PATH . 'overrides/order-details-item.php';
		}

		return $template;
	}

	/**
	 * Adds query params to the thank you page URL for the app
	 * to use.
	 */
	public function custom_thankyou_url( $url, $order ) {
		return add_query_arg(
			array(
				'order-received' => $order->get_id(),
				'key'            => $order->order_key,
				'clear-cart'     => 'true',
				'ml_is_app'      => MLWOO_IS_APP ? 'true' : 'false',
			),
			MLWOO_ENDPOINT . '/thankyou'
		);
	}
}