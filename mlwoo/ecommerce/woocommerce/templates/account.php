<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
		<?php wp_head(); ?>
	</head>
	<body>
		<div class="mlwoo mlwoo--account">
			<div class="mlwoo--account__nav-wrapper">
				<ul class="mlwoo--account__nav-menu mlwoo-account__nav-menu--level-1">
					<li><a onclick="nativeFunctions.handleLink( '<?php echo esc_url( MLWOO_ENDPOINT . '/account/orders' ); ?>', 'My Orders', 'native' )"><?php esc_html_e( 'My Orders', 'mlwoo' ); ?></a></li>
					<li><a onclick="nativeFunctions.handleLink( '<?php echo esc_url( MLWOO_ENDPOINT . '/account/edit-address/billing' ); ?>', 'Billing Address', 'native' )"><?php esc_html_e( 'Billing Address', 'mlwoo' ); ?></a></li>
					<li><a onclick="nativeFunctions.handleLink( '<?php echo esc_url( MLWOO_ENDPOINT . '/account/edit-address/shipping' ); ?>', 'Shipping Address', 'native' )"><?php esc_html_e( 'Shipping Address', 'mlwoo' ); ?></a></li>
				</ul>
			</div>
		</div>
		<footer class="mlwoo__footer">
			<?php wp_footer(); ?>
		</footer>
	</body>
</html>
