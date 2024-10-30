<?php
/**
 * Contains logic to handle WooCommerce Categories page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

use \MLWoo\Ecommerce\WooCommerce\Endpoints\Base;

/**
 * Methods to register endpoint and templates for the
 * Product Categories MobiLoud endpoint.
 */
class Product_Categories extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ), 12 );
	}

	/**
	 * Registers the endpoint for product categories.
	 *
	 * product-categories: ml-api/v2/ecommerce/product-categories
	 */
	public function register_endpoint() {
		add_rewrite_rule( 'ml-api/v2/ecommerce/product-categories$', 'index.php?&is_ml_page=true&ml_page_type=product-categories' );
	}

	/**
	 * Custom template for viewing the product categories page.
	 */
	public function load_custom_template( $template ) {
		if ( 'product-categories' !== self::$page_type ) {
			return $template;
		}

		return apply_filters(
			'mlwoo_template_product_categories',
			MLWOO_TEMPLATE_PATH . 'product-categories.php'
		);
	}
}