<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
		<?php wp_head(); ?>
	</head>
	<body>
		<div class="mlwoo mlwoo--edit-address">
			<div class="mlwoo__pill mlwoo__pill--success"><?php esc_html_e( 'Address updated.', 'mlwoo' ); ?></div>
			<div class="mlwoo__pill mlwoo__pill--fail"><?php esc_html_e( 'Failed to update address.', 'mlwoo' ); ?></div>
			<?php echo do_shortcode( '[woocommerce_my_account]' ); ?>
		</div>
		<footer class="mlwoo__footer">
			<?php wp_footer(); ?>
		</footer>
	</body>
</html>