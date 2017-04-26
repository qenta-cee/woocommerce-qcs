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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Eps
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Eps {

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
		return __( 'eps-Überweisung', 'woocommerce-wirecard-checkout-seamless' );
	}

	/**
	 * Return full url to the icon
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return WOOCOMMERCE_GATEWAY_WCS_URL . 'assets/images/eps-Ueberweisung_h32.png';
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
		$html = '<fieldset  class="wc-credit-card-form wc-payment-form">';

		// dropdown for financial institution
		$html .= "<p class='form-row'>";
		$html .= "<label>" . __( 'Financial institution:',
		                         'woocommerce-wirecard-checkout-seamless' ) . " <span class='required'>*</span></label>";
		$html .= "<select name='woo_wcs_eps_financialInstitution' autocomplete='off'>";
		$html .= "<option value=''>" . __( 'Choose your bank', 'woocommerce-wirecard-checkout-seamless' ) . "</option>";
		foreach ( WirecardCEE_Stdlib_PaymentTypeAbstract::getFinancialInstitutions( WirecardCEE_Stdlib_PaymentTypeAbstract::EPS ) as $key => $value ) {
			$html .= "<option value='$key'>$value</option>";
		}

		$html .= "</select>";
		$html .= "</p>";

		$html .= '</fieldset>';

		return $html;

	}


	public function get_payment_type() {
		return WirecardCEE_QMore_PaymentType::EPS;
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

		if ( empty( $data['woo_wcs_eps_financialInstitution'] ) ) {
			$errors[] = "&bull; " . __( 'Financial institution must not be empty.',
			                            'woocommerce-wirecard-checkout-seamless' );
		}

		return count( $errors ) == 0 ? true : join( "<br>", $errors );
	}

}
