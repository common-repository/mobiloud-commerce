<?php
/**
 * Contains logic to handle WooCommerce Home page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

/**
 * Methods to register endpoint and templates for the
 * Home MobiLoud endpoint.
 */
class Home extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ) );
	}

	/**
	 * Registers the endpoint to view an orders
	 *
	 * home: ml-api/v2/ecommerce/home
	 */
	public function register_endpoint() {
		add_rewrite_rule( 'ml-api/v2/ecommerce/home$', "index.php?is_ml_page=true&ml_page_type=home" );
	}

	/**
	 * Custom template for viewing the home page.
	 */
	public function load_custom_template( $template ) {
		if ( 'home' !== self::$page_type ) {
			return $template;
		}

		return apply_filters(
			'mlwoo_template_home',
			MLWOO_TEMPLATE_PATH . 'home.php'
		);
	}

	/**
	 * Adds image URL to a term object and returns it.
	 */
	public static function add_to_categories_response( $term ) {
		$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		$image_url    = wp_get_attachment_url( $thumbnail_id );

		if ( false === $image_url ) {
			$image_url = \MLWoo\Ecommerce\WooCommerce\Utils\get_placeholder_image_url();
		}

		$term->image_url = $image_url;

		return $term;
	}

	/**
	 * Adds image URL, product category and product price to a product.
	 */
	public static function add_to_products_response( $post ) {
		$product           = wc_get_product( $post['id'] );
		$post['image_url'] = get_the_post_thumbnail_url( $post['id'], 'medium' );
		$post['category']  = \MLWoo\Ecommerce\WooCommerce\Utils\get_product_category( $post['id'] );
		$post['price']     = $product->get_price_html();

		return $post;
	}

	/**
	 * Returns product categories.
	 */
	public static function get_categories() {
		add_filter( 'mlwoo_get_taxonomy_terms', array( __CLASS__, 'add_to_categories_response' ) );
		$categories = \MLWoo\Ecommerce\WooCommerce\Utils\get_taxonomy_terms();
		remove_filter( 'mlwoo_get_taxonomy_terms', array( __CLASS__, 'add_to_categories_response' ) );

		return $categories;
	}

	/**
	 * Returns products.
	 */
	public static function get_products() {
		add_filter( 'mlwoo_get_posts', array( __CLASS__, 'add_to_products_response' ) );
		$products = \MLWoo\Ecommerce\WooCommerce\Utils\get_posts();
		remove_filter( 'mlwoo_get_posts', array( __CLASS__, 'add_to_products_response' ) );

		return $products;
	}
}
