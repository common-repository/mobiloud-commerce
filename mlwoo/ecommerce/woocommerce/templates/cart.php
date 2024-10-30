<?php header( 'Cache-Control: no-cache, must-revalidate, max-age=0' ); ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
		<?php wp_head(); ?>
		<?php global $woocommerce; ?>
	</head>
	<body>
		<div class="mlwoo mlwoo--cart">
			<?php echo do_shortcode( '[woocommerce_cart]' ); ?>
		</div>
		<script>
			if ( 'undefined' !== typeof nativeFunctions ) {
				nativeFunctions.syncCart( Number( <?php echo $woocommerce->cart->cart_contents_count; ?> ) );
			}
		</script>
		<footer class="mlwoo__footer">
			<?php wp_footer(); ?>
		</footer>
	</body>
</html>