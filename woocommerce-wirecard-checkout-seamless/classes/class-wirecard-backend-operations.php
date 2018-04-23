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
 * Backend Operations class
 *
 * Handles back-end operations, refund, deposit, ...
 *
 * @since 1.0.0
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Backend_Operations {

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
	 * WC_Gateway_Wirecard_Checkout_Seamless_Backend_Operations constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 */
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
	public function refund( $order_id = 0, $amount = 0, $reason = '' ) {
		global $wpdb;

		$order_id      = $_POST['order_id'];
		$refund_amount = $_POST['refund_amount'];
		if ( $refund_amount <= 0 ) {
			$this->_logger->error( __( 'Refund amount must be greater than zero.', 'woocommerce-wirecard-checkout-seamless' ) );

			return false;
		}

		$line_item_qtys   = json_decode( str_replace( '\\', "", $_POST['line_item_qtys'] ) );
		$line_item_totals = (array) json_decode( str_replace( '\\', "", $_POST['line_item_totals'] ) );
		$refund_items     = array();
		$total_items      = 0;

		if ( ! empty( $line_item_qtys ) ) { // refund via ratepay is possible
			foreach ( $line_item_totals as $itemno => $qty ) {
				$total_items += $qty;
				$refund_items[ $itemno ] = array(
					'refund_total' => $qty,
					'refund_qty'   => isset( $line_item_qtys->{$itemno} ) ? $line_item_qtys->{$itemno} : 0
				);
			}
		}

		$wc_order         = wc_get_order( $order_id ); // woocommerce order
		$wc_order_items   = $wc_order->get_items();
		$wcs_order_number = $wc_order->get_meta( 'wcs_order_number' );
		$order_details    = $this->get_order_details( $wcs_order_number );

		if ( $order_details->getStatus() != 0 ) {
			$this->logResponseErrors( __METHOD__, $order_details->getErrors() );

			return false;
		}

		$order = $order_details->getOrder();

		// get transaction informations
		$tx_query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wirecard_checkout_seamless_tx WHERE id_order = %d", $order_id );
		$tx_data  = $wpdb->get_row( $tx_query );

		$basket = null;

		if ( in_array( 'REFUND', $order->getOperationsAllowed() ) ) {
			if (
				(
					$tx_data->payment_method == WirecardCEE_Stdlib_PaymentTypeAbstract::INVOICE
					&& $this->_settings['woo_wcs_invoiceprovider'] != 'payolution'
				)
				or
				(
					$tx_data->payment_method == WirecardCEE_QMore_PaymentType::INSTALLMENT
					&& $this->_settings['woo_wcs_installmentprovider'] != 'payolution'
				)
			) {
				if ( $total_items == 0 ) {
                    $basket = $this->create_basket_without_items( $refund_amount, $wc_order );
                    $order_data = $wc_order->get_data();
                    $response_with_basket = $this->get_client()->refund( $wcs_order_number, $order_data['total'], $order->getCurrency(), $basket );
                    if ( $response_with_basket->hasFailed() ) {
                        $this->logResponseErrors( __METHOD__, $response_with_basket->getErrors() );

                        return false;
                    } else {
                        return true;
                    }
				}
				$basket = $this->create_basket( $refund_items, $wc_order);
				$response_with_basket = $this->get_client()->refund( $wcs_order_number, $refund_amount, $order->getCurrency(), $basket );
				if ( $response_with_basket->hasFailed() ) {
				    $this->logResponseErrors( __METHOD__, $response_with_basket->getErrors() );

				    return false;
				} else {
				    return true;
				}

			} else {

				$response = $this->get_client()->refund( $wcs_order_number, $refund_amount, $order->getCurrency() );
				if ( $response->hasFailed() ) {
					$this->logResponseErrors( __METHOD__, $response->getErrors() );

					return false;
				} else {
					return true;
				}
			}

		} else {
			// the following have allowed transferFund command
			$allowed_payment_methods = array(
				WirecardCEE_QMore_PaymentType::IDL,
				WirecardCEE_QMore_PaymentType::SKRILLWALLET,
				WirecardCEE_QMore_PaymentType::SOFORTUEBERWEISUNG,
				WirecardCEE_QMore_PaymentType::SEPADD
			);

			if ( in_array( $tx_data->payment_method, $allowed_payment_methods ) ) {
				return $this->transfer_fund_refund( $refund_amount, $order->getCurrency(), $wcs_order_number, $wc_order, $tx_data->payment_method );
			}
		}

		return false;
	}

    /**
     * Create basket with items
     *
     * @since 1.0.15
     *
     * @param $refund_items
     * @param $wc_order
     * @return WirecardCEE_Stdlib_Basket
     */
	public function create_basket( $refund_items, $wc_order ) {
        $wc_order_items   = $wc_order->get_items();
        $basket = new WirecardCEE_Stdlib_Basket();
        $sum = 0;
        foreach ( $wc_order_items as $item_id => $item ) {
            if ( $refund_items[$item_id]['refund_qty'] < 1 ) {
                continue;
            }
            $wc_product  = new WC_Product( $wc_order_items[ $item_id ]->get_product_id() );

            $article_nr = $wc_product->get_id();
            if ( $wc_product->get_sku() != '' ) {
                $article_nr = $wc_product->get_sku();
            }

            $sum += number_format( wc_get_price_including_tax( $wc_product ), wc_get_price_decimals() );
            $basket_item = new WirecardCEE_Stdlib_Basket_Item( $article_nr, $refund_items[$item_id]['refund_qty'] );


            $tax = wc_get_price_including_tax($wc_product) - wc_get_price_excluding_tax($wc_product);
            $item_tax_rate          = $tax / wc_get_price_excluding_tax( $wc_product );

            $description = $wc_product->get_short_description();
            $tax_rate = 0;
            if ( $wc_product->is_taxable() ) {
                $tax_rate = floatval(number_format( $item_tax_rate, 3 ));
            }

            $basket_item->setName( $wc_product->get_name() )
                ->setDescription( $description )
                ->setImageUrl( wp_get_attachment_image_url( $wc_product->get_image_id() ) )
                ->setUnitNetAmount( wc_format_decimal( wc_get_price_excluding_tax( $wc_product ), wc_get_price_decimals() ) )
                ->setUnitGrossAmount( wc_format_decimal( wc_get_price_including_tax( $wc_product ), wc_get_price_decimals() ) )
                ->setUnitTaxAmount( wc_format_decimal( $tax, wc_get_price_decimals() ) )
                ->setUnitTaxRate( $tax_rate * 100 );

            $basket->addItem( $basket_item );
        }
        return $basket;
    }

    /**
     * Create basket with items
     *
     * @since 1.0.15
     *
     * @param $refund_items
     * @param $wc_order
     * @return WirecardCEE_Stdlib_Basket
     */
    public function create_basket_without_items( $refund_amount, $wc_order ) {
        $order_data = $wc_order->get_data();

        $basket = new WirecardCEE_Stdlib_Basket();
        $basket_item = new WirecardCEE_Stdlib_Basket_Item( 'Total refund ratepay' , 1);

        $tax = $order_data['total_tax'];
        $net = $order_data['total'] - $order_data['total_tax'];
        $item_unit_gross_amount = $order_data['total'];
        $item_tax_rate = $tax / $net;

        $description = 'Refund full amount of order';
        $tax_rate = 0;
        if ($tax > 0) {
            $tax_rate = floatval(number_format($item_tax_rate, 3));
        }

        $basket_item->setName('Full Refund Order')
            ->setDescription($description)
            ->setImageUrl()
            ->setUnitNetAmount(wc_format_decimal($net, wc_get_price_decimals()))
            ->setUnitGrossAmount(wc_format_decimal($item_unit_gross_amount, wc_get_price_decimals()))
            ->setUnitTaxAmount(wc_format_decimal($tax, wc_get_price_decimals()))
            ->setUnitTaxRate($tax_rate);
        $basket->addItem($basket_item);

        return $basket;
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
	 * transfer fund for existing order
	 *
	 * @since 1.0.0
	 *
	 * @param $amount
	 * @param $currency
	 * @param $order_number
	 * @param WC_Order $woocommerce_order
	 * @param $payment_method
	 *
	 * @return boolean
	 */
	public function transfer_fund_refund( $amount, $currency, $order_number, $woocommerce_order, $payment_method ) {
		global $wpdb;

		/** @var WirecardCEE_QMore_Request_Backend_TransferFund_Existing $client */
		$client = $this->get_client()->transferFund( WirecardCEE_QMore_BackendClient::$TRANSFER_FUND_TYPE_EXISTING );

		// collect data of the order
		$refundable_sum = $wpdb->prepare( "SELECT SUM(amount) as sum FROM {$wpdb->prefix}wirecard_checkout_seamless_tx WHERE id_order = %d", $woocommerce_order->get_id() );
		$refundable_sum = $wpdb->get_row( $refundable_sum );

		if ( $refundable_sum !== null && $amount > $refundable_sum->sum ) {
			$this->_logger->error( __METHOD__ . ":" . __( 'The refunded amount must be less than deposited amount.', 'woocommerce-wirecard-checkout-seamless' ) );

			return false;
		}

		$transaction = new WC_Gateway_Wirecard_Checkout_Seamless_Transaction( $this->_settings );

		$ret = $client->send(
			$amount,
			$currency,
			sprintf(
				'%s %s %s',
				$woocommerce_order->get_billing_email(),
				$woocommerce_order->get_billing_first_name(),
				$woocommerce_order->get_billing_last_name() ),
			$order_number );

		$this->_logger->info( __METHOD__ . ':' . print_r( $client->getRequestData(), true ) );

		if ( $ret->hasFailed() ) {
			$this->logResponseErrors( __METHOD__, $ret->getErrors() );

			return false;
		} else {
			/** @var WirecardCEE_QMore_Response_Backend_TransferFund $response */
			$response = $ret->getResponse();

			$transaction->create( $woocommerce_order->get_id(), - $amount, $currency, 'TRANSFERFUND::' . $payment_method );
		}


		return true;
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
	 * @param $payment_number
	 * @param $order_number
	 * @param $currency
	 * @param $amount
	 * @param $type
	 * @param $wc_order_id
	 *
	 * @return mixed
	 */
	public function do_backend_operation( $payment_number, $order_number, $currency, $amount, $type, $wc_order_id ) {
		switch ( $type ) {
			case 'DEPOSIT':
				return $this->deposit( $order_number, $amount, $currency );
			case 'DEPOSITREVERSAL':
				return $this->depositreversal( $order_number, $payment_number );
			case 'APPROVEREVERSAL':
				return $this->approvereversal( $order_number );
			case 'REFUNDREVERSAL':
				return $this->refundreversal( $order_number, $payment_number, $wc_order_id );
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
		$transaction = new WC_Gateway_Wirecard_Checkout_Seamless_Transaction( $this->_settings );

		if ( $response->hasFailed() ) {
			$this->logResponseErrors( __METHOD__, $response->getErrors() );
			$errors = array();
			foreach ( $response->getErrors() as $error ) {
				$errors[] = $error->getConsumerMessage();
			}

			return array( 'type' => 'error', 'message' => join( "<br>", $errors ) );
		} else {
			if( isset($_POST['id_tx'])){
				$transaction->update( array( 'payment_state' => 'CANCELED BY ADMIN' ), array( 'id_tx' => $_POST['id_tx'] ) );
			}
			return array( 'type' => 'updated', 'message' => __( 'APPROVEREVERSAL', 'woocommerce-wirecard-checkout-seamless' ) );
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
	public function refundreversal( $orderNumber, $creditNumber, $wc_order_number ) {
		// get the amount from the credit
		$credit_amount = 0;
		foreach ( $this->get_client()->getOrderDetails( $orderNumber )->getOrder()->getCredits() as $credit ) {
			$credit = $credit->getData();
			if ( $credit['creditNumber'] == $creditNumber ) {
				$credit_amount = $credit['amount'];
				break;
			}
		}

		// get the order to find the correct refund_number
		$wc_order = new WC_Order( $wc_order_number );

		$refunds = $wc_order->get_refunds();
		$refund  = null;
		foreach ( $refunds as $_refund ) {
			if ( $_refund->get_amount() == $credit_amount ) {
				$refund = $_refund;
			}
		}

		// do the refundreversal
		$response = $this->get_client()->refundReversal( $orderNumber, $creditNumber );
		if ( $response->hasFailed() ) {
			$this->logResponseErrors( __METHOD__, $response->getErrors() );
			$errors = array();
			foreach ( $response->getErrors() as $error ) {
				$errors[] = $error->getConsumerMessage();
			}

			return array( 'type' => 'error', 'message' => join( "<br>", $errors ) );
		} else {
			if ( $refund != null ) {

				// delete the refund
				$refund->delete();

				return array(
					'type'    => 'updated',
					'message' => __( 'Refund reversal finished. If you have previously reduced this item\'s stock, or this order was submitted by a customer, you will need to manually restore the item\'s stock.', 'woocommerce' )
				);
			} else {
				return array(
					'type'    => 'notice-warning',
					'message' => __( 'Refund reversal finished, but no corresponing refund found in the order. You might need to update the order manually.', 'woocommerce-wirecard-checkout-seamless' )
				);
			}
		}
	}
}
