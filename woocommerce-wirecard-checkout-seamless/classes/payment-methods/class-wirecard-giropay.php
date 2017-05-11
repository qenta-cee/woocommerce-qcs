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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Giropay
 *
 * @since 1.0.0
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Giropay {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $_settings = array();

	/**
	 * WC_Gateway_Wirecard_Checkout_Seamless_Giropay constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 */
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
		return __( 'giropay', 'woocommerce-wirecard-checkout-seamless' );
	}

	/**
	 * Return full url to the icon
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return WOOCOMMERCE_GATEWAY_WCS_URL . "assets/images/giropay_h32.png";
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
	 * returns the payment fields in the frontend
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_payment_fields() {
		wp_enqueue_script( 'wc-credit-card-form' );
		$html = "<fieldset class='wc-credit-card-form wc-payment-form'>";

		$html .= "<p class='form-row form-row-wide'>";
		$html .= "<label>" . __( 'Account owner:', 'woocommerce-wirecard-checkout-seamless' ) . "</label>";
		$html .= "<input type='text' name='woo_wcs_giropay_accountowner' autocomplete='off' class='input-text'>";
		$html .= "</p>";

		$html .= "<p class='form-row form-row-wide'>";
		$html .= "<label>" . __( 'Account number:',
		                         'woocommerce-wirecard-checkout-seamless' ) . " <span class='required'>*</span></label>";
		$html .= "<input type='text' name='woo_wcs_giropay_accountnumber' autocomplete='off' class='input-text'>";
		$html .= "</p>";

		$html .= "<p class='form-row form-row-wide'>";
		$html .= "<label>" . __( 'Bank number:',
		                         'woocommerce-wirecard-checkout-seamless' ) . " <span class='required'>*</span></label>";
		$html .= "<input type='text' name='woo_wcs_giropay_banknumber' autocomplete='off' class='input-text'>";
		$html .= "</p>";


		$html .= "</fieldset>";

		return $html;

	}

	/**
	 * Return payment type
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_payment_type() {
		return WirecardCEE_QMore_PaymentType::GIROPAY;
	}

	/**
	 * return true or error message if there are errors in the validation
	 *
	 * @since 1.0.0
	 *
	 * @param $data - post data received
	 *
	 * @return boolean|string
	 */
	public function validate_payment_fields( $data ) {

		$errors = [ ];

		if ( empty( $data['woo_wcs_giropay_accountnumber'] ) ) {
			$errors[] = "&bull; " . __( 'Account number must not be empty.',
			                            'woocommerce-wirecard-checkout-seamless' );
		}

		if ( empty( $data['woo_wcs_giropay_banknumber'] ) ) {
			$errors[] = "&bull; " . __( 'Bank number must not be empty.',
			                            'woocommerce-wirecard-checkout-seamless' );
		}

		return count( $errors ) == 0 ? true : join( "<br>", $errors );
	}
}
