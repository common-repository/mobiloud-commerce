<?php
/**
 * Contains logic to handle WooCommerce view orders
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

/**
 * Methods to register endpoint and templates for the
 * orders MobiLoud endpoint.
 */
class Account_Orders extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ) );
		add_filter( 'woocommerce_locate_template', array( $this, 'load_custom_override_template' ), 9999, 3 );

		add_action( 'wp_ajax_load_more_orders', array( $this, 'load_more_orders' ) );
		add_action( 'wp_ajax_nopriv_load_more_orders', array( $this, 'load_more_orders' ) );
	}

	/**
	 * Registers the endpoint to view the orders
	 *
	 * orders: ml-api/v2/ecommerce/account/orders
	 */
	public function register_endpoint() {
		$order_page_id = get_option( 'woocommerce_myaccount_page_id' );
		add_rewrite_rule( 'ml-api/v2/ecommerce/account/orders$', "index.php?is_ml_page=true&ml_page_type=account-orders&page_id={$order_page_id}&orders" );
	}

	/**
	 * Custom template for vieweing orders.
	 */
	public function load_custom_template( $template ) {
		if ( 'account-orders' !== self::$page_type ) {
			return $template;
		}

		return MLWOO_TEMPLATE_PATH . 'orders.php';
	}

	/**
	 * Overrides certain WooCommerce templates necessary for view orders page.
	 */
	public function load_custom_override_template( $template, $template_name, $template_path ) {
		if ( 'account-orders' !== self::$page_type ) {
			return $template;
		}

		$basename = basename( $template );

		if ( 'form-login.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/form-login.php';
		}

		if ( 'orders.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/orders.php';
		}

		return $template;
	}

	/**
	 * Ajax handler to load more orders.
	 */
	public function load_more_orders() {
		$current_page = filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT );

		$customer_orders = wc_get_orders( array(
			'customer' => get_current_user_id(),
			'page'     => $current_page,
			'paginate' => true,
		) );

		ob_start();

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

		$html = \ob_get_clean();
		$data = array(
			'data' => $html,
		);

		echo wp_send_json_success( $data );
	}
}
