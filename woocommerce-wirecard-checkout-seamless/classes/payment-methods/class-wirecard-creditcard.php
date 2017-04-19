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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Credit_Card
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Ccard {

	private $payment_type = WirecardCEE_QMore_PaymentType::CCARD;
	private $settings = array();

	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	public function get_payment_type() {
		return $this->payment_type;
	}

	public function get_label() {
		return __( 'Credit Card', 'woocommerce-wirecard-checkout-seamless' );
	}

	public function get_icon() {
		return WOOCOMMERCE_GATEWAY_WCS_URL."assets/images/ccard.png";
	}

	public function get_payment_fields() {

		$html = "<input type='radio' name='woocommerce_wirecard_wcs_payment_type' value='" . $this->payment_type . "'>";
		$html .= "<label for='woocommerce_wirecard_wcs_payment_type_'>" . __( 'PaymentType ' . $this->payment_type,
		                                                                      'woocommerce-wirecard-checkout-seamless' ) . "</label>";
		if ( $this->settings['woo_wcs_saqacompliance'] ) {
			$html .= "<div id='woocommerce_wcs_iframe_ccard'></div>";

			return $html;
		} else {
			if ( $this->settings['woo_wcs_cc_display_cardholder_field'] ) {
				$html .= "<p class='form-row'>";
				$html .= "<label>" . __( 'Card holder:', 'woocommerce-wirecard-checkout-seamless' ) . "</label>";
				$html .= "<input name='cardholder' autocomplete='off' class='input-text' type='text' placeholder='{$this->settings['woo_wcs_cc_holder_placeholder_text']}'>";
				$html .= "</p>";
			}
			$html .= "<p class='form-row'>";
			$html .= "<label>" . __( 'Credit card number:', 'woocommerce-wirecard-checkout-seamless' ) . "</label>";
			$html .= "<input name='cardnumber' autocomplete='off' class='input-text' type='text' placeholder='{$this->settings['woo_wcs_cc_number_placeholder_text']}'>";
			$html .= "</p>";

			return $html;
		}
	}

}