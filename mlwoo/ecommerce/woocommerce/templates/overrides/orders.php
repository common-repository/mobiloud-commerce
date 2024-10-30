<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<?php if ( $has_orders ) : ?>

	<div class="mlwoo--orders__order-list-wrapper">
		<h3 class="mlwoo--orders__orders-title">
			<?php esc_html_e( 'Your orders:', 'mlwoo' ); ?>
		</h3>
		<div class="mlwoo--orders__order-list">
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				?>
				<a class="mlwoo--orders__order-item-row" onclick="nativeFunctions.handleLink( '<?php echo esc_url( sprintf( MLWOO_ENDPOINT . '/account/order/%s', $order->get_order_number() ) ); ?>', 'Order', 'native' )">
					<div class="woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
						<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
							<div class="mlwoo--orders__item-cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
									<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

								<?php elseif ( 'order-number' === $column_id ) : ?>
									<span class="mlwoo--orders__order-col-title">
										<?php esc_html_e( 'Order: ', 'mlwoo' ); ?>
									</span>
									<span class="mlwoo--orders__order-col-value">
										<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
									</span>

								<?php elseif ( 'order-date' === $column_id ) : ?>
									<span class="mlwoo--orders__order-col-title">
										<?php esc_html_e( 'Date: ', 'mlwoo' ); ?>
									</span>
									<span class="mlwoo--orders__order-col-value">
										<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>
									</span>
								<?php elseif ( 'order-status' === $column_id ) : ?>
									<span class="mlwoo--orders__order-col-title">
										<?php esc_html_e( 'Status: ', 'mlwoo' ); ?>
									</span>
									<span class="mlwoo--orders__order-col-value">
										<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
									</span>
								<?php elseif ( 'order-total' === $column_id ) : ?>
									<span class="mlwoo--orders__order-col-title">
										<?php esc_html_e( 'Total: ', 'mlwoo' ); ?>
									</span>
									<span class="mlwoo--orders__order-col-value">
										<?php
										/* translators: 1: formatted order total 2: total order items */
										echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
										?>
									</span>
								<?php elseif ( 'order-actions' === $column_id ) : ?>
									<?php
									$actions = wc_get_account_orders_actions( $order );

									if ( ! empty( $actions ) ) {
										foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
											// echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
										}
									}
									?>
								<?php endif; ?>
								</div>
						<?php endforeach; ?>
					</div>
				</a>
				<?php
			}
			?>
		</div>
	</div>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<button class="mlwoo__button mlwoo__button--primary mlwoo--orders__load-more-orders" type="button">
		<span class="mlwoo--orders__load-more-text">
			<?php esc_html_e( 'Load more', 'mlwoo' ); ?>
		</span>
		<span class="mlwoo--orders__add-to-cart-spinner">
			<?php require_once MLWOO_PATH . 'dist/images/loading.svg' ?>
		</span>
	</button>

<?php else : ?>
	<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Browse products', 'woocommerce' ); ?>
		</a>
		<?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
