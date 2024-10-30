<?php
/**
 * Contains logic to handle WooCommerce single Product Category page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

/**
 * Methods to register endpoint and templates for a
 * single Product Category MobiLoud endpoint.
 */
class Product_Category extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ) );
	}

	/**
	 * Registers the endpoint for product categories.
	 *
	 * product-category: ml-api/v2/ecommerce/product-category/<product_category>/
	 */
	public function register_endpoint() {
		add_rewrite_rule( 'ml-api/v2/ecommerce/product-category/([^/]+)/?$', 'index.php?&is_ml_page=true&ml_page_type=product-category&product_cat=$matches[1]' );
	}

	/**
	 * Custom template for viewing the a single product category page.
	 */
	public function load_custom_template( $template ) {
		if ( 'product-category' !== self::$page_type ) {
			return $template;
		}

		return apply_filters(
			'mlwoo_template_product_category',
			MLWOO_TEMPLATE_PATH . 'product-category.php'
		);
	}

	/**
	 * Returns products by product category ID.
	 */
	public static function get_product_by_category( $category_id = 0 ) {
		if ( 0 === $category_id ) {
			return;
		}

		add_filter( 'mlwoo_get_posts', '\MLWoo\Ecommerce\WooCommerce\Utils\add_to_products_response' );
		$products = \MLWoo\Ecommerce\WooCommerce\Utils\get_posts( array(
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $category_id,
				),
			),
		) );
		remove_filter( 'mlwoo_get_posts', '\MLWoo\Ecommerce\WooCommerce\Utils\add_to_products_response' );

		return $products;
	}

	/**
	 * Returns product category term meta by product ID. 
	 */
	public static function get_category_meta_by_id( $category_id ) {
		if ( 0 === $category_id ) {
			return;
		}

		$thumbnail_id = get_term_meta( $category_id, 'thumbnail_id', true );
		$image_url    = wp_get_attachment_url( $thumbnail_id );

		if ( false === $image_url ) {
			$image_url = \MLWoo\Ecommerce\WooCommerce\Utils\get_placeholder_image_url();
		}

		$term = get_term_by( 'id', $category_id, 'product_cat' );

		return array(
			'name'        => $term->name,
			'description' => $term->description,
			'image_url'   => $image_url,
		);
	}
}
