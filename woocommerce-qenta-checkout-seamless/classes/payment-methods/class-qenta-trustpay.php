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
 * Class WC_Gateway_Qenta_Checkout_Seamless_Trustpay
 *
 * @since 1.0.0
 */
class WC_Gateway_Qenta_Checkout_Seamless_Trustpay {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $_settings = array();

	/**
	 * Use WC_Logger for errorhandling
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var null|WC_Logger
	 */
	protected $_logger = null;

	/**
	 * WC_Gateway_Qenta_Checkout_Seamless_Trustpay constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 */
	public function __construct( $settings ) {
		$this->_settings = $settings;
		$this->_logger   = new WC_Logger();
	}

	/**
	 * Return translated label for payment method
	 *
	 * @since 1.0.0
	 *
	 * @return string|void
	 */
	public function get_label() {
		return __( 'TrustPay', 'woocommerce-qenta-checkout-seamless' );
	}

	/**
	 * Return full url to the icon
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return WOOCOMMERCE_GATEWAY_WCS_URL . 'assets/images/TrustPay_h32.png';
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

		$config = new WC_Gateway_Qenta_Checkout_Seamless_Config( $this->_settings );

		$config_array             = $config->get_client_config();
		$config_array['PASSWORD'] = $config->get_backend_password();

		try {
			$backend_client = new QentaCEE_QMore_BackendClient( $config_array );
		} catch ( QentaCEE_QMore_Exception_InvalidArgumentException $e ) {
			$this->_logger->error( __METHOD__ . ':' . print_r( $e, true ) );

			return __( 'This payment method is not available. Please contact the administrator.',
			           'woocommerce-qenta-checkout-seamless' );
		}


		try {
			$response = $backend_client->getFinancialInstitutions( 'TRUSTPAY' );
		} catch ( Exception $e ) {

			$this->_logger->error( __METHOD__ . ':' . print_r( $e, true ) );

			return __( 'This payment method is not available. Please contact the administrator.',
			           'woocommerce-qenta-checkout-seamless' );
		}

		if ( ! $response->hasFailed() ) {
			$financial_institutions = $response->getFinancialInstitutions();

			uasort( $financial_institutions, function ( $a, $b ) {
				return strcmp( $a['id'], $b['id'] );
			} );
		} else {
			$this->_logger->error( __METHOD__ . ':' . print_r( $response->getErrors(), true ) );
		}

		$html = '<fieldset  class="wc-credit-card-form wc-payment-form">';

		// dropdown for financial institution
		$html .= "<p class='form-row'>";
		$html .= "<label>" . __( 'Financial institution:',
		                         'woocommerce-qenta-checkout-seamless' ) . " <span class='required'>*</span></label>";
		$html .= "<select name='woo_wcs_trustpay_financialInstitution' autocomplete='off'>";
		$html .= "<option value=''>" . __( 'Choose your bank', 'woocommerce-qenta-checkout-seamless' ) . "</option>";
		foreach ( $financial_institutions as $institution ) {
			$html .= "<option value='" . $institution['id'] . "'>" . $institution['name'] . "</option>";
		}

		$html .= "</select>";
		$html .= "</p>";

		$html .= '</fieldset>';

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
		return QentaCEE_QMore_PaymentType::TRUSTPAY;
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

		if ( empty( $data['woo_wcs_trustpay_financialInstitution'] ) ) {
			$errors[] = "&bull; " . __( 'Financial institution must not be empty.',
			                            'woocommerce-qenta-checkout-seamless' );
		}

		return count( $errors ) == 0 ? true : join( "<br>", $errors );
	}
}
