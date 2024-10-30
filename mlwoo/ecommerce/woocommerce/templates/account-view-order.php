<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
		<?php wp_head(); ?>
	</head>
	<body>
		<div class="mlwoo mlwoo--view-order">
			<?php echo do_shortcode( '[woocommerce_my_account]' ); ?>
		</div>
		<footer class="mlwoo__footer">
			<?php wp_footer(); ?>
		</footer>
	</body>
</html>
