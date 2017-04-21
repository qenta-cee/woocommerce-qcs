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
	private $_settings = array();

	public function __construct( $settings ) {
		$this->_settings = $settings;
	}

	public function get_payment_type() {
		return $this->payment_type;
	}

	public function get_label() {
		return __( 'Credit Card', 'woocommerce-wirecard-checkout-seamless' );
	}

	public function get_icon() {
		return WOOCOMMERCE_GATEWAY_WCS_URL."assets/images/cc_h32.png";
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

	public function get_payment_fields() {
		wp_enqueue_script( 'wc-credit-card-form' );
		$html = "<fieldset class='wc-credit-card-form wc-payment-form'>";
		if ( $this->_settings['woo_wcs_saqacompliance'] ) {
			$html .= "<div id='woocommerce_wcs_iframe_ccard'></div>";

			return $html;
		} else {
			if ( $this->_settings['woo_wcs_cc_display_cardholder_field'] ) {
				$html .= "<p class='form-row form-row-wide'>";
				$html .= "<label>" . __( 'Card holder:', 'woocommerce-wirecard-checkout-seamless' ) . "</label>";
				$html .= "<input name='cardholder' autocomplete='off' class='input-text' type='text' placeholder='{$this->_settings['woo_wcs_cc_holder_placeholder_text']}'>";
				$html .= "</p>";
			}

			// card number field
			$html .= "<p class='form-row'>";
			$html .= "<label>" . __( 'Credit card number:', 'woocommerce-wirecard-checkout-seamless' ) . "</label>";
			$html .= "<input name='cardnumber' autocomplete='off' class='input-text' type='text' placeholder='{$this->_settings['woo_wcs_cc_number_placeholder_text']}'>";
			$html .= "</p>";

			// expiration date input group
			$html .= '<p class="form-row form-row-first">
				<label>' . __( 'Expiration date', 'woocommerce-wirecard-checkout-seamless' ) . ' <span class="required">*</span></label>
				<input class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="MM / YYYY" name="expirationDate" />
			</p>
			<p class="form-row form-row-last">
				<label>' . __( 'Card verification code', 'woocommerce-wirecard-checkout-seamless' ) . ' <span class="required">*</span></label>
				<input class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="'.$this->_settings["woo_wcs_cc_cvc_placeholder_text"].'" name="" />
			</p>';
			$html .= "<div class=clear'></div>";


			$html .= "</fieldset>";
			return $html;
		}
	}

}