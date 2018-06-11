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
 *
 * @since 1.0.0
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Ccard {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $_settings = array();

	/**
	 * WC_Gateway_Wirecard_Checkout_Seamless_Ccard constructor.
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
		return __( 'Credit Card', 'woocommerce-wirecard-checkout-seamless' );
	}

	/**
	 * Return full url to the icon
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return WOOCOMMERCE_GATEWAY_WCS_URL . "assets/images/cc_h32.png";
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
	 * Handle input fields for payment method
	 *
	 * @since 1.0.0
	 *
	 * @param $storage_id
	 *
	 * @return string
	 */
	public function get_payment_fields( $storage_id ) {
		wp_enqueue_script( 'wc-credit-card-form' );
		$payment_type = str_replace( "-", "_", $this->get_payment_type() );

		$html = "<fieldset class='wc-credit-card-form wc-payment-form'>";
		$html .= "<input type='hidden' name='storageId' value='$storage_id'>";
		if ( $this->_settings['woo_wcs_saqacompliance'] ) {
			$html .= "<div id='woocommerce_wcs_iframe_" . strtolower( $payment_type ) . "'></div>";

			return $html;
		} else {
			$html .= '
			<script>
				function parse' . $payment_type . 'date(value,issexp){
					var month_field = document.getElementById("' . $payment_type . '-" + issexp + "-month"),
					year_field = document.getElementById("' . $payment_type . '-" + issexp + "-year");
					if( value.indexOf("/") > -1 ){
						month_field.value = value.split( "/" )[0].trim();
						year = value.split("/")[1].trim();
						year_field.value = year.toString().length <= 2 ? 2000 + parseInt(year) : year;
					}
				}
			</script>';
			if ( $this->_settings['woo_wcs_cc_display_cardholder_field'] ) {
				$html .= "<p class='form-row form-row-wide'>";
				$html .= "<label>" . __( 'Card holder:', 'woocommerce-wirecard-checkout-seamless' ) . "</label>";
				$html .= "<input name='{$payment_type}cardholder' autocomplete='off' class='input-text' type='text' placeholder='{$this->_settings['woo_wcs_cc_holder_placeholder_text']}'>";
				$html .= "</p>";
			}

			// card number field
			$html .= "<p class='form-row'>";
			$html .= "<label>" . __( 'Credit card number:',
					'woocommerce-wirecard-checkout-seamless' ) . " <span class='required'>*</span></label>";
			$html .= "<input name='{$payment_type}cardnumber' autocomplete='off' class='input-text wc-credit-card-form-card-number' type='text' placeholder='{$this->_settings['woo_wcs_cc_number_placeholder_text']}'>";
			$html .= "</p>";

			// expiration date input group
			$html .= '
			<p class="form-row form-row-first">
				<label>' . __( 'Expiration date', 'woocommerce-wirecard-checkout-seamless' ) . ' <span class="required">*</span></label>
				<input 
					class="input-text wc-credit-card-form-card-expiry"
					type="text" 
					autocomplete="off" 
					placeholder="MM / YYYY"
					onchange="parse' . $payment_type . 'date(this.value,\'exp\')"/>
				<input type="hidden" id="' . $payment_type . '-exp-month" name="' . $payment_type . 'expirationMonth">
				<input type="hidden" id="' . $payment_type . '-exp-year" name="' . $payment_type . 'expirationYear">
			</p>';

			// display cvc field if enabled
			if ( $this->_settings['woo_wcs_cc_display_cvc_field'] ) {
				$html .= '<p class="form-row form-row-last">';
				$html .= '<label>' . __( 'Card verification code',
						'woocommerce-wirecard-checkout-seamless' );
				// cvc is not required for credit card mail order / telephone order
				if ( $this->get_payment_type() != WirecardCEE_QMore_PaymentType::CCARD_MOTO ) {
					$html .= ' <span class="required">*</span>';
				}
				$html .= '</label>';
				$html .= '<input class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="' . $this->_settings["woo_wcs_cc_cvc_placeholder_text"] . '" name="' . $payment_type . 'cvc" />';
				$html .= '</p>';
			}
			$html .= "<div class='clear'></div>";

			// expiration date input group
			if ( $this->_settings['woo_wcs_cc_display_issue_date_field'] ) {
				$html .= '
					<p class="form-row form-row-first">
						<label>' . __( 'Issue date', 'woocommerce-wirecard-checkout-seamless' ) . '</label>
						<input 
							class="input-text wc-credit-card-form-card-expiry"
							type="text" 
							autocomplete="off" 
							placeholder="MM / YYYY" 
							onchange="parse' . $payment_type . 'date(this.value,\'issue\')"/>
						<input type="hidden" id="' . $payment_type . '-issue-month" name="' . $payment_type . 'issueMonth">
						<input type="hidden" id="' . $payment_type . '-issue-year" name="' . $payment_type . 'issueYear">
					</p>';
			}

			if ( $this->_settings['woo_wcs_cc_display_issue_number_field'] ) {
				$html .= "<p class='form-row form-row-last'>";
				$html .= "<label>" . __( 'Issue number:', 'woocommerce-wirecard-checkout-seamless' ) . "</label>";
				$html .= "<input name='{$payment_type}issueNumber' autocomplete='off' class='input-text wc-credit-card-form-card-cvc' type='text' placeholder='{$this->_settings['woo_wcs_cc_issue_number_placeholder_text']}'>";
				$html .= "</p>";
			}
			$html .= '<div class="clear"></div>';


			$html .= "</fieldset>";

			return $html;
		}
	}

	/**
	 * Return payment type
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_payment_type() {
		return WirecardCEE_QMore_PaymentType::CCARD;
	}

	/**
	 * return true or error message if there are errors in the validation
	 *
	 * @since 1.0.0
	 *
	 * @return boolean|string
	 */
	public function validate_payment_fields( $data ) {
		if ( $this->_settings['woo_wcs_saqacompliance'] ) {
			return true;
		}
		$errors = [ ];

		$payment_type = str_replace( "-", "_", $data['wcs_payment_method'] );

		if ( $this->_settings['woo_wcs_cc_display_cvc_field'] && $data['wcs_payment_method'] != WirecardCEE_QMore_PaymentType::CCARD_MOTO && empty( $data[ $payment_type . 'cvc' ] ) ) {
			$errors[] = __( 'Card verification code is missing',
					'woocommerce-wirecard-checkout-seamless' );
		}

		return count( $errors ) == 0 ? true : join( "<br>", $errors );
	}
}
