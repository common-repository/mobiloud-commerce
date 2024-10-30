<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
		<?php wp_head(); ?>
	</head>
	<body>
		<?php
		global $post, $product, $woocommerce;
		?>
		<div class="mlwoo mlwoo--single-product">

			<div class="mlwoo__pill"><?php esc_html_e( 'Product added to the cart.', 'mlwoo' ); ?></div>

			<!-- Breadcrumbs -->
			<div class="mlwoo--single-product__breadcrumb">
				<?php \MLWoo\Ecommerce\WooCommerce\Endpoints\Single_Product::breadcrumb(); ?>
			</div>
			<!-- Breadcrumbs. -->

			<?php
				while ( have_posts() ) {
					the_post();

					/**
					 * Product main image and gallery ids.
					 */
					$image_ids = array_merge(
						array(
							(int) $product->get_image_id(),
						),
						$product->get_gallery_image_ids()
					);

					/**
					 * OnsenUI product image carousel container.
					 */
					echo '<div class="mlwoo--single-product__carousel"><div class="mlwoo--single-product__slider">';
					foreach ( $image_ids as $image_id ) {

						/**
						 * Get image URL by attachment ID.
						 */
						$url = wp_get_attachment_image_url( $image_id, 'medium' );

						/**
						 * OnsenUI product image carousel ID.
						 */
						printf(
							'
							<div>
								<img class="mlwoo--single-product__fp-image-item" src="%s" />
							</div>
							',
							esc_url( $url )
						);
					}
					echo '</div></div>';

					echo '<div class="mlwoo--single-product__thumbnail-nav" id="mlwoo--single-product__thumbnail-nav">';
					foreach ( $image_ids as $image_id ) {
						$url = wp_get_attachment_image_url( $image_id, 'medium' );
						printf(
							'<img src="%s" />',
							esc_url( $url )
						);
					}
					echo '</div>';

					/**
					 * Product title.
					 */
					the_title( '<div class="mlwoo--single-product__title">', '</div>' );

					/**
					 * Product short description.
					 */
					printf( '<div class="mlwoo--single-product__short-description">%s</div>', wp_kses_post( $post->post_excerpt ) );

					/**
					 * Separator.
					 */
					printf( '<div class="mlwoo--single-product__separator"></div>' );

					/**
					 * Product SKU.
					 */
					if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) :
						?>

						<span class="mlwoo--single-product__sku-wrapper"><?php esc_html_e( 'SKU:', 'mlwoo' ); ?>
							<span class="mlwoo--single-product__sku">
								<?php echo ( $sku = $product->get_sku() ) ? esc_html( $sku ) : esc_html__( 'N/A', 'mlwoo' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</span>

					<?php endif;

					/**
					 * Product price.
					 */
					printf(
						'
						<div class="mlwoo--single-product__price-and-quantity">
							<div class="mlwoo--single-product__price">%s</div>
						</div>
						',
						$product->get_price_html() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);

					/**
					 * Add to cart button.
					 */
					if ( $product->is_purchasable() && $product->is_in_stock() ) {
						echo wc_get_stock_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						<form class="mlwoo--single-product__add-to-cart-form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
							<div class="mlwoo--single-product__quantity">
								<div data-quantity-action="decrease" class="mlwoo--single-product__quantity-input-control mlwoo--single-product__quantity-input-control--decrease">-</div>
								<input
									type="number"
									id="mlwoo--single-product__quantity-input"
									class="mlwoo__input mlwoo--single-product__quantity-input"
									min="<?php echo esc_attr( $product->get_min_purchase_quantity() ); ?>"
									max="<?php echo esc_attr( $product->get_max_purchase_quantity() ); ?>"
									value="1"
								/></input>
								<div data-quantity-action="increase" class="mlwoo--single-product__quantity-input-control mlwoo--single-product__quantity-input-control--increase">+</div>
							</div>
							<button
								type="button"
								id="mlwoo--single-product__add-to-cart-button"
								class="mlwoo__button mlwoo__button--secondary mlwoo--single-product__add-to-cart-button"
								name="add-to-cart"
								data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
							>
								<span class="mlwoo--single-product__add-to-cart-text">
									<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
								</span>
								<span class="mlwoo--single-product__add-to-cart-spinner">
									<?php require_once MLWOO_PATH . 'dist/images/loading.svg' ?>
								</span>
							</button>
						</form>
						<?php
					}
				}
			?>
		</div>
		<footer class="mlwoo__footer">
			<?php wp_footer(); ?>
			<script>
				if ( 'undefined' !== typeof nativeFunctions ) {
					nativeFunctions.syncCart( <?php echo $woocommerce->cart->cart_contents_count; ?> );
				}
			</script>
		</footer>
	</body>
</html>