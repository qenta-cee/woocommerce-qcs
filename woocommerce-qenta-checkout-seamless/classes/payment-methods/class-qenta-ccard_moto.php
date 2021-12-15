<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Qenta Payment CEE GmbH
 * (abbreviated to Qenta CEE) and are explicitly not part of the Qenta CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Qenta CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Qenta CEE does not guarantee their full
 * functionality neither does Qenta CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Qenta CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WC_Gateway_Qenta_Checkout_Seamless_Ccard_Moto
 *
 * @since 1.0.0
 *
 * @see WC_Gateway_Qenta_Checkout_Seamless_Ccard
 */
class WC_Gateway_Qenta_Checkout_Seamless_Ccard_Moto extends WC_Gateway_Qenta_Checkout_Seamless_Ccard {

	/**
	 * override the label to the credit card moto
	 *
	 * @since 1.0.0
	 *
	 * @return string|void
	 */
	public function get_label() {
		return __( 'Credit Card Moto', 'woocommerce-qenta-checkout-seamless' );
	}

	/**
	 * override the payment type to creditcard mail order telephone order
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_payment_type() {
		return QentaCEE\QMore\PaymentType::CCARD_MOTO;
	}

	/**
	 * can any of user roles see this payment method? let's see here
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function get_risk() {
		// check if current user has permission to see this
		$user_roles             = wp_get_current_user()->roles;
		$enabled_roles_for_moto = $this->_settings['woo_wcs_allowmotoforgroup'];

    if ($enabled_roles_for_moto === '') {
      return false;
    }

		return count( array_intersect( $user_roles, $enabled_roles_for_moto ) ) == 0 ? false : true;
	}

}
