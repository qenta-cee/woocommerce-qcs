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

/**
 * Datastorage class
 *
 * Handles data storage initialization
 *
 * @since 1.0.0
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Data_Storage {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $_settings;

	/**
	 * Configurations
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var WC_Gateway_Wirecard_Checkout_Seamless_Config
	 */
	protected $_config;

	/**
	 * Use WC_Logger for errorhandling
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var WC_Logger
	 */
	protected $_logger;

	/**
	 * WC_Gateway_Wirecard_Checkout_Seamless_Data_Storage constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 */
	public function __construct( $settings ) {
		$this->_settings = $settings;

		$this->_logger = new WC_Logger();
		$this->_config = new WC_Gateway_Wirecard_Checkout_Seamless_Config( $settings );
	}

	/**
	 * Initialize data storage
	 *
	 * @since 1.0.0
	 *
	 * @return WirecardCEE_QMore_DataStorage_Response_Initiation
	 */
	public function init() {
		global $woocommerce;

		$order_ident = md5( time() * time() );

		if ( $woocommerce->session->get( 'wcs_session_order_ident' ) !== null ) {
			$order_ident = $woocommerce->session->get( 'wcs_session_order_ident' );
		} else {
			$woocommerce->session->set( 'wcs_session_order_ident', $order_ident );
		}
		$data_storage_init = new WirecardCEE_QMore_DataStorageClient(
			$this->_config->get_client_config()
		);


		$data_storage_return_url = add_query_arg(
			'wc-api',
			'wc_gateway_wcs_datastorage_return',
			home_url( '/', is_ssl() ? 'https' : 'http' ) );

		$data_storage_init->setReturnUrl( $data_storage_return_url );
		$data_storage_init->setOrderIdent( $order_ident );

		if ( $this->_settings['woo_wcs_saqacompliance'] ) {
			$data_storage_init->setJavascriptScriptVersion( 'pci3' );
			if ( strlen( trim( $this->_settings['woo_wcs_iframecssurl'] ) ) ) {
				$data_storage_init->setIframeCssUrl( $this->_settings['woo_wcs_iframecssurl'] );
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
