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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Sepa_dd
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Sepa_dd {

	protected $_settings = array();

	public function __construct( $settings ) {
		$this->_settings = $settings;
	}

	/**
	 * Return translated label for payment method
	 *
	 * @since 1.0.0
	 *
	 * @return string|void
	 */
	public function get_label() {
		return __( 'SEPA Direct Debit', 'woocommerce-wirecard-checkout-seamless' );
	}

	/**
	 * Return full url to the icon
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return WOOCOMMERCE_GATEWAY_WCS_URL . "assets/images/SEPA_h32.png";
	}

	/**
	 * returns true because the payment method has input fields
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_payment_fields() {
		return true;
	}

	/**
	 * show fields for the sepa payment method
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_payment_fields() {
		$html = "<fieldset class='wc-credit-card-form wc-payment-form'>";
		$html .= '';

		// account owner field
		$html .= "<p class='form-row'>";
		$html .= "<label>" . __( 'Account owner:',
		                         'woocommerce-wirecard-checkout-seamless' ) . "</label>";
		$html .= "<input name='accountOwner' autocomplete='off' class='input-text' type='text'/>";
		$html .= "</p>";

		// bic field
		$html .= "<p class='form-row'>";
		$html .= "<label>" . __( 'BIC:',
		                         'woocommerce-wirecard-checkout-seamless' ) . " <span class='required'>*</span></label>";
		$html .= "<input name='bankBic' autocomplete='off' class='input-text' type='text'/>";
		$html .= "</p>";

		// iban field
		$html .= "<p class='form-row'>";
		$html .= "<label>" . __( 'IBAN:',
		                         'woocommerce-wirecard-checkout-seamless' ) . " <span class='required'>*</span></label>";
		$html .= "<input name='bankAccountIban' autocomplete='off' class='input-text' type='text'/>";
		$html .= "</p>";

		$html .= "</fieldset>";

		return $html;
	}

	/**
	 * return the payment type sepa direct debit
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_payment_type() {
		return WirecardCEE_QMore_PaymentType::SEPADD;
	}

}