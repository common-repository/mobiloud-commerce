<?php
/**
 * Contains logic to handle WooCommerce account page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

/**
 * Methods to register endpoint and templates for the
 * Accounts endpoint.
 */
class Account extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ) );
	}

	/**
	 * Registers the endpoint for the accounts page.
	 */
	public function register_endpoint() {
		add_rewrite_rule( 'ml-api/v2/ecommerce/account$', "index.php?is_ml_page=true&ml_page_type=account" );
	}

	/**
	 * Custom template for the account page.
	 */
	public function load_custom_template( $template ) {
		if ( 'account' !== self::$page_type ) {
			return $template;
		}

		return MLWOO_TEMPLATE_PATH . 'account.php';
	}
}