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

/**
 * Backend Operations class
 *
 * Handles back-end operations, refund, deposit, ...
 *
 * @since 1.0.0
 */
class WC_Gateway_Qenta_Checkout_Seamless_Backend_Operations {

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
	 * @var WC_Gateway_Qenta_Checkout_Seamless_Config
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
	 * WC_Gateway_Qenta_Checkout_Seamless_Backend_Operations constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 */
	public function __construct( $settings ) {
		$this->_settings = $settings;
		$this->_config   = new WC_Gateway_Qenta_Checkout_Seamless_Config( $settings );
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

    $params_post   = $this->get_post_data();
		$order_id      = $params_post['order_id'];
		$refund_amount = $params_post['refund_amount'];
		if ( $refund_amount <= 0 ) {
			$this->_logger->error( esc_html( __( 'Refund amount must be greater than zero.', 'woocommerce-qenta-checkout-seamless' ) ) );

			return false;
		}

		$line_item_qtys   = json_decode( str_replace( '\\', "", $params_post['line_item_qtys'] ) );
		$line_item_totals = (array) json_decode( str_replace( '\\', "", $params_post['line_item_totals'] ) );
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
		$wcs_order_number = $wc_order->get_meta( 'wcs_order_number' );
		$order_details    = $this->get_order_details( $wcs_order_number );

		if ( $order_details->getStatus() != 0 ) {
			$this->logResponseErrors( __METHOD__, $order_details->getErrors() );

			return false;
		}

		$order = $order_details->getOrder();

		// get transaction informations
		$tx_query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}qenta_checkout_seamless_tx WHERE id_order = %d", $order_id );
		$tx_data  = $wpdb->get_row( $tx_query );
		$tx_original = unserialize( $tx_data->request );

		$basket = null;

        if ( in_array( 'REFUND', $order->getOperationsAllowed() ) ) {
            if (
                (
                    $tx_data->payment_method == QentaCEE\Stdlib\PaymentTypeAbstract::INVOICE
                    && $this->_settings['woo_wcs_invoiceprovider'] != 'payolution'
                )
                or
                (
                    $tx_data->payment_method == QentaCEE\QMore\PaymentType::INSTALLMENT
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

                $basket = $this->create_basket( $refund_items, $wc_order, $tx_original );
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
				QentaCEE\QMore\PaymentType::IDL,
				QentaCEE\QMore\PaymentType::SKRILLWALLET,
				QentaCEE\QMore\PaymentType::SOFORTUEBERWEISUNG,
				QentaCEE\QMore\PaymentType::SEPADD
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
     * @since 1.0.16
     *
     * @param $refund_items
     * @param $wc_order
     * @param $tx_original
     * @return QentaCEE\Stdlib\Basket
     */
	public function create_basket( $refund_items, $wc_order, $tx_original ) {
        $wc_order_items = $wc_order->get_items();
        $basket         = new QentaCEE\Stdlib\Basket();

        $basket_items = 0;
        if ( isset( $tx_original['basketItems'] ) ) {
            $basket_items = $tx_original['basketItems'];
        }
        $original_basket = array();
        for ( $count = 1; $count <= $basket_items; $count++ ) {
            $prefix = 'basketItem'.$count;
            $original_basket[$tx_original[$prefix . 'articleNumber']] = array(
                'gross'         => $tx_original[$prefix . 'unitGrossAmount'],
                'net'           => $tx_original[$prefix . 'unitNetAmount'],
                'tax'           => $tx_original[$prefix . 'unitTaxAmount'],
                'tax_rate'      => $tx_original[$prefix . 'unitTaxRate'],
                'description'   => $tx_original[$prefix . 'description'],
                'name'          => $tx_original[$prefix . 'name'],
                'imageUrl'      => $tx_original[$prefix . 'imageUrl']
            );
        }

        foreach ( $wc_order_items as $item_id => $item ) {
            $refund_item_quantity = $refund_items[$item_id]['refund_qty'];
            if ( $refund_item_quantity < 1 ) {
                continue;
            }
            $product = $item->get_product();
            $product_data = $product->get_data();


            if ( key_exists( $product_data['sku'], $original_basket ) ) {
                $refund_item = $original_basket[$product_data['sku']];
                $basket_item = new QentaCEE\Stdlib\Basket\Item( $product_data['sku'] );

                $basket_item->setName( $refund_item['name'] )
                    ->setDescription( $refund_item['description'] )
                    ->setImageUrl( $refund_item['imageUrl'] )
                    ->setUnitNetAmount( $refund_item['net'] )
                    ->setUnitGrossAmount( $refund_item['gross'] )
                    ->setUnitTaxAmount( $refund_item['tax'] )
                    ->setUnitTaxRate( $refund_item['tax_rate'] );

                $basket->addItem( $basket_item, $refund_item_quantity );
            }

        }
        return $basket;
    }

    /**
     * Create basket with items
     *
     * @since 1.0.16
     *
     * @param $refund_items
     * @param $wc_order
     * @return QentaCEE\Stdlib\Basket
     */
    public function create_basket_without_items( $refund_amount, $wc_order ) {
        $order_data = $wc_order->get_data();

        $basket = new QentaCEE\Stdlib\Basket();
        $basket_item = new QentaCEE\Stdlib\Basket\Item( 'Total refund ratepay' , 1);

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
	 * get the qenta order details
	 *
	 * @since 1.0.0
	 *
	 * @param int $wcs_order_number
	 *
	 * @return QentaCEE\QMore\Response\Backend\GetOrderDetails
	 */
	public function get_order_details( $wcs_order_number ) {
		return $this->get_client()->getOrderDetails( $wcs_order_number );
	}

	/**
	 * get the qenta backend client
	 *
	 * @since 1.0.0
	 *
	 * @return QentaCEE\QMore\BackendClient
	 */
	public function get_client() {
		return new QentaCEE\QMore\BackendClient(
			array_merge( $this->_config->get_client_config(),
			             array( 'PASSWORD' => $this->_config->get_backend_password() )
			) );
	}

	/**
	 * write response errors to log
	 *
	 * @since 1.0.0
	 *
	 * @param QentaCEE\QMore\Error $errors
	 */
	private function logResponseErrors( $method, $errors ) {
		$_errors = array();
		foreach ( $errors as $error ) {
			$_errors[] = $error->getConsumerMessage();
		}
		$this->_logger->error( esc_html( "$method : processing refund failed with error(s): " . join( '|', $_errors ) ) );
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

		/** @var QentaCEE\QMore\Request\Backend\TransferFund\Existing $client */
		$client = $this->get_client()->transferFund( QentaCEE\QMore\BackendClient::$TRANSFER_FUND_TYPE_EXISTING );

		// collect data of the order
		$refundable_sum = $wpdb->prepare( "SELECT SUM(amount) as sum FROM {$wpdb->prefix}qenta_checkout_seamless_tx WHERE id_order = %d", $woocommerce_order->get_id() );
		$refundable_sum = $wpdb->get_row( $refundable_sum );

		if ( $refundable_sum !== null && $amount > $refundable_sum->sum ) {
			$this->_logger->error( __METHOD__ . ":" . esc_html( __( 'The refunded amount must be less than deposited amount.', 'woocommerce-qenta-checkout-seamless' ) ) );

			return false;
		}

		$transaction = new WC_Gateway_Qenta_Checkout_Seamless_Transaction( $this->_settings );

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
			/** @var QentaCEE\QMore\Response\Backend\TransferFund $response */
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
	 * @return QentaCEE\QMore\Response\Backend\Order\PaymentIterator
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
	 * @return QentaCEE\QMore\Response\Backend\Order\CreditIterator
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
    $params_post = $this->get_post_data();
		$response = $this->get_client()->approveReversal( $orderNumber );
		$transaction = new WC_Gateway_Qenta_Checkout_Seamless_Transaction( $this->_settings );

		if ( $response->hasFailed() ) {
			$this->logResponseErrors( __METHOD__, $response->getErrors() );
			$errors = array();
			foreach ( $response->getErrors() as $error ) {
				$errors[] = $error->getConsumerMessage();
			}

			return array( 'type' => 'error', 'message' => join( "<br>", $errors ) );
		} else {
			if( isset($params_post['id_tx'])){
				$transaction->update( array( 'payment_state' => 'CANCELED BY ADMIN' ), array( 'id_tx' => $params_post['id_tx'] ) );
			}
			return array( 'type' => 'updated', 'message' => __( 'APPROVEREVERSAL', 'woocommerce-qenta-checkout-seamless' ) );
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
					'message' => __( 'Refund reversal finished, but no corresponing refund found in the order. You might need to update the order manually.', 'woocommerce-qenta-checkout-seamless' )
				);
			}
		}
	}
}
