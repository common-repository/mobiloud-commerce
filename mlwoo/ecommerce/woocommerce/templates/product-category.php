<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
		<?php wp_head(); ?>
	</head>
	<body>
		<?php
			$cat_id   = (int)get_query_var( 'product_cat' );
			$products = \MLWoo\Ecommerce\WooCommerce\Endpoints\Product_Category::get_product_by_category( $cat_id );
			$cat_meta = \MLWoo\Ecommerce\WooCommerce\Endpoints\Product_Category::get_category_meta_by_id( $cat_id );
		?>
		<div class="mlwoo mlwoo--product-category">

			<!-- Category meta -->
			<div class="mlwoo--product-category__bg" style="background-image: url( <?php echo esc_url( $cat_meta['image_url'] ) ?> )">
				<div class="mlwoo--product-category__meta">
					<div class="mlwoo--product-category__title">
						<?php echo esc_html( $cat_meta['name'] ); ?>
					</div>
					<div class="mlwoo--product-category__description">
						<?php echo esc_html( $cat_meta['description'] ); ?>
					</div>
				</div>
			</div>
			<!-- Category meta. -->

			<!-- Products -->
			<div class="mlwoo__grid mlwoo__grid--products">
				<?php foreach ( $products['posts'] as $post ) : ?>
					<a onclick="nativeFunctions.handleLink( '<?php echo esc_url( sprintf( MLWOO_ENDPOINT . '?product_id=%s', $post['id'] ) ); ?>', '<?php echo $post['title']; ?>', 'native' )" class="mlwoo__grid-item--normal">
						<div class="mlwoo__grid-item__wrapper">
							<div class="mlwoo__grid-item__wrapper-inner">
								<img class="mlwoo__grid-item-image" src="<?php echo esc_url( $post['image_url'] ); ?>" />
								<div class="mlwoo__grid-item-meta">
									<div class="mlwoo__grid-item__category"><?php echo esc_html( $post['category'] ); ?></div>
									<div class="mlwoo__grid-item__title"><?php echo esc_html( $post['title'] ); ?></div>
									<div class="mlwoo__grid-item__price"><?php echo wp_kses_post( $post['price'] ); ?></div>
								</div>
							</div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
			<!-- Products. -->

		</div>
		<footer class="mlwoo__footer">
			<?php wp_footer(); ?>
		</footer>
	</body>
</html>