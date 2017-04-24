<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Maestro
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Maestro extends WC_Gateway_Wirecard_Checkout_Seamless_Ccard {

	public function get_label() {
		return __( 'Maestro SecureCode', 'woocommerce-wirecard-checkout-seamless' );
	}

	/**
	 * override the payment type to maestro
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_payment_type() {
		return WirecardCEE_QMore_PaymentType::MAESTRO;
	}

	/**
	 * override the icons to show icons of maestro and mastercard secure code
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_icon() {
		return array(
			WOOCOMMERCE_GATEWAY_WCS_URL . 'assets/images/Maestro_h32.png',
			WOOCOMMERCE_GATEWAY_WCS_URL . 'assets/images/Secure_code_h32.png'
		);
	}

}