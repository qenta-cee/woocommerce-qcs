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
class WC_Gateway_Wirecard_Checkout_Seamless_Data_Storage {

	protected $_settings;
	protected $_config;
	protected $_logger;

	public function __construct( $settings ) {
		$this->_settings = $settings;

		$this->_logger = new WC_Logger();
		$this->_config = new WC_Gateway_Wirecard_Checkout_Seamless_Config( $settings );
	}

	/**
	 * initialize data storage
	 */
	public function init() {

		$cart = new WC_Cart();
		$cart->get_cart_from_session();

		$data_storage_init = new WirecardCEE_QMore_DataStorageClient(
			$this->_config->get_client_config()
		);

		$data_storage_return_url = add_query_arg( 'wc-api', 'wc_gateway_wcs_datastorage_return',
		                             site_url( '/', is_ssl() ? 'https' : 'http' ) );

		$data_storage_init->setReturnUrl( $data_storage_return_url );
		$data_storage_init->setOrderIdent( key( $cart->cart_contents ) );

		if ( $this->_settings['woo_wcs_saqacompliance'] ) {
			$data_storage_init->setJavascriptScriptVersion( 'pci3' );
			if ( strlen( trim( $this->_settings['iframe_css_url'] ) ) ) {
				$data_storage_init->setIframeCssUrl( $this->_settings['iframe_css_url'] );
			}

			// set placeholders
			$data_storage_init->setCreditCardPanPlaceholder(
				__( $this->_settings['woo_wcs_cc_number_placeholder_text'] )
			);
			$data_storage_init->setCreditCardCardholderNamePlaceholder(
				__( $this->_settings['woo_wcs_cc_holder_placeholder_text'] )
			);
			$data_storage_init->setCreditCardCvcPlaceholder(
				__( $this->_settings['woo_wcs_cc_cvc_placeholder_text'] )
			);
			$data_storage_init->setCreditCardCardIssueNumberPlaceholder(
				__( $this->_settings['woo_wcs_cc_issue_number_placeholder_text'] )
			);

			// set visibility of fields
			$data_storage_init->setCreditCardShowExpirationDatePlaceholder(
				$this->_settings['woo_wcs_cc_display_exp_date_field']
			);
			$data_storage_init->setCreditCardShowIssueDatePlaceholder(
				$this->_settings['woo_wcs_cc_display_issue_date_placeholder_text']
			);
			$data_storage_init->setCreditCardShowCardholderNameField(
				$this->_settings['woo_wcs_cc_display_cardholder_field']
			);
			$data_storage_init->setCreditCardShowCvcField(
				$this->_settings['woo_wcs_cc_display_cvc_field']
			);
			$data_storage_init->setCreditCardShowIssueDateField(
				$this->_settings['woo_wcs_cc_display_issue_date_field']
			);
			$data_storage_init->setCreditCardShowIssueNumberField(
				$this->_settings['woo_wcs_cc_display_issue_number_field']
			);
		}

		$this->_logger->info( __METHOD__ . ':' . print_r( $data_storage_init->getRequestData(), true ) );

		return $data_storage_init->initiate();
	}
}