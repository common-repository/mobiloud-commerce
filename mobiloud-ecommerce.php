<?php
/**
 * Plugin Name:     MobiLoud Commerce
 * Plugin URI:      http://www.mobiloud.com
 * Description:     Adds support for WooCommerce based sites.
 * Author:          MobiLoud
 * Author URI:		https://www.mobiloud.com
 * Text Domain:     mlwoo
 * Domain Path:     /languages
 * Version:         1.0.4
 *
 * @package         MLWoo
 */

if ( ! defined( 'MLWOO_PATH' ) ) {
	define( 'MLWOO_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'MLWOO_URL' ) ) {
	define( 'MLWOO_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'MLWOO_ENDPOINT' ) ) {
	define( 'MLWOO_ENDPOINT', get_site_url() . '/ml-api/v2/ecommerce' );
}

if ( ! defined( 'MLWOO_TEMPLATE_PATH' ) ) {
	define( 'MLWOO_TEMPLATE_PATH', MLWOO_PATH . 'mlwoo/ecommerce/woocommerce/templates/' );
}

if ( ! defined( 'MLWOO_ENDPOINT_ROOT' ) ) {
	define( 'MLWOO_ENDPOINT_ROOT', get_site_url() . '/ml-api/v2' );
}

if ( ! defined( 'MLWOO_DEV_MODE' ) ) {
	define( 'MLWOO_DEV_MODE', true );
}

if ( false !== strpos( $_SERVER['HTTP_USER_AGENT'], 'Mobiloud' ) || isset( $_SERVER['HTTP_X_ML_PLATFORM'] ) || ( isset( $_COOKIE['MLWOO_IS_APP'] ) && 'yes' === $_COOKIE['MLWOO_IS_APP'] ) ) {
	setcookie( 'MLWOO_IS_APP', 'yes', 0, '/' );
	define( 'MLWOO_IS_APP', true );
} else {
	if ( 'activate' === get_option( 'ml-ecommerce-dev-mode', 'deactivate' ) ) {
		define( 'MLWOO_IS_APP', true );
	} else {
		define( 'MLWOO_IS_APP', false );
	}
}

if ( file_exists( MLWOO_PATH . 'vendor/autoload.php' ) ) {
	require_once 'vendor/autoload.php';
	require_once 'mlwoo/init.php';
}

register_activation_hook( __FILE__, 'mlwoo_commerce_check_parent_plugin' );
function mlwoo_commerce_check_parent_plugin() {
	$is_active = is_plugin_active( 'mobiloud-mobile-app-plugin/mobiloud.php' );
	$act_code  = get_option( 'ml_code', false );

	/**
	 * Return if parent plugin is activated.
	 */
	if ( $is_active && $act_code ) {
		update_option( 'mobiloud_popup_on_activation', 'no' );
		return;
	}

	/**
	 * Add option if parent plugin is not activated.
	 */
	update_option( 'mobiloud_popup_on_activation', 'yes' );
}

add_action( 'admin_head', 'mlwoo_print_plugin_activation_popup' );
function mlwoo_print_plugin_activation_popup() {
	$show_popup = get_option( 'mobiloud_popup_on_activation', 'no' );

	if ( 'no' === $show_popup ) {
		return;
	}
	?>

	<div id="mlwoo-parent-plugin-notice-popup-overlay"></div>
	<div id="mlwoo-parent-plugin-notice-popup">
		<div class="mlwoo-popup-wrapper">
			<div class="mlwoo-popup-title">
				<h2><?php esc_html_e( 'Important notice!', 'mlwoo' ); ?></h2>
			</div>
			<div class="mlwoo-popup-content">
				<?php esc_html_e( 'The MobiLoud Commerce extension is an add-on to the core MobiLoud plugin, in order for it to work you will need to have an active subscription, as this is a paid service. Please schedule a demo with our team to learn more.', 'mlwoo' ); ?>
			</div>
			<div class="mlwoo-popup-controls-container">
				<a class="mlwoo-popup-demo-link" target="_blank" href="https://www.mobiloud.com/woocommerce-mobile-app#pricing">
					<?php esc_html_e( 'Schedule a demo', 'mlwoo' ); ?>
				</a>
				<span class="mlwoo-popup-demo-close">
					<?php esc_html_e( 'Close', 'mlwoo' ); ?>
				</span>
			</div>
		</div>
	</div>
	<?php
	update_option( 'mobiloud_popup_on_activation', 'no' );
}

/**
 * Loads a popup on plugin activation.
 */
add_action( 'admin_enqueue_scripts', function() {
	$show_popup = get_option( 'mobiloud_popup_on_activation', 'no' );

	if ( 'no' === $show_popup ) {
		return;
	}

	wp_enqueue_script( 'mlwoo-admin-dc-script', MLWOO_URL . 'src/js/admin/dependency-checker.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_style( 'mlwoo-admin-dc-style', MLWOO_URL . 'src/scss/admin/dependency-checker.css', array(), '1.0', 'all' );
} );

/**
 * Loads a customised checkout.js script instead of WooCommerce's
 * on the MobiLouds checkout endpoint.
 */
add_action( 'wp_head', function() {
	$page_type = get_query_var( 'ml_page_type' );

	if ( 'checkout' !== $page_type ) {
		return;
	}

	wp_deregister_script( 'wc-checkout' );
	wp_register_script( 'wc-checkout', MLWOO_URL . 'src/js/checkout.js', array( 'jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n' ) );
	wp_localize_script( 'wc-checkout', 'wc_checkout_params', array(
		'ajax_url'                  => WC()->ajax_url(),
		'wc_ajax_url'               => \WC_AJAX::get_endpoint( '%%endpoint%%' ),
		'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
		'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
		'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
		'option_guest_checkout'     => get_option( 'woocommerce_enable_guest_checkout' ),
		'checkout_url'              => \WC_AJAX::get_endpoint( 'checkout' ),
		'is_checkout'               => is_checkout() && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] ) ? 1 : 0,
		'i18n_checkout_error'       => esc_attr__( 'Error processing checkout. Please try again.', 'woocommerce' ),
	) );
} );
