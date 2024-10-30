<?php

function mlwoo_load_plugin() {
	if ( ! class_exists( 'Mobiloud' ) ) {
		add_action( 'admin_notices', 'mlwoo_mobiloud_missing_notice' );
		return;
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'mlwoo_woocommerce_missing_notice' );
		return;
	}

	( new \MLWoo\Admin\MLWoo() )->init();

	if ( 'woocommerce' === \Mobiloud::get_option( 'ml-ecommerce-platform', '' ) ) {
		require_once 'ecommerce/woocommerce/utils.php';
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Base() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Home() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Single_Product() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Product_Categories() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Product_Category() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Account() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Account_Edit_Address() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Account_Orders() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Account_View_Order() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Cart() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Checkout() )->init();
		( new \MLWoo\Ecommerce\WooCommerce\Endpoints\Thank_You() )->init();
	}
}
add_action( 'plugins_loaded', 'mlwoo_load_plugin' );

/**
 * Displays a warning if MobiLoud News is not activated.
 */
function mlwoo_woocommerce_missing_notice() {
	?>
	<div class="notice notice-warning is-dismissible">
		<?php printf( '<p>%s</p>', esc_html__( 'MobiLoud WooCommerce Extension requires WooCommerce to be installed and activated.', 'mlwoo' ) ); ?>
	</div>
	<?php
}

/**
 * Displays a warning if WooCommerce is not activated.
 */
function mlwoo_mobiloud_missing_notice() {
	?>
	<div class="notice notice-warning is-dismissible">
		<?php printf( '<p>%s</p>', esc_html__( 'MobiLoud WooCommerce Extension requires Mobiloud to be installed and activated.', 'mlwoo' ) ); ?>
	</div>
	<?php
}

