<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_user_logged_in() ) {
	return;
}

?>

<div class="mlwoo-account__login-form">
	<div class="mlwoo-account__login-form-instructions">
		<div class="mlwoo-account__login-form-instructions-text"><?php esc_html_e( apply_filters( 'mlwoo_checkout_login_header_text', 'In order to access this page you must be logged-in.' ), 'mlwoo' ); ?></div>
		<div>
			<a
				class="mlwoo__button mlwoo__button--primary mlwoo__login-button"
				onclick="nativeFunctions.handleButton( 'login', null ,null )"
				>
				<?php esc_html_e( apply_filters( 'mlwoo_checkout_login_button_text', 'Login' ), 'mlwoo' ); ?>
			</a>
		</div>
	</div>
</div>
