<?php
/**
 * Contains logic to handle WooCommerce view order page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

/**
 * Methods to register endpoint and templates for the
 * view order MobiLoud endpoint.
 */
class Account_View_Order extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ) );
		add_filter( 'woocommerce_locate_template', array( $this, 'load_custom_override_template' ), 9999, 3 );
	}

	/**
	 * Registers the endpoint to view an orders
	 *
	 * order: ml-api/v2/ecommerce/account/order/<order_id>
	 */
	public function register_endpoint() {
		$order_page_id = get_option( 'woocommerce_myaccount_page_id' );
		add_rewrite_rule( 'ml-api/v2/ecommerce/account/order/(\d+)$', "index.php?is_ml_page=true&ml_page_type=account-view-order&page_id={$order_page_id}&" . 'view-order=$matches[1]' );
	}

	/**
	 * Custom template for viewing an order.
	 */
	public function load_custom_template( $template ) {
		if ( 'account-view-order' !== self::$page_type ) {
			return $template;
		}

		return MLWOO_TEMPLATE_PATH . 'account-view-order.php';
	}

	/**
	 * Overrides certain WooCommerce templates necessary to view an order.
	 */
	public function load_custom_override_template( $template, $template_name, $template_path ) {
		return $template;
	}
}
