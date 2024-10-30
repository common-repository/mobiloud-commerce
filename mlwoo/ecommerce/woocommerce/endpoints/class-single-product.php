<?php
/**
 * Contains logic to handle WooCommerce Single Product page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

/**
 * Methods to register endpoint and templates for the
 * Single Product page MobiLoud endpoint.
 */
class Single_Product extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_action( 'pre_get_posts', array( $this, 'set_product_id' ) );
		add_action( 'template_include', array( $this, 'load_custom_template' ), 11 );
		add_action( 'wp_ajax_mlwoo_add_to_cart', array( $this, 'add_to_cart' ), 20 );
		add_action( 'wp_ajax_nopriv_mlwoo_add_to_cart', array( $this, 'add_to_cart' ), 20 );
	}

	/**
	 * Registers the endpoint for product categories.
	 *
	 * single page: ml-api/v2/ecommerce
	 */
	public function register_endpoint() {
		add_rewrite_rule( 'ml-api/v2/ecommerce$', 'index.php?p=1&post_type=product&is_ml_page=true&ml_page_type=single-product' );
	}

	/**
	 * Sets `p` to the `product_id` on the `pre_get_posts` hook.
	 */
	public function set_product_id( $query ) {
		if ( is_admin() && ! $query->is_main_query() ) {
			return;
		}

		if ( ! self::$is_mlwoo ) {
			return;
		}

		$product_id = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_STRING );

		if ( false === $product_id ) {
			return;
		}

		$query->set( 'p', $product_id );
	}

	/**
	 * Custom template for viewing a single product.
	 */
	public function load_custom_template( $template ) {
		if ( 'single-product' !== self::$page_type ) {
			return $template;
		}

		return apply_filters(
			'mlwoo_template_single_product',
			MLWOO_TEMPLATE_PATH . 'single-product.php'
		);
	}

	/**
	 * Renders a navbar.
	 */
	public static function breadcrumb() {
		$args = array(
			'home' => _x( 'Home', 'breadcrumb', 'mlwoo' ),
		);

		$breadcrumbs = new \WC_Breadcrumb();

		if ( ! empty( $args['home'] ) ) {
			$breadcrumbs->add_crumb( $args['home'], apply_filters( 'woocommerce_breadcrumb_home_url', home_url() ) );
		}

		$args['breadcrumb'] = $breadcrumbs->generate();
		$breadcrumb         = $args['breadcrumb'];

		if ( ! empty( $breadcrumb ) ) {
			?>
				<nav class="mlwoo-breadcrumb">
			<?php
			foreach ( $breadcrumb as $key => $crumb ) {
				if ( ! empty( $crumb[1] ) && count( $breadcrumb ) !== $key + 1 ) {
					if ( $key > 0 ) {
						$product_cat_slug = \basename( $crumb[1] );
						$term             = get_term_by( 'slug', $product_cat_slug, 'product_cat' );
					}
					?>
					<a
						onclick="nativeFunctions.handleLink( '<?php echo esc_url( MLWOO_ENDPOINT . '/product-category/' . $term->term_id ); ?>', '<?php echo esc_html( $crumb[0] ); ?>', 'native' )"
					>
						<?php echo esc_html( $crumb[0] ); ?></a>
					<?php
				} else {
					echo esc_html( $crumb[0] );
				}

				if ( count( $breadcrumb ) !== $key + 1 ) {
					echo ' / ';
				}
			}
			?>
			</nav>
			<?php
		}
	}

	/**
	 * Ajax handler to add a product to cart.
	 */
	public function add_to_cart() {
		global $woocommerce;

		$product_id = filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT );
		$quantity   = filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT );

		if ( false === $product_id ) {
			wp_send_json_error( __( 'Product ID not found.', 'mlwoo' ) );
		}

		$was_added_to_cart = false;
		$adding_to_cart    = wc_get_product( $product_id );

		if ( ! $adding_to_cart ) {
			wp_send_json_error( __( 'Product not found.', 'mlwoo' ) );
		}

		$add_to_cart_handler = $adding_to_cart->get_type();
		WC()->cart->add_to_cart( $product_id, $quantity );

		wp_send_json_success( array(
			'cartCount' => $woocommerce->cart->cart_contents_count,
			'message'   => __( 'Product added to cart.', 'mlwoo' ),
		) );
	}
}
