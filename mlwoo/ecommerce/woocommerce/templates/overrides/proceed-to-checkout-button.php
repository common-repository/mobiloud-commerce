<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="mlwoo__button mlwoo__button--primary">
	<a
		class="checkout-button button alt wc-forward"
		onclick="nativeFunctions.handleLink( '<?php echo MLWOO_ENDPOINT_ROOT . '/ecommerce/checkout'; ?>', 'Checkout', 'native' )"
	>
		<?php esc_html_e( 'Proceed to checkout', 'woocommerce' ); ?>
	</a>
<div>
