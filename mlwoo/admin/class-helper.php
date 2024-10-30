<?php
/**
 * Helper methods for settings on the E-commerce tab.
 *
 * @package MLWoo
 */

namespace MLWoo\Admin;

/**
 * Defines static methods to get/set E-Commerce settings.
 */
class Helper {

	/**
	 * Returns if E-Commerce is activated.
	 *
	 * @return boolean
	 */
	public static function is_ecommerce_active() {
		if ( 'activate' === \Mobiloud::get_option( 'ml-ecommerce-status', '' ) ) {
			return true;
		}

		return false;
	}

	public static function is_dev_mode() {
		if ( 'activate' === \Mobiloud::get_option( 'ml-ecommerce-dev-mode', '' ) ) {
			return true;
		}

		return false;
	}

	public static function is_cart_icon_active() {
		if ( 'on' === \Mobiloud::get_option( 'ml-ecommerce-toggle-cart-icon', 'off' ) ) {
			return true;
		}

		return false;
	}
}
