<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
		<?php wp_head(); ?>
	</head>
	<body>

		<?php
			$categories = \MLWoo\Ecommerce\WooCommerce\Endpoints\Home::get_categories();
			$products   = \MLWoo\Ecommerce\WooCommerce\Endpoints\Home::get_products();
		?>

		<div class="mlwoo mlwoo--product-categories">

			<!-- Search field -->
			<div class="mlwoo__search mlwoo__search--product">
				<div class="mlwoo__search-wrapper">
					<input class="mlwoo__input" id="mlwoo__search-input" type="search" placeholder="<?php esc_html_e( 'Search products...' ); ?>" />
					<div class="mlwoo__clear-btn">
						<img src="<?php echo esc_url( MLWOO_URL . 'dist/images/clear.svg' ); ?>" />
					</div>
				</div>
			</div>
			<div id="mlwoo__search-results" class="mlwoo__search-results">
				<div class="mlwoo__grid mlwoo__grid--products"></div>
				<div class="mlwoo__load-more-spinner mlwoo__load-more-spinner--products">
					<img src="<?php echo esc_url( MLWOO_URL . 'dist/images/loading-dark.svg' ); ?>" />
				</div>
			</div>
			<!-- Search field. -->

			<!-- Categories -->
			<?php if ( apply_filters( 'mlwoo_product_categories_display_categories', true ) ) : ?>
				<div>
					<div class="mlwoo__grid mlwoo__grid--category">
						<?php foreach ( $categories as $category ) : ?>
							<a href="" onclick="nativeFunctions.handleLink( '<?php echo esc_url( sprintf( MLWOO_ENDPOINT . '/product-category/%s', $category->term_id ) ); ?>', '<?php echo $category->name; ?>', 'native' )" class="mlwoo__grid-item--square">
								<div class="mlwoo__grid-item__wrapper">
									<div class="mlwoo__grid-item__wrapper-inner" style="background-image: url( <?php echo esc_url( $category->image_url ); ?> )">
										<div class="mlwoo__grid-item-title mlwoo__grid-item-title--category">
											<?php echo esc_html( $category->name ); ?>
										</div>
									</div>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
					<div class="mlwoo__load-more-spinner mlwoo__load-more-spinner--categories">
						<img src="<?php echo esc_url( MLWOO_URL . 'dist/images/loading-dark.svg' ); ?>" />
					</div>
				</div>
			<?php endif; ?>
			<?php do_action( 'mlwoo_product_categories_replace_categories' ); ?>
			<!-- Categories. -->
		</div>

		<footer class="mlwoo__footer">
			<?php wp_footer(); ?>
		</footer>
	</body>
</html>