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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Paypal
 *
 * @since 1.0.0
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Paypal {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $settings = array();

	/**
	 * WC_Gateway_Wirecard_Checkout_Seamless_Paypal constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * return the translated payment method label
	 *
	 * @since 1.0.0
	 *
	 * @return string|void
	 */
	public function get_label() {
		return __( 'PayPal', 'woocommerce-wirecard-checkout-seamless' );
	}

	/**
	 * return the full url to the payment method icon
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return WOOCOMMERCE_GATEWAY_WCS_URL."assets/images/paypal_h32.png";
	}

	/**
	 * returns false because the payment method has no input fields
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_payment_fields() {
		return false;
	}

	/**
	 * Return payment type
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_payment_type() {
		return WirecardCEE_QMore_PaymentType::PAYPAL;
	}

}
