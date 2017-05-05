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
class WC_Gateway_Wirecard_Checkout_Seamless_Backend_Operations {

	protected $_settings;
	protected $_config;
	protected $_logger;

	public function __construct( $settings ) {
		$this->_settings = $settings;
		$this->_config   = new WC_Gateway_Wirecard_Checkout_Seamless_Config( $settings );
		$this->_logger   = new WC_Logger();
	}

	/**
	 * do a refund
	 *
	 * @since 1.0.0
	 *
	 * @return bool|WP_Error
	 */
	public function refund() {

		$order_id      = $_POST['order_id'];
		$refund_amount = $_POST['refund_amount'];


		if ( $refund_amount <= 0 ) {
			return new WP_Error( 'error', __( 'Refund amount must be greater than zero.', 'woocommerce-wirecard-checkout-seamless' ) );
		}

		$line_item_qtys   = json_decode( str_replace( '\\', "", $_POST['line_item_qtys'] ) );
		$line_item_totals = (array) json_decode( str_replace( '\\', "", $_POST['line_item_totals'] ) );
		$refund_items     = array();

		if ( ! empty( $line_item_qtys ) ) { // refund via ratepay is possible
			foreach ( $line_item_totals as $itemno => $qty ) {
				$refund_items[ $itemno ] = array(
					'refund_total' => $qty,
					'refund_qty'   => isset( $line_item_qtys->{$itemno} ) ? $line_item_qtys->{$itemno} : 1
				);
			}
		}

		$wc_order         = wc_get_order( $order_id ); // woocommerce order
		$wcs_order_number = $wc_order->get_meta( 'wcs_order_number' );
		$order_details    = $this->get_order_details( $wcs_order_number );

		if ( $order_details->getStatus() != 0 ) {
			$this->logResponseErrors( __METHOD__, $order_details->getErrors() );
			$this->showResponseErrors( $order_details->getErrors() );

			return false;
		}

		$order = $order_details->getOrder();

		$basket = null;

		if ( in_array( 'REFUND', $order->getOperationsAllowed() ) ) {

			if (
				(
					$order->getPaymentType() == WirecardCEE_QMore_PaymentType::INSTALLMENT
					&& $this->_settings['woo_wcs_invoiceprovider'] != 'payolution'
				)
				or
				(
					$order->getPaymentType() == WirecardCEE_QMore_PaymentType::INSTALLMENT
					&& $this->_settings['woo_wcs_invoiceprovider'] != 'payolution'
				)
			) {
				if ( empty ( $line_item_qtys ) ) {
					// invoice / installment provider is set to ratepay / wirecard and basket items were not sent
					//return false;
				}
				//$basket = new WirecardCEE_Stdlib_Basket();

			} else {
				$response = $this->get_client()->refund( $wcs_order_number, $refund_amount, $order->getCurrency() );

				if ( $response->hasFailed() ){
					$this->logResponseErrors( __METHOD__, $response->getErrors() );
					return false;
				}
				else{
					return true;
				}
			}

		}
return false;


	}

	/**
	 * get the wirecard order details
	 *
	 * @since 1.0.0
	 *
	 * @param int $wcs_order_number
	 *
	 * @return WirecardCEE_QMore_Response_Backend_GetOrderDetails
	 */
	public function get_order_details( $wcs_order_number ) {
		return $this->get_client()->getOrderDetails( $wcs_order_number );
	}

	/**
	 * get the wirecard backend client
	 *
	 * @since 1.0.0
	 *
	 * @return WirecardCEE_QMore_BackendClient
	 */
	public function get_client() {
		return new WirecardCEE_QMore_BackendClient(
			array_merge( $this->_config->get_client_config(),
			             array( 'PASSWORD' => $this->_config->get_backend_password() )
			) );
	}

	/**
	 * write response errors to log
	 *
	 * @since 1.0.0
	 *
	 * @param WirecardCEE_QMore_Error $errors
	 */
	private function logResponseErrors( $method, $errors ) {
		$_errors = array();
		foreach ( $errors as $error ) {
			$_errors[] = $error->getConsumerMessage();
		}
		$this->_logger->error( "$method : processing refund failed with error(s): " . join( '|', $_errors ) );
	}

	/**
	 * show response errors to admin
	 *
	 * @since 1.0.0
	 *
	 * @param WirecardCEE_QMore_Error $errors
	 */
	private function showResponseErrors( $errors ) {
		$_errors = array();
		foreach ( $errors as $error ) {
			$_errors[] = $error->getConsumerMessage();
		}
		wc_add_notice( join( '<br>', $_errors ), 'error' );
	}


	/**
	 * get the list of payments associated with $wcs_order_number
	 *
	 * @since 1.0.0
	 *
	 * @param $wcs_order_number
	 *
	 * @return WirecardCEE_QMore_Response_Backend_Order_PaymentIterator
	 */
	public function get_payments( $wcs_order_number ) {
		return $this->get_order_details( $wcs_order_number )->getOrder()->getPayments();
	}

	/**
	 * get the list of credits associated with $wcs_order_number
	 *
	 * @since 1.0.0
	 *
	 * @param $wcs_order_number
	 *
	 * @return WirecardCEE_QMore_Response_Backend_Order_CreditIterator
	 */
	public function get_credits( $wcs_order_number ) {
		return $this->get_order_details( $wcs_order_number )->getOrder()->getCredits();
	}

	/**
	 * @param $paymentNumber
	 * @param $orderNumber
	 * @param $currency
	 * @param $amount
	 * @param $type
	 *
	 * @return mixed
	 */
	public function do_backend_operation( $paymentNumber, $orderNumber, $currency, $amount, $type ) {
		switch ( $type ) {
			case 'DEPOSIT':
				return $this->deposit( $orderNumber, $amount, $currency );
			case 'DEPOSITREVERSAL':
				return $this->depositreversal( $orderNumber, $paymentNumber );
			case 'APPROVEREVERSAL':
				return $this->approvereversal( $orderNumber );
			case 'REFUNDREVERSAL':
				return $this->refundreversal( $orderNumber, $paymentNumber );
			default:
				return false;
		}
	}

	/**
	 * deposit desired amount
	 *
	 * @since 1.0.0
	 *
	 * @param $orderNumber
	 * @param $amount
	 * @param $currency
	 *
	 * @return array
	 */
	public function deposit( $orderNumber, $amount, $currency ) {
		$response = $this->get_client()->deposit( $orderNumber, $amount, $currency );

		if ( $response->hasFailed() ) {
			$this->logResponseErrors( __METHOD__, $response->getErrors() );
			$errors = array();
			foreach ( $response->getErrors() as $error ) {
				$errors[] = $error->getConsumerMessage();
			}

			return array( 'type' => 'error', 'message' => join( "<br>", $errors ) );
		} else {
			return array( 'type' => 'updated', 'message' => sprintf( 'DEPOSIT %1.2f %s', $amount, $currency ) );
		}
	}

	/**
	 * reversal the deposit of a payment
	 *
	 * @since
	 *
	 * @param $orderNumber
	 * @param $paymentNumber
	 *
	 * @return array
	 */
	public function depositreversal( $orderNumber, $paymentNumber ) {
		$response = $this->get_client()->depositReversal( $orderNumber, $paymentNumber );

		if ( $response->hasFailed() ) {
			$this->logResponseErrors( __METHOD__, $response->getErrors() );
			$errors = array();
			foreach ( $response->getErrors() as $error ) {
				$errors[] = $error->getConsumerMessage();
			}

			return array( 'type' => 'error', 'message' => join( "<br>", $errors ) );
		} else {
			return array( 'type' => 'updated', 'message' => 'DEPOSITREVERSAL' );
		}
	}

	/**
 * reversal the approval of a payment
 *
 * @since
 *
 * @param $orderNumber
 *
 * @return array
 */
	public function approvereversal( $orderNumber ) {
		$response = $this->get_client()->approveReversal( $orderNumber );

		if ( $response->hasFailed() ) {
			$this->logResponseErrors( __METHOD__, $response->getErrors() );
			$errors = array();
			foreach ( $response->getErrors() as $error ) {
				$errors[] = $error->getConsumerMessage();
			}

			return array( 'type' => 'error', 'message' => join( "<br>", $errors ) );
		} else {
			return array( 'type' => 'updated', 'message' => 'APPROVEREVERSAL' );
		}
	}

	/**
	 * reverse the refund of a payment
	 *
	 * @since
	 *
	 * @param $orderNumber
	 *
	 * @return array
	 */
	public function refundreversal( $orderNumber, $creditNumber ) {
		$response = $this->get_client()->refundReversal( $orderNumber, $creditNumber );

		if ( $response->hasFailed() ) {
			$this->logResponseErrors( __METHOD__, $response->getErrors() );
			$errors = array();
			foreach ( $response->getErrors() as $error ) {
				$errors[] = $error->getConsumerMessage();
			}

			return array( 'type' => 'error', 'message' => join( "<br>", $errors ) );
		} else {
			return array( 'type' => 'updated', 'message' => 'REFUNDREVERSAL' );
		}
	}
}