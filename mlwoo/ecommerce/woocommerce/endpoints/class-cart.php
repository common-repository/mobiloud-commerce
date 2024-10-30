<?php
/**
 * Contains logic to handle WooCommerce Cart page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

use \MLWoo\Ecommerce\WooCommerce\Endpoints\Base;

/**
 * Methods to register endpoint and templates for the
 * Cart MobiLoud endpoint.
 */
class Cart extends Base {

	/**
	 * Array of paths which are used to detect if the AJAX-request
	 * are from the app and only the following paths are allowed
	 * to update the Cart page template.
	 */
	public static $valid_referrers = array(
		'/ml-api/v2/ecommerce/cart/',
		'/?wc-ajax=apply_coupon',
		'/?wc-ajax=remove_coupon',
		'/?wc-ajax=update_shipping_method',
		'/?removed_item=1',
		'/cart/',
	);

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ) );
		add_filter( 'woocommerce_locate_template', array( $this, 'load_custom_override_template' ), 999, 3 );

		if ( MLWOO_IS_APP ) {
			add_action( 'wp_print_scripts', array( $this, 'remove_specific_scripts_loaded_by_woocommerce' ), 99999 );
		}

		self::$valid_referrers = apply_filters( 'mlwoo_cart_valid_referrers', self::$valid_referrers );
	}

	/**
	 * A utility function that returns true if the current request is a valid request
	 * and false otherwise.
	 */
	public static function is_valid_request( $current_request, $valid_requests ) {
		foreach ( $valid_requests as $request ) {
			if ( false !== strpos( $current_request, $request ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Registers the endpoint for the cart page.
	 *
	 * cart: ml-api/v2/ecommerce/cart
	 */
	public function register_endpoint() {
		$cart_page_id = get_option( 'woocommerce_cart_page_id' );
		add_rewrite_rule( 'ml-api/v2/ecommerce/cart$', "index.php?is_ml_page=true&ml_page_type=cart&page_id={$cart_page_id}" );
	}

	/**
	 * Custom template for viewing the cart page.
	 */
	public function load_custom_template( $template ) {
		if ( MLWOO_IS_APP && ( 'cart' === self::$page_type || self::is_valid_request( $_SERVER['REQUEST_URI'], self::$valid_referrers ) ) ) {
			return MLWOO_TEMPLATE_PATH . 'cart.php';
		}

		return $template;
	}

	/**
	 * Overrides certain WooCommerce templates necessary for the cart page.
	 */
	public function load_custom_override_template( $template, $template_name, $template_path ) {
		if ( ! MLWOO_IS_APP ) {
			return $template;
		}

		if ( ! self::is_valid_request( $_SERVER['REQUEST_URI'], self::$valid_referrers ) ) {
			return $template;
		}

		$basename = basename( $template );

		if ( 'quantity-input.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/quantity-input.php';
		}

		if ( 'cart.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/cart.php';
		}

		if ( 'cart-empty.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/cart-empty.php';
		}

		if ( 'proceed-to-checkout-button.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/proceed-to-checkout-button.php';
		}

		if ( 'shipping-calculator.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/shipping-calculator.php';
		}

		return $template;
	}

	/**
	 * Remove the SelectWoo script loaded by WooCommerce.
	 * We don't need this in the app.
	 */
	public function remove_specific_scripts_loaded_by_woocommerce() {
		if ( 'cart' === self::$page_type ) {
			wp_deregister_script( 'selectWoo' );
			wp_dequeue_script( 'selectWoo' );
		}
	}
}
