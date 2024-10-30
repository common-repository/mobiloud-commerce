<?php
/**
 * Contains logic required by all MobiLoud WooCommerce endpoints.
 */
namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

/**
 * This class is extended by all other classes in this directory.
 * Sets query vars and variables and loads JS/CSS assets depending
 * on the page type.
 */
class Base {

	/**
	 * Set to true on an `ml-api/v2/ecommerce` endpoint.
	 */
	public static $is_mlwoo = false;

	/**
	 * The current page type.
	 * For ex; sets to `cart` on a cart page, `single-product`
	 * on a single product page.
	 */
	public static $page_type = false;

	/**
	 * JS & CSS assets version.
	 * Can be set via MobiLoud settings menu.
	 */
	public static $asset_version = 1;

	/**
	 * Starts here.
	 */
	public function init() {
		add_filter( 'query_vars', array( $this, 'set_query_vars' ) );
		add_action( 'parse_query', array( $this, 'setup_base_variables' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_theme_assets' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_fonts' ) );
		add_action( 'wp_footer', array( $this, 'apply_codemirror_css' ), 999999 );
	}

	/**
	 * Sets query vars which are used on the
	 * `ml-api/v2/ecommerce` endpoint.
	 */
	public function set_query_vars( $vars ) {
		$vars[] = 'is_ml_page';
		$vars[] = 'ml_page_type';
		
		return $vars;
	}

	/**
	 * Sets up necessary variables.
	 */
	public function setup_base_variables() {
		self::$page_type = get_query_var( 'ml_page_type' );
		
		if ( 'true' === get_query_var( 'is_ml_page' ) ) {
			self::$is_mlwoo = true;
			add_action( 'show_admin_bar', '__return_false' );
		}

		self::$asset_version = \Mobiloud::get_option( 'ml-ecommerce-assets-version', '1.0.0' );
	}

	/**
	 * Removes style that are loaded by the current
	 * active theme.s
	 */
	public function dequeue_theme_assets() {
		if ( ! self::$is_mlwoo ) {
			return;
		}

		$wp_styles  = wp_styles();
		$themes_uri = get_theme_root_uri();

		foreach ( $wp_styles->registered as $wp_style ) {
			if ( false !== strpos( $wp_style->src, $themes_uri ) ) {
				wp_deregister_style( $wp_style->handle );
			}
		}
	}

	/**
	 * Loads CSS and JS assets depending on the page type.
	 */
	public function enqueue_scripts() {
		global $woocommerce;

		if ( ! MLWOO_IS_APP ) {
			return;
		}

		if ( apply_filters( 'mlwoo_' . self::$page_type . '_css', true ) ) {
			wp_enqueue_style(
				'mlwoo-' . self::$page_type . "-style",
				MLWOO_URL . "dist/css/" . self::$page_type . ".css",
				array(),
				self::$asset_version,
				'all'
			);
		}

		if ( apply_filters( 'mlwoo_' . self::$page_type . '_js', true ) ) {
			if ( 'checkout' === self::$page_type ) {
				return;
			}

			wp_enqueue_script(
				'mlwoo-' . self::$page_type . "-script",
				MLWOO_URL . "dist/js/" . self::$page_type . ".bundle.js",
				array( 'jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n' ),
				self::$asset_version,
				true
			);

			wp_localize_script(
				'mlwoo-' . self::$page_type . "-script",
				'mlwoo',
				array(
					'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
					'cartCount' => $woocommerce->cart->cart_contents_count,
				)
			);
		}
	}

	/**
	 * Enqueues default Google fonts.
	 */
	public function enqueue_fonts() {
		if ( ! MLWOO_IS_APP ) {
			return;
		}

		if ( apply_filters( 'mlwoo_enqueue_default_fonts', true ) ) {
			wp_enqueue_style( 'ml-base-default-fonts', 'https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Nunito:ital,wght@0,300;0,700;1,400&display=swap', array(), null );
		}
	}

	/**
	 * Applies CSS added through Editors > Commerce Custom CSS.
	 */
	public function apply_codemirror_css() {
		if ( ! self::$is_mlwoo ) {
			return;
		}

		$css_option_name = 'mlwoo-commerce-css-textarea';
		$custom_css      = stripslashes( get_option( $css_option_name, '' ) );

		if ( ! empty( $custom_css ) ) {
			?>
				<style type="text/css" media="screen"><?php echo $custom_css; ?></style>
			<?php
		}
	}
}
