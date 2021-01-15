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
 * Class WC_Gateway_Qenta_Checkout_Seamless_Invoice
 *
 * @since 1.0.0
 */
class WC_Gateway_Qenta_Checkout_Seamless_Invoiceb2b {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $_settings = array();

	/**
	 * WC_Gateway_Qenta_Checkout_Seamless_Invoiceb2b constructor.
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
		return __( 'Invoice B2B', 'woocommerce-qenta-checkout-seamless' );
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

		if ( $this->_settings['woo_wcs_payolutionterms'] && $this->_settings['woo_wcs_invoiceprovider'] == 'payolution' ) {

			$payolution_mid = urlencode( base64_encode( $this->_settings['woo_wcs_payolutionmid'] ) );

			$consent_link = __( 'consent', 'woocommerce-qenta-checkout-seamless' );

			if ( strlen( $this->_settings['woo_wcs_payolutionmid'] ) > 5 ) {
				$consent_link = sprintf( '<a href="https://payment.payolution.com/payolution-payment/infoport/dataprivacyconsent?mId=%s" target="_blank">%s</a>',
				                         $payolution_mid,
				                         __( 'consent', 'woocommerce-qenta-checkout-seamless' ) );
			}

			$html .= "<p class='form-row'>";

			$html .= "<label><input type='checkbox' name='b2b_consent' id='woo_wcs_b2b_consent'>"
			         . __( 'I agree that the data which are necessary for the liquidation of purchase on account and which are used to complete the identity and credit check are transmitted to payolution. My ', 'woocommerce-qenta-checkout-seamless' )
			         . $consent_link
			         . __( ' can be revoked at any time with effect for the future.', 'woocommerce-qenta-checkout-seamless' ) . "</label>";

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
		return QentaCEE\QMore\PaymentType::INVOICE . "b2b";
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
		} elseif ( $this->_settings['woo_wcs_invoiceprovider'] == 'qenta' ) {
			return $this->is_available_qenta();
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

		$customer = new WC_Customer( get_current_user_id() );

		// this shipping address can be empty
		$shipping_address             = new stdClass();
		$shipping_address->first_name = $customer->get_shipping_first_name();
		$shipping_address->last_name  = $customer->get_shipping_last_name();
		$shipping_address->company    = $customer->get_shipping_company();
		$shipping_address->address_1  = $customer->get_shipping_address_1();
		$shipping_address->address_2  = $customer->get_shipping_address_2();
		$shipping_address->city       = $customer->get_shipping_city();
		$shipping_address->postcode   = $customer->get_shipping_postcode();
		$shipping_address->country    = $customer->get_shipping_country();

		// this is the first address to be filled, it can't be empty
		$billing_address             = new stdClass();
		$billing_address->first_name = $customer->get_billing_first_name();
		$billing_address->last_name  = $customer->get_billing_last_name();
		$billing_address->company    = $customer->get_billing_company();
		$billing_address->address_1  = $customer->get_billing_address_1();
		$billing_address->address_2  = $customer->get_billing_address_2();
		$billing_address->city       = $customer->get_billing_city();
		$billing_address->postcode   = $customer->get_billing_postcode();
		$billing_address->country    = $customer->get_billing_country();

		if ( $this->address_empty( $shipping_address ) ) {
			$shipping_address = $billing_address;
		}

		// check if addresses are equal
		if ( $this->_settings['woo_wcs_invoice_billing_shipping_equal'] ) {
			foreach ( $billing_address as $key => $value ) {
				if ( $billing_address->$key != $shipping_address->$key ) {
					return false;
				}
			}
		}

		// check if shipping country is allowed
		if ( ! in_array( $shipping_address->country,
		                 $this->_settings['woo_wcs_invoice_allowed_shipping_countries'] )
		) {
			return false;
		}

		// check if billing country is allowed
		if ( ! in_array( $shipping_address->country, $this->_settings['woo_wcs_invoice_allowed_billing_countries'] ) ) {
			return false;
		}

		return true;
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
	 * as the qenta has the same conditions as ratepay, return the is_available_ratepay method
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_available_qenta() {
		// should conditions of qenta change, add them here
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

		$customer = new WC_Customer( get_current_user_id() );

		$errors = [ ];

		if ( $this->_settings['woo_wcs_payolutionterms'] && $data['b2b_consent'] != 'on' && $this->check_terms( $data['wcs_payment_method'] ) ) {
			$errors[] = "&bull; " . __( 'Please accept the consent terms!', 'woocommerce-qenta-checkout-seamless' );
		}

		if ( empty( $customer->get_billing_company() ) )
			$errors[] = "&bull; " . __( 'For Invoice B2B, <a href=\'#billing_company\'>Company name</a> must not be empty.', 'woocommerce-qenta-checkout-seamless' );

		return count( $errors ) == 0 ? true : join( "<br>", $errors );
	}

    /**
     * Check if the provider is payolution if so agb check is needed
     *
     * @since 1.0.11
     * @param $payment_method
     * @return bool
     */
    private function check_terms( $payment_method ) {
        if ( ( $payment_method == 'INVOICE' && $this->_settings['woo_wcs_invoiceprovider'] == 'payolution' ) ) {
            return true;
        }
        return false;
    }
}
