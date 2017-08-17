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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Invoice
 *
 * @since 1.0.0
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Invoice {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $_settings = array();

	/**
	 * WC_Gateway_Wirecard_Checkout_Seamless_Invoice constructor.
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
		return __( 'Invoice', 'woocommerce-wirecard-checkout-seamless' );
	}

	/**
	 * Return full url to the icon
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return WOOCOMMERCE_GATEWAY_WCS_URL . "assets/images/Invoice_h32.png";
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
		$html .= "<label>" . __( 'Date of Birth:',
				'woocommerce-wirecard-checkout-seamless' ) . "</label>";
		$html .= "<select name='dob_day' class=''>";

		for ( $day = 31; $day > 0; $day -- ) {
			$html .= "<option value='$day'> $day </option>";
		}

		$html .= "</select>";

		$html .= "<select name='dob_month' class=''>";
		for ( $month = 12; $month > 0; $month -- ) {
			$html .= "<option value='$month'> $month </option>";
		}
		$html .= "</select>";

		$html .= "<select name='dob_year' class=''>";
		for ( $year = date( "Y" ); $year > 1920; $year -- ) {
			$html .= "<option value='$year'> $year </option>";
		}
		$html .= "</select>";
		$html .= "</p>";


		if ( $this->_settings['woo_wcs_payolutionterms'] && $this->_settings['woo_wcs_invoiceprovider'] == 'payolution' ) {

			$payolution_mid = urlencode( base64_encode( $this->_settings['woo_wcs_payolutionmid'] ) );

			$consent_link = __( 'consent', 'woocommerce-wirecard-checkout-seamless' );

			if ( strlen( $this->_settings['woo_wcs_payolutionmid'] ) > 5 ) {
				$consent_link = sprintf( '<a href="https://payment.payolution.com/payolution-payment/infoport/dataprivacyconsent?mId=%s" target="_blank">%s</a>',
					$payolution_mid,
					__( 'consent', 'woocommerce-wirecard-checkout-seamless' ) );
			}

			$html .= "<p class='form-row'>";

			$html .= "<label><input type='checkbox' name='consent'>"
			         . __( 'I agree that the data which are necessary for the liquidation of purchase on account and which are used to complete the identity and credit check are transmitted to payolution. My ', 'woocommerce-wirecard-checkout-seamless' )
			         . $consent_link
			         . __(' can be revoked at any time with effect for the future.', 'woocommerce-wirecard-checkout-seamless' ) . "</label>";

			$html .= "</p>";
		}

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
		return WirecardCEE_QMore_PaymentType::INVOICE;
	}

	/**
	 * are the conditions of selected provider fulfilled?
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function get_risk() {
		if ( $this->_settings['woo_wcs_invoiceprovider'] == 'payolution' ) {
			return $this->is_available_payolution();
		} elseif ( $this->_settings['woo_wcs_invoiceprovider'] == 'ratepay' ) {
			return $this->is_available_ratepay();
		} elseif ( $this->_settings['woo_wcs_invoiceprovider'] == 'wirecard' ) {
			return $this->is_available_wirecard();
		}

		return false;
	}

	/**
	 * Handles payolution conditions
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_available_payolution() {
		global $woocommerce;

		$cart = new WC_Cart();
		$cart->get_cart_from_session();

		// age is not checked as woocommerce does not have a default way to enter birthdate

		// if the currency isn't allowed
		if (
			! is_array( $this->_settings['woo_wcs_invoice_accepted_currencies'] )
			|| ! in_array( get_woocommerce_currency(), $this->_settings['woo_wcs_invoice_accepted_currencies'] )
		) {
			return false;
		}

		// if cart total is smaller than set limit
		if ( $cart->total <= floatval( $this->_settings['woo_wcs_invoice_min_amount'] ) ) {
			return false;
		}

		// if cart total is greater than set limit
		if (
			floatval( $this->_settings['woo_wcs_invoice_max_amount'] ) != 0
			&& $cart->total >= floatval( $this->_settings['woo_wcs_invoice_max_amount'] )
		) {
			return false;
		}

		foreach ( $cart->cart_contents as $hash => $item ) {
			$product = new WC_Product( $item['product_id'] );

			// if the product is in the "digital goods" category, do not show invoice as payment method
			if ( $product->is_downloadable() || $product->is_virtual() ) {
				return false;
			}


		}

		$customer = $woocommerce->customer;
		$fields   = array(
			'first_name',
			'last_name',
			'address_1',
			'address_2',
			'city',
			'country',
			'postcode',
			'state'
		);
		foreach ( $fields as $f ) {
			$m1 = "get_billing_$f";
			$m2 = "get_shipping_$f";

			$f1 = call_user_func(
				array(
					$customer,
					$m1
				)
			);

			$f2 = call_user_func(
				array(
					$customer,
					$m2
				)
			);
			if ( $f1 != $f2 && ! empty( $f2 ) ) {
				return false;
			}
		}

		// check if shipping country is allowed
		if ( ! in_array( $customer->get_shipping_country(),
				$this->_settings['woo_wcs_invoice_allowed_shipping_countries'] ) && ! empty( $customer->get_shipping_country() )
		) {
			return false;
		}

		// check if billing country is allowed
		if ( ! in_array( $customer->get_billing_country(), $this->_settings['woo_wcs_invoice_allowed_billing_countries'] ) && ! empty( $customer->get_billing_country() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Handles ratepay conditions
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_available_ratepay() {
		// should conditions of ratepay change, add them here
		return $this->is_available_payolution();
	}

	/**
	 * as the wirecard has the same conditions as ratepay, return the is_available_ratepay method
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_available_wirecard() {
		// should conditions of wirecard change, add them here
		return $this->is_available_ratepay();
	}

	/**
	 * return true or error message if there are errors in the validation
	 *
	 * @since 1.0.0
	 *
	 * @return boolean|string
	 */
	public function validate_payment_fields( $data ) {

		$errors = [ ];

		if ( $this->_settings['woo_wcs_payolutionterms'] && $data['consent'] != 'on' ) {
			$errors[] = "&bull; " . __( 'Please accept the consent terms!', 'woocommerce-wirecard-checkout-seamless' );
		}

		$birthdate = new DateTime( $data['dob_year'] . '-' . $data['dob_month'] . '-' . $data['dob_day'] );

		$age = $birthdate->diff( new DateTime );
		$age = $age->format( '%y' );

		if ( $age < 18 ) {
			$errors[] = "&bull; " . __( 'You have to be 18 years or older to use this payment.',
					'woocommerce-wirecard-checkout-seamless' );
		}

		return count( $errors ) == 0 ? true : join( "<br>", $errors );
	}

	/**
	 * check if the fields in address are empty
	 *
	 * @since 1.0.0
	 *
	 * @param $address
	 *
	 * @return boolean
	 */
	private function address_empty( $address ) {

		foreach ( $address as $key => $value ) {
			if ( ! empty( $value ) ) {
				return false;
			}
		}

		return true;
	}
}
