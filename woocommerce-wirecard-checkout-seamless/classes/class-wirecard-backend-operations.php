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

		print_r($_POST);die;
		$line_item_qtys   = str_replace( '\\', "", $_POST['line_item_qtys'] );
		$line_item_totals = str_replace( '\\', "", $_POST['line_item_totals'] );
		$refund_items     = array();

		$wc_order         = wc_get_order( $order_id );
		$wcs_order_number = $wc_order->get_meta( 'wcs_order_number' );
		$order_details    = $this->get_order_details( $wcs_order_number );

		if ( $order_details->getStatus() != 0 ) {
			$this->logResponseErrors( __METHOD__, $order_details->getErrors() );

			return false;
		}

		$order    = $order_details->getOrder();
		$payments = $order->getPayments();


		if ( ! empty( $line_item_qtys ) ) { // refund via ratepay is possible
			foreach ( json_decode( $line_item_qtys ) as $itemno => $qty ) {
				$refund_items[ $itemno ]['refund_qty'] = $qty;
			}
			foreach ( json_decode( $line_item_totals ) as $itemno => $total ) {
				if ( array_key_exists( $itemno, $refund_items ) ) {
					$refund_items[ $itemno ]['refund_total'] = $total;
				}
			}
		} else {

		}

		$operations_allowed = array();
		foreach ( $payments as $payment ) {
			foreach ( $payment->getOperationsAllowed() as $operation ) {
				if ( ! in_array( $operation, $operations_allowed ) ) {
					$operations_allowed[] = $operation;
				}
			}
		}

		if ( in_array( "REFUND", $operations_allowed ) ) {
			$response = $this->get_client()->refund(
				$wcs_order_number,
				$refund_amount,
				$wc_order->get_currency() );

			if ( $response->hasFailed() ) {
				$this->logResponseErrors( __METHOD__, $response->getErrors() );
			} else {
				return true;
			}
		}
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
}