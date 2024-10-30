<?php
/**
 * The view component for the E-Commerce tab.
 *
 * @package MLWoo
 */

$ecommerce_status = \MLWoo\Admin\Helper::is_ecommerce_active();
$is_dev_mode = \MLWoo\Admin\Helper::is_dev_mode();
$ecommerce_platform = \Mobiloud::get_option( 'ml-ecommerce-platform', '' );
$assets_version = \Mobiloud::get_option( 'ml-ecommerce-assets-version', '1.0.0' );
$ml_active_plugins = \Mobiloud::get_option( 'ml-ecommerce-active-plugins', array() );
$cart_icon_status = \MLWoo\Admin\Helper::is_cart_icon_active();
?>

<div class="ml2-block">
	<div class="ml2-header"><h2><?php esc_html_e( 'E-commerce settings', 'mlwoo' ); ?></h2></div>
	<div class="ml2-body">
		<div class="ml-col-row">
			<?php
			if ( $ecommerce_status ) {
				printf(
					'<h4>%s</h4>',
					esc_html__( 'Ecommerce status:', 'mlwoo' )
				);
			} else {
				printf(
					'<p>%s</p>',
					esc_html__( 'Activate the e-commerce extension below to display more settings.', 'mlwoo' )
				);
			}
			?>
			<label><input <?php checked( $ecommerce_status, true, true ); ?> name="ml-ecommerce-status" type="radio" value="activate"><?php esc_html_e( 'Activate', 'mlwoo' ); ?></label>
			<label><input <?php checked( $ecommerce_status, false, true ); ?> name="ml-ecommerce-status" type="radio" value="deactivate"><?php esc_html_e( 'Deactivate', 'mlwoo' ); ?></label>
		</div>
		<div class="ml-col-row">
			<?php
			printf(
				'<h4>%s</h4>',
				esc_html__( 'E-commerce platform:', 'mlwoo' )
			);

			$ecomm_options = array(
				'0'           => '-- Select --',
				'woocommerce' => 'WooCommerce',
			);
			?>
			<select name="ml-ecommerce-platform">
				<?php foreach( $ecomm_options as $key => $value ) : ?>
					<option <?php selected( $key, $ecommerce_platform, true ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="ml-col-row">
			<h4><?php esc_html_e( 'Display cart icon on header:', 'mlwoo' ); ?></h4>
			<label for="ml-ecommerce-toggle-cart-icon">
				<input type="checkbox" id="ml-ecommerce-toggle-cart-icon" name="ml-ecommerce-toggle-cart-icon" <?php checked( $cart_icon_status, true ); ?>>
				<?php esc_html_e( 'Enable', 'mlwoo' ); ?>
			</label>
		</div>
		<div class="ml-col-row">
			<h4><?php esc_html_e( 'Assets version:', 'mlwoo' ); ?></h4>
			<input type="text" name="ml-ecommerce-assets-version" value="<?php echo esc_attr( $assets_version ); ?>">
		</div>
		<div class="ml-col-row">
		<?php
			if ( $is_dev_mode ) {
				printf(
					'<h4>%s</h4>',
					esc_html__( 'Dev mode:', 'mlwoo' )
				);
			} else {
				printf(
					'<h4>%s</h4>',
					esc_html__( 'Toggle dev mode.', 'mlwoo' )
				);
			}
			?>
			<label><input <?php checked( $is_dev_mode, true, true ); ?> name="ml-ecommerce-dev-mode" type="radio" value="activate"><?php esc_html_e( 'Activate', 'mlwoo' ); ?></label>
			<label><input <?php checked( $is_dev_mode, false, true ); ?> name="ml-ecommerce-dev-mode" type="radio" value="deactivate"><?php esc_html_e( 'Deactivate', 'mlwoo' ); ?></label>
		</div>

		<div class="ml-col-row">
			<h4>Disable specific plugins on <code>ml-api/v2/ecommerce</code> endpoint.</h4>
			<ul>
				<?php
					$active_plugins    = get_option( 'active_plugins' );
					$plugins           = get_plugins();
					$mandatory_plugins = array(
						'mobiloud-mobile-app-plugin/mobiloud.php',
						'mobiloud-commerce/mobiloud-ecommerce.php',
						'mobiloud-commerce-berkshire/mobiloud-commerce-berkshire.php',
						'woocommerce/woocommerce.php',
					);

					foreach ( $active_plugins as $path ) :
						if ( isset( $plugins[ $path ] ) ) :
							if ( in_array( $path, $ml_active_plugins ) ) {
								$checked = 'checked';
							} else {
								$checked = '';
							}

							if ( in_array( $path, $mandatory_plugins ) ) {
								$required = 'required';
							} else {
								$required = '';
							}
					?>
						<li>
							<label><input <?php echo esc_attr( $required ); ?> <?php echo esc_attr( $checked ); ?> name="ml-ecommerce-active-plugins[]" value="<?php echo esc_html( $path ); ?>" type="checkbox"><?php echo esc_html( $plugins[ $path ]['Name'] ); ?> <i><?php ! empty( $required ) && esc_html_e( '(Required)' ); ?></i></label>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			<ul>
		</div>
	</div>
</div>

