<?php
/**
 * Contains logic to handle WooCommerce edit address page
 * on MobiLoud endpoint.
 */

namespace MLWoo\Ecommerce\WooCommerce\Endpoints;

/**
 * Methods to register endpoint and templates for the
 * Edit Address MobiLoud endpoint.
 */
class Account_Edit_Address extends Base {

	/**
	 * Starts here.
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'template_include', array( $this, 'load_custom_template' ) );

		if ( MLWOO_IS_APP ) {
			add_filter( 'woocommerce_locate_template', array( $this, 'load_custom_override_template' ), 9999, 3 );
			add_action( 'wp_ajax_ml_save_address', array( $this, 'save_address' ) );
			add_action( 'wp_ajax_nopriv_ml_save_address', array( $this, 'save_address' ) );
			add_action( 'wp_print_scripts', array( $this, 'remove_specific_scripts_loaded_by_woocommerce' ) );
		}
	}

	/**
	 * Registers the endpoint to edit the
	 * Billing and Shipping addresses.
	 *
	 * billing:  ml-api/v2/ecommerce/account/edit-address/billing
	 * shipping: ml-api/v2/ecommerce/account/edit-address/shipping
	 */
	public function register_endpoint() {
		$account_page_id = get_option( 'woocommerce_myaccount_page_id' );
		add_rewrite_rule( 'ml-api/v2/ecommerce/account/edit-address/billing$', "index.php?is_ml_page=true&ml_page_type=account-edit-address&page_id={$account_page_id}&edit-address=billing&pagename=my-account" );
		add_rewrite_rule( 'ml-api/v2/ecommerce/account/edit-address/shipping$', "index.php?is_ml_page=true&ml_page_type=account-edit-address&page_id={$account_page_id}&edit-address=shipping&pagename=my-account" );
	}

	/**
	 * Custom template for editing billing and shipping addresses.
	 */
	public function load_custom_template( $template ) {
		if ( 'account-edit-address' !== self::$page_type ) {
			return $template;
		}

		return MLWOO_TEMPLATE_PATH . 'account-edit-address.php';
	}

	/**
	 * Overrides certain WooCommerce templates necessary for the edit address page.
	 */
	public function load_custom_override_template( $template, $template_name, $template_path ) {
		if ( 'account-edit-address' !== self::$page_type ) {
			return $template;
		}

		$basename = basename( $template );

		if ( 'form-edit-address.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/form-edit-address.php';
		}

		if ( 'form-login.php' === $basename ) {
			return MLWOO_TEMPLATE_PATH . 'overrides/form-login.php';
		}

		return $template;
	}

	/**
	 * Remove the SelectWoo script loaded by WooCommerce.
	 * We don't need this in the app.
	 */
	public function remove_specific_scripts_loaded_by_woocommerce() {
		if ( 'account-edit-address' === self::$page_type ) {
			wp_deregister_script( 'selectWoo' );
			wp_dequeue_script( 'selectWoo' );
		}
	}

	/**
	 * Ajax handler to save/update the address.
	 */
	public function save_address() {
		$nonce_value = wc_get_var( $_REQUEST['woocommerce-edit-address-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

		if ( ! wp_verify_nonce( $nonce_value, 'woocommerce-edit_address' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'ml_save_address' !== $_POST['action'] ) {
			return;
		}

		wc_nocache_headers();

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		$customer = new \WC_Customer( $user_id );

		if ( ! $customer ) {
			return;
		}

		$load_address = filter_input( INPUT_POST, 'address_type', FILTER_SANITIZE_STRING );

		if ( ! isset( $_POST[ $load_address . '_country' ] ) ) {
			return;
		}

		$address = \WC()->countries->get_address_fields( wc_clean( wp_unslash( $_POST[ $load_address . '_country' ] ) ), $load_address . '_' );

		$notices_array = array();

		foreach ( $address as $key => $field ) {
			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}

			// Get Value.
			if ( 'checkbox' === $field['type'] ) {
				$value = (int) isset( $_POST[ $key ] );
			} else {
				$value = isset( $_POST[ $key ] ) ? wc_clean( wp_unslash( $_POST[ $key ] ) ) : '';
			}

			// Validation: Required fields.
			if ( ! empty( $field['required'] ) && empty( $value ) ) {
				/* translators: %s: Field name. */
				// wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), $field['label'] ), 'error', array( 'id' => $key ) );
				$notices_array[] = sprintf( __( '%s is a required field.', 'woocommerce' ), $field['label'] );
			}

			if ( ! empty( $value ) ) {
				// Validation and formatting rules.
				if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
					foreach ( $field['validate'] as $rule ) {
						switch ( $rule ) {
							case 'postcode':
								$country = wc_clean( wp_unslash( $_POST[ $load_address . '_country' ] ) );
								$value   = wc_format_postcode( $value, $country );

								if ( '' !== $value && ! \WC_Validation::is_postcode( $value, $country ) ) {
									switch ( $country ) {
										case 'IE':
											$postcode_validation_notice = __( 'Please enter a valid Eircode.', 'woocommerce' );
											break;
										default:
											$postcode_validation_notice = __( 'Please enter a valid postcode / ZIP.', 'woocommerce' );
									}
									// wc_add_notice( $postcode_validation_notice, 'error' );
									$notices_array[] = $postcode_validation_notice;
								}
								break;
							case 'phone':
								if ( '' !== $value && ! \WC_Validation::is_phone( $value ) ) {
									/* translators: %s: Phone number. */
									// wc_add_notice( sprintf( __( '%s is not a valid phone number.', 'woocommerce' ), '<strong>' . $field['label'] . '</strong>' ), 'error' );
									$notices_array[] = sprintf( __( '%s is not a valid phone number.', 'woocommerce' ), '<strong>' . $field['label'] . '</strong>' );
								}
								break;
							case 'email':
								$value = strtolower( $value );

								if ( ! is_email( $value ) ) {
									/* translators: %s: Email address. */
									// wc_add_notice( sprintf( __( '%s is not a valid email address.', 'woocommerce' ), '<strong>' . $field['label'] . '</strong>' ), 'error' );
									$notices_array[] = sprintf( __( '%s is not a valid email address.', 'woocommerce' ), '<strong>' . $field['label'] . '</strong>' );
								}
								break;
						}
					}
				}
			}

			try {
				// Set prop in customer object.
				if ( is_callable( array( $customer, "set_$key" ) ) ) {
					$customer->{"set_$key"}( $value );
				} else {
					$customer->update_meta_data( $key, $value );
				}
			} catch ( \WC_Data_Exception $e ) {
				// Set notices. Ignore invalid billing email, since is already validated.
				if ( 'customer_invalid_billing_email' !== $e->getErrorCode() ) {
					// wc_add_notice( $e->getMessage(), 'error' );
					$notices_array[] = $e->getMessage();
				}
			}
		}

		if ( 0 < count( $notices_array ) ) {
			wp_send_json_error( self::generate_error_html( $notices_array ) );
		}

		$customer->save();

		wp_send_json_success( __( 'Address saved successfully', 'mlwoo' ) );
	}

	/**
	 * Logic to generate error while saving/updating address.
	 */
	public static function generate_error_html( $error_array = array() ) {
		ob_start();

		?>

		<div class="woocommerce-error">
			<?php echo implode( '', array_map( function( $message ) {
				return sprintf( '<li>%s</li>', esc_html( $message ) );
			}, $error_array ) ); ?>
		</div>

		<?php

		$html_string = \ob_get_clean();
		return $html_string;
	}
}
