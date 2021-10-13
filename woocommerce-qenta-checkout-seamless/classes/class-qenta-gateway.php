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

require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-qenta-admin.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-qenta-config.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-qenta-datastorage.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-qenta-transaction.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-qenta-backend-operations.php' );

/**
 * Basic gateway class
 *
 * Handles payment process and payment methods
 *
 * @since 1.0.0
 */
class WC_Gateway_Qenta_Checkout_Seamless extends WC_Payment_Gateway {

	/**
     * Admin Class
     *
     * @since 1.0.0
     * @access protected
	 * @var WC_Gateway_Qenta_Checkout_Seamless_Admin
     */
	protected $_admin;

	/**
     * Config Class
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
	 * Transaction Class
     *
     * @since 1.0.0
	 * @access protected
	 * @var WC_Gateway_Qenta_Checkout_Seamless_Transaction
     */
	protected $_transaction;

	/**
	 * WC_Gateway_Qenta_Checkout_Seamless constructor.
     *
     * @since 1.0.0
     */
	public function __construct() {

		$this->id           = "woocommerce_wcs";
		$this->method_title = "Qenta Checkout Seamless";
		$this->has_fields   = true;
		$this->payment_name = '';
		$this->init_form_fields();
		$this->init_settings();

		$this->_logger      = new WC_Logger();
		$this->_admin       = new WC_Gateway_Qenta_Checkout_Seamless_Admin( $this->settings );
		$this->_config      = new WC_Gateway_Qenta_Checkout_Seamless_Config( $this->settings );
		$this->_transaction = new WC_Gateway_Qenta_Checkout_Seamless_Transaction( $this->settings );
		$this->supports     = array( 'refunds' );

		// if any of the payment types are enabled, set this to "yes", otherwise "no"
		$this->enabled = count( $this->get_enabled_payment_types( false ) ) > 0 ? "yes" : "no";
		$this->title   = 'Qenta Checkout Seamless';


		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );

		// Payment listener/API hook
		add_action(
			'woocommerce_api_wc_gateway_qenta_checkout_seamless',
			array(
				$this,
				'confirm_request'
			)
		);
		add_action(
			'woocommerce_api_wc_gateway_qenta_checkout_seamless_return',
			array(
				$this,
				'return_request'
			)
		);

		add_action(
			'woocommerce_api_wc_gateway_wcs_datastorage_return',
			array(
				$this,
				'datastorage_return'
			)
		);
		add_action(
			'woocommerce_receipt_' . $this->id,
			array(
				$this,
				'payment_page'
			)
		);
	}

	/**
	 * Load form fields saved settings from the database.
     *
     * @since 1.0.0
	 */
	public function init_form_fields() {
		global $wpdb;

		$query_string = "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'woocommerce_{$this->id}_settings'";
		$result       = $wpdb->get_row( $query_string );
		if ( ! empty( $result->option_value ) ) {
			array_merge( $this->settings, unserialize( $result->option_value ) );
		}
	}

	/**
	 * Array of enabled payment types
	 *
	 * @since 1.0.0
     *
	 * @return array
	 */
	protected function get_enabled_payment_types( $load_class = true ) {
		$types = array();
		foreach ( $this->settings as $k => $v ) {
			if ( strpos( $k, 'enable' ) !== false ) {
				if ( $v == 1 ) {
					$code = str_replace( '_enable', '', $k );
					$code = str_replace( 'wcs_', '', $code );

					$type = new stdClass();
					if ( $load_class ) {
						$class = 'WC_Gateway_Qenta_Checkout_Seamless_' . ucfirst( strtolower( str_replace( "-", "_",
						                                                                                      $code ) ) );
						$type  = new $class( $this->settings );

						if ( method_exists( $type, 'get_risk' ) ) {
							$riskvalue = $type->get_risk();
							if ( ! $riskvalue ) {
								continue;
							}
						}
					}

					if ( method_exists( $this, $code ) ) {
						if ( ! call_user_func( array( $this, $code ) ) ) {
							continue;
						}
					}
					$types[] = $type;
				}
			}
		}

		return $types;
	}

	/**
     * Processes and saves options.
	 *
     * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
     *
     * @since 1.0.0
     *
	 * @return mixed
     */
	public function process_admin_options() {
		$this->init_settings();

		$post_data = $this->get_post_data();

		foreach ( $this->_admin->get_settings_fields() as $group => $fields ) {
			foreach ( $fields as $key => $field ) {
				if ( 'title' !== $this->get_field_type( $field ) ) {
					try {
						$this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
					} catch ( Exception $e ) {
						$this->add_error( $e->getMessage() );
					}
				}
			}
		}

		return update_option( $this->get_option_key(),
		                      apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id,
		                                     $this->settings ) );
	}

	/**
	 * Generate Switch HTML.
	 *
	 * @param  mixed $key
	 * @param  mixed $data
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function generate_switch_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'label'             => '',
			'disabled'          => false,
			'css'               => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		$data['label'] = $data['title'];

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span>
					</legend>
					<label for="<?php echo esc_attr( $field_key ); ?>" class="wcs-chkbx-switch">
						<input <?php disabled( $data['disabled'], true ); ?>
							class="<?php echo esc_attr( $data['css'] ); ?>" type="checkbox"
							name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>"
							style="<?php echo esc_attr( $data['css'] ); ?>"
							value="1" <?php checked( $this->get_option( $key ),
							                         '1' ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?> />
						<div class="wcs-chkbx-switch-slider"></div>
					</label><br/>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * Admin Panel Options.
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
		$this->_admin->include_backend_header( $this );
		$this->_admin->print_admin_form_fields( $this );

	}

	/**
	 * Generate frontend payment fields
	 *
	 * @since 1.0.0
	 */
	public function payment_fields() {
		$dataStorage = new WC_Gateway_Qenta_Checkout_Seamless_Data_Storage( $this->settings );
		if ( WC()->session->get( 'consumerDeviceId' ) ) {
            $consumerDeviceId = WC()->session->get( 'consumerDeviceId' );
	    } else {
	        $timestamp = microtime();
	        $customerId = $this->_config->get_client_customer_id( $this );
	        $consumerDeviceId = md5( $customerId . "_" . $timestamp );
	        WC()->session->set( 'consumerDeviceId', $consumerDeviceId );
	    }


	    if( ($this->settings['wcs_invoice_enable'] == "1" && $this->settings['woo_wcs_invoiceprovider'] == "ratepay") ||
		    ($this->settings['wcs_installment_enable'] == "1" && $this->settings['woo_wcs_installmentprovider'] == "ratepay") )
		    {
            echo "<script language='JavaScript'>
                    var di = {t:'" . $consumerDeviceId . "',v:'WDWL',l:'Checkout'};
                  </script>
                  <script type='text/javascript' src='//d.ratepay.com/" . $consumerDeviceId . "/di.js'></script>
                  <noscript>
                    <link rel='stylesheet' type='text/css' href='//d.ratepay.com/di.css?t=" . $consumerDeviceId . "&v=WDWL&l=Checkout'>
                  </noscript>
                  <object type='application/x-shockwave-flash' data='//d.ratepay.com/WDWL/c.swf' width='0' height='0'>
                    <param name='movie' value='//d.ratepay.com/WDWL/c.swf' />
                    <param name='flashvars' value='t=" . $consumerDeviceId . "&v=WDWL'/>
                    <param name='AllowScriptAccess' value='always'/>
                  </object>";
            }


		try {
			$response = $dataStorage->init();

			if ( ! $response->hasFailed() ) {
				$this->_logger->info( __METHOD__ . ': storageid :' . $response->getStorageId() );
				$this->_logger->info( __METHOD__ . ': jsurl :' . $response->getJavascriptUrl() );
			}

			?>
			<input id="wcs_payment_method_changer" type="hidden" value="woocommerce_wcs" name="wcs_payment_method"/>
			<script type="text/javascript">
				function changeWCSPayment(code) {
					var changer = document.getElementById('wcs_payment_method_changer');
					changer.value = code;
				}
			</script>
			<script type="text/javascript" src="<?= $response->getJavascriptUrl() ?>"></script>
			<script type="text/javascript" src="<?= WOOCOMMERCE_GATEWAY_WCS_URL ?>assets/scripts/payment.js"></script>
			<link rel="stylesheet" type="text/css" href="<?= WOOCOMMERCE_GATEWAY_WCS_URL ?>assets/styles/payment.css">
			<?php
			foreach ( $this->get_enabled_payment_types() as $type ) {
				?>
				</div></li>
				<input type="hidden" name="storageId" value="<?= $response->getStorageId() ?>"/>
				<li class="wc_payment_method payment_method_woocommerce_wcs_payment">
				<input
					id="payment_method_wcs_<?php echo $type->get_payment_type() ?>"
					type="radio"
					class="input-radio"
					value="woocommerce_wcs"
					name="payment_method"
					onclick="changeWCSPayment('<?php echo $type->get_payment_type() ?>');"
					data-order_button_text>
				<label for="payment_method_wcs_<?php echo $type->get_payment_type() ?>">
					<?php echo $type->get_label();
					if ( is_array( $type->get_icon() ) ) {
						foreach ( $type->get_icon() as $icon ) {
							echo "<img src='{$icon}' alt='Qenta {$type->get_payment_type()}'>";
						}
					} else {
						echo "<img src='{$type->get_icon()}' alt='Qenta {$type->get_payment_type()}'>";
					} ?>
				</label>
			<div
				class="payment_box payment_method_wcs_<?= ( $type->has_payment_fields() ) ? $type->get_payment_type() : "" ?>"
				style="display:none;">
				<?php
				echo $type->has_payment_fields() ? $type->get_payment_fields( $response->getStorageId() ) : null;
			}

		} catch ( Exception $e ) {
			$this->_logger->emergency( __METHOD__ . ":" . $e->getMessage() );
			$this->_logger->emergency( __METHOD__ . ":" . $e->getTraceAsString() );
		}
	}

	/**
	 * Process a refund if supported.
	 *
	 * @since 1.0.0
	 *
	 * @param  int $order_id
	 * @param  float $amount
	 * @param  string $reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

        if ( empty( $this->settings['woo_wcs_backendpassword'] ) ) {
	       return new WP_Error( 'refund_error', 'No password for backend operations (Toolkit) provided. Please visit your settings!' );
	    }
		$backend_operations = new WC_Gateway_Qenta_Checkout_Seamless_Backend_Operations( $this->settings );

		return $backend_operations->refund( $order_id, $amount, $reason );
	}

	/**
	 * Handles payment and processes the order
	 *
	 * @since 1.0.0
	 *
	 * @param $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
    $params_post = array_map( 'sanitize_text_field', $_POST );
		$order = wc_get_order( $order_id );

		$payment_type = $params_post['wcs_payment_method'];

		$paymentClass = 'WC_Gateway_Qenta_Checkout_Seamless_'. str_replace('-', '_', ucfirst(strtolower($payment_type)));
		$paymentClass = new $paymentClass( $this->settings );
		update_post_meta( $order_id, '_payment_method_title', $paymentClass->get_label());


		$page_url = $order->get_checkout_payment_url(true);
		$page_url = add_query_arg( 'key', $order->get_order_key(), $page_url );
		$page_url = add_query_arg( 'order-pay', $order_id, $page_url );

		WC()->session->set( 'wcs_checkout_data', $params_post );

		return array(
			'result'   => 'success',
			'redirect' => $page_url
		);
	}

	/**
     * Handles iframe on payment page
     *
     * @since 1.0.0
     *
	 * @param $order_id
     */
	public function payment_page( $order_id ) {
		$order = new WC_Order( $order_id );

		$data = $this->initiate_payment( $order );
		if( ! $data['iframeUrl'] ) {
			$data['iframeUrl'] = $order->get_cancel_endpoint();
			header( 'Location: ' . $data['iframeUrl'] );
			die();
		} else if ( $data['wcs_payment_method'] == QentaCEE\Stdlib\PaymentTypeAbstract::SOFORTUEBERWEISUNG ) {
		    header( 'Location: ' . $data['iframeUrl'] );
			die();
		}
		?>
			<iframe src="<?php echo $data['iframeUrl'] ?>" width="100%" height="700px" border="0" frameborder="0">
				<p>Your browser does not support iframes.</p>
			</iframe>
		<?php
		die();
	}

	/**
	 * Initialization of Qenta payment
	 *
	 * @since 1.0.0
	 *
	 * @param $order WC_Order
	 *
	 * @return array
	 * @throws Exception
	 */
	public function initiate_payment( $order ) {
		global $woocommerce;

		$checkout_data = WC()->session->get( 'wcs_checkout_data' );

		try {
			$config_array = $this->_config->get_client_config();
			$client       = new QentaCEE\QMore\FrontendClient( $config_array );


			$return_url = add_query_arg( 'wc-api', 'WC_Gateway_Qenta_Checkout_Seamless',
			                             home_url( '/', is_ssl() ? 'https' : 'http' ) );

			$consumer_data = $this->_config->get_consumer_data( $order, $this, $checkout_data );
			$auto_deposit  = $this->get_option( 'woo_wcs_automateddeposit' );
			$service_url   = $this->get_option( 'woo_wcs_serviceurl' );

			// Check if service url is valid
			if ( filter_var( $service_url, FILTER_VALIDATE_URL ) === false ) {
				wc_add_notice( __( "Service URL is invalid", 'woocommerce-qenta-checkout-seamless' ), 'error' );

				return;
			}

			$cart = new WC_Cart();
			$cart->get_cart_from_session();

			$transaction_id = $this->_transaction->get_existing_transaction( $order->get_id() );

			if( $transaction_id ) {
				$this->_transaction->update( array(
					'id_order' => $order->get_id(),
					'amount' => $order->get_total(),
					'currency' => get_woocommerce_currency(),
					'payment_method' => $checkout_data['wcs_payment_method'],
					'payment_state' => 'CREATED'
				),
				array( 'id_tx' => $transaction_id ) );
			} else {
				$transaction_id = $this->_transaction->create( $order->get_id(), $order->get_total(),
			                                               get_woocommerce_currency(), $checkout_data['wcs_payment_method'] );
			}

			if ( $transaction_id == 0 ) {
				wc_add_notice( __( "Creating transaction entry failed!", 'woocommerce-qenta-checkout-seamless' ),
					               'error' );

				$this->_logger->error( __METHOD__ . ': Creating transaction entry failed before initializing.' );

				return;
			}

			$client->setPluginVersion( $this->_config->get_plugin_version() );
			$client->setOrderReference( $this->_config->get_order_reference( $order ) );

			$client->setAmount( $order->get_total() )
			       ->setCurrency( get_woocommerce_currency() )
			       ->setPaymentType( $checkout_data['wcs_payment_method'] )
			       ->setOrderDescription( $this->_config->get_order_description( $order ) )
			       ->setSuccessUrl( $this->create_return_url( $order, 'SUCCESS' ) )
			       ->setPendingUrl( $this->create_return_url( $order, 'PENDING' ) )
			       ->setCancelUrl( $this->create_return_url( $order, 'CANCEL' ) )
			       ->setFailureUrl( $this->create_return_url( $order, 'FAILURE' ) )
			       ->setConfirmUrl( $return_url )
			       ->setServiceUrl( $service_url )
			       ->setAutoDeposit( $auto_deposit )
			       ->setConsumerData( $consumer_data )
			       ->setStorageId( $checkout_data['storageId'] )
			       ->setOrderIdent( $woocommerce->session->get( 'wcs_session_order_ident' ) )
			       ->createConsumerMerchantCrmId( $order->get_billing_email() );

			if ( WC()->session->get( 'consumerDeviceId' ) ) {
			    $client->consumerDeviceId = WC()->session->get( 'consumerDeviceId' );
			    WC()->session->set( 'consumerDeviceId', false );
			}

			switch ( $checkout_data['wcs_payment_method'] ){
				case QentaCEE\QMore\PaymentType::EPS : $client->setFinancialInstitution($checkout_data['woo_wcs_eps_financialInstitution']);
				break;
				case QentaCEE\QMore\PaymentType::IDL : $client->setFinancialInstitution($checkout_data['woo_wcs_idl_financialInstitution']);
				break;
				case QentaCEE\QMore\PaymentType::TRUSTPAY : $client->setFinancialInstitution($checkout_data['woo_wcs_trustpay_financialInstitution']);
				break;
			}

			$client->setCustomerStatement( $this->_config->get_customer_statement( $order, $checkout_data['wcs_payment_method'] ) );

			if ( $this->get_option( 'woo_wcs_notificationemail' ) ) {
				$client->setConfirmMail( get_bloginfo( 'admin_email' ) );
			}

			if ( $this->get_option( 'woo_wcs_forwardbasketdata' )
			|| ( $this->_config->force_basket_data( $checkout_data['wcs_payment_method'], $this ) ) ) {
				$client->setBasket( $this->_config->get_shopping_basket( $order->get_total() ) );
			}

			$client->wooOrderId    = $order->get_id();
			$client->transactionId = $transaction_id;

			$this->_logger->info( print_r( $client->getRequestData(), true ) );

			$initResponse = $client->initiate();

			$this->_transaction->update( array(
				                             'request' => serialize( $client->getRequestData() )
			                             ), array( 'id_tx' => $transaction_id ) );

			if ( $initResponse->hasFailed() ) {


				foreach ( $initResponse->getErrors() as $error ) {
					wc_add_notice( __( "Response failed! Error: {$error->getConsumerMessage()}",
					                   'woocommerce-qenta-checkout-seamless' ),
					               'error' );

					$this->_logger->error( __METHOD__ . ': ' . $error->getConsumerMessage() );

					return;
				}


				$this->_transaction->update( array(
					                             'payment_state' => 'INITIATED',
					                             'message'       => 'error',
					                             'modified'      => current_time( 'mysql', true )
				                             ),
				                             array( 'id_tx' => $transaction_id ) );
			} else {

				$this->_transaction->update( array(
					                             'payment_state'   => 'INITIATED',
					                             'message'         => 'ok',
					                             'order_reference' => $this->_config->get_order_reference( $order ),
					                             'modified'        => current_time( 'mysql', true )
				                             ),
				                             array( 'id_tx' => $transaction_id ) );

			}
		} catch ( Exception $e ) {
			$this->_logger->error( __METHOD__ . ': ' . $e->getMessage() );
			$this->_transaction->update( array(
				                             'payment_state' => 'INITIATED',
				                             'message'       => 'error',
				                             'modified'      => current_time( 'mysql', true )
			                             ),
			                             array( 'id_tx' => $transaction_id ) );
			throw ( $e );
		}

		return ['iframeUrl'          => $initResponse->getRedirectUrl(),
		        'wcs_payment_method' => $checkout_data['wcs_payment_method']];
	}

	/**
	 * Create return url for different paymentstates
	 *
	 * @since 1.0.0
	 *
	 * @param $order
	 * @param $payment_state
	 *
	 * @return mixed
	 */
	public function create_return_url( $order, $payment_state ) {
		$return_url = add_query_arg( array(
			                             'wc-api'       => 'WC_Gateway_Qenta_Checkout_Seamless_Return',
			                             'order-id'     => $order->get_id(),
			                             'paymentState' => $payment_state
		                             ),
		                             home_url( '/', is_ssl() ? 'https' : 'http' ) );

		return $return_url;
	}

	/**
	 * Validate response from server and edit payment informations
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function confirm_request() {
    $params_post = array_map( 'sanitize_text_field', $_POST );
    $params_request = array_map( 'sanitize_text_field', $_REQUEST );
		$message = null;

    foreach ( $params_request as &$param ) {
      $param = stripslashes( $param );
    }
    foreach ( $params_post as &$param ) {
      $param = stripslashes( $param );
    }

		if ( ! isset( $params_request['wooOrderId'] ) || ! strlen( $params_request['wooOrderId'] ) ) {
			$message = 'order-id missing';
			$this->_logger->error( __METHOD__ . ':' . $message );

			print QentaCEE\QMore\ReturnFactory::generateConfirmResponseString( $message );
			exit();
		}

		$order_id       = $params_request['wooOrderId'];
		$transaction_id = $params_request['transactionId'];
		$order          = new WC_Order( $order_id );

		if ( ! $order->get_id() ) {
			$message = "order with id `" . esc_html($order->get_id()) . "` not found";
			$this->_logger->error( __METHOD__ . ':' . $message );

			print QentaCEE\QMore\ReturnFactory::generateConfirmResponseString( $message );
			exit();
		}

		if ( $order->get_status() == "processing" || $order->get_status() == "completed" ) {
			$message = "cannot change the order with id `" . esc_html($order->get_id()) . "`";
			$this->_logger->error( __METHOD__ . ':' . $message );

			print QentaCEE\QMore\ReturnFactory::generateConfirmResponseString( $message );
			exit();
		}

		//save updated payment data in extra field
		if ( get_post_meta( $order->get_id(), 'wcs_data', true ) ) {
			add_post_meta( sanitize_text_field($order->get_id()), 'wcs_updated_data', $this->create_payment_data(), false );
		} else {
			add_post_meta( sanitize_text_field($order->get_id()), 'wcs_data', $this->create_payment_data(), false );
		}

		$message = null;
		try {
			$return = QentaCEE\QMore\ReturnFactory::getInstance(
				$params_post,
				$this->_config->get_client_secret( $this )
			);
			if ( ! $return->validate() ) {

				$message = __( 'Validation error: invalid response', 'woocommerce-qenta-checkout-seamless' );
				$this->_logger->error( __METHOD__ . ':' . $message );
				$order->update_status( 'failed', $message );

				print QentaCEE\QMore\ReturnFactory::generateConfirmResponseString( $message );
				exit();
			}

			$this->_logger->info( __METHOD__ . ':' . print_r( $return->getReturned(), true ) );

			//save new payment state in updated field
			if ( get_post_meta( $order->get_id(), 'wcs_payment_state', true ) ) {
				add_post_meta( sanitize_text_field($order->get_id()), 'wcs_updated_payment_state', $return->getPaymentState(), false );
			} else {
				add_post_meta( sanitize_text_field($order->get_id()), 'wcs_payment_state', $return->getPaymentState(), false );
			}

			switch ( $return->getPaymentState() ) {
				case QentaCEE\QMore\ReturnFactory::STATE_SUCCESS:
					update_post_meta( sanitize_text_field($order->get_id()), 'wcs_gateway_reference_number',
					                  $return->getGatewayReferenceNumber() );
					update_post_meta( sanitize_text_field($order->get_id()), 'wcs_order_number', $return->getOrderNumber() );
					$this->_transaction->update( array_map( 'sanitize_text_field', 
                                          array(
                                            'payment_state'     => $return->getPaymentState(),
                                            'message'           => 'ok',
                                            'response'          => serialize( $return->getReturned() ),
                                            'gateway_reference' => $return->getGatewayReferenceNumber(),
                                            'modified'          => current_time( 'mysql', true )
                                          )
                                        ),
                                        array_map( 'sanitize_text_field', array( 'id_tx' => $transaction_id ) ));
					$order->payment_complete();
					print QentaCEE\QMore\ReturnFactory::generateConfirmResponseString( $message );
					exit();

				case QentaCEE\QMore\ReturnFactory::STATE_PENDING:
					$order->update_status(
						'on-hold',
						__( 'Awaiting payment notification from 3rd party.', 'woocommerce-qenta-checkout-seamless' )
					);
					$this->_transaction->update( array_map( 'sanitize_text_field',
                                          array(
                                            'payment_state' => $return->getPaymentState(),
                                            'message'       => 'ok',
                                            'modified'      => current_time( 'mysql', true )
                                          )
                                        ),
                                        array_map( 'sanitize_text_field', array( 'id_tx' => $transaction_id ) )
                                      );

					print QentaCEE\QMore\ReturnFactory::generateConfirmResponseString( $message );
					exit();

				case QentaCEE\QMore\ReturnFactory::STATE_CANCEL:
					$order->update_status( 'cancelled',
					                       __( 'Payment cancelled.', 'woocommerce-qenta-checkout-seamless' ) );
          $this->_transaction->update( array_map( 'sanitize_text_field',
                                          array(
                                            'payment_state' => $return->getPaymentState(),
                                            'message'       => 'ok',
                                            'modified'      => current_time( 'mysql', true )
                                          )
                                        ),
                                        array_map( 'sanitize_text_field', array( 'id_tx' => $transaction_id ) )
                                      );

					print QentaCEE\QMore\ReturnFactory::generateConfirmResponseString( $message );
					die();

				case QentaCEE\QMore\ReturnFactory::STATE_FAILURE:
					$errors = array();
					foreach ( $return->getErrors() as $error ) {
						$errors[] = $error->getConsumerMessage();
						$message  = $error->getConsumerMessage();
					}
					$order->update_status(
						'failed',
						sanitize_text_field(join( '<br/>', $errors ))
					);
          $this->_transaction->update( array_map( 'sanitize_text_field',
                                          array(
                                            'payment_state' => $return->getPaymentState(),
                                            'message'       => 'error',
                                            'modified'      => current_time( 'mysql', true )
                                          )
                                        ),
                                        array_map( 'sanitize_text_field', array( 'id_tx' => $transaction_id ) )
                                      );

					print QentaCEE\QMore\ReturnFactory::generateConfirmResponseString( $message );
					die();

				default:
					break;
			}
		} catch ( Exception $e ) {
			$order->update_status( 'failed', $e->getMessage() );
			$message = $e->getMessage();
			$this->_logger->error( __METHOD__ . ':' . $message );
      $this->_transaction->update( array_map( 'sanitize_text_field',
                                      array(
                                        'payment_state' => $return->getPaymentState(),
                                        'message'       => 'error',
                                        'modified'      => current_time( 'mysql', true )
                                      )
                                    ),
                                    array_map( 'sanitize_text_field', array( 'id_tx' => $transaction_id ) )
                                  );
		}

		print QentaCEE\QMore\ReturnFactory::generateConfirmResponseString( $message );
		exit();
	}

	/**
	 * Create payment data for orderoverview
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function create_payment_data() {
		$data = '';
    $params_post = array_map( 'sanitize_text_field', $_POST );
		foreach ( $params_post as $key => $value ) {
			$data .= "$key:$value\n";
		}
		$data = trim( $data );

		return $data;
	}

	/**
	 * Redirect to specific return URL
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function return_request() {
    global $woocommerce;

    $params_request = array_map( 'sanitize_text_field', $_REQUEST );

	  // reset wcs_session_order_ident so that the new order gets new datastorage id
		$woocommerce->session->set( 'wcs_session_order_ident', null );

		$redirectUrl = $this->get_return_url();
		WC()->session->set( 'wcs_checkout_data', array() );

		if ( !array_key_exists( 'redirected', $_REQUEST ) ) {
        	$url = add_query_arg( array(
        		'wc-api' => 'WC_Gateway_Qenta_Checkout_Seamless_Return',
        		'order-id' => isset( $params_request['order-id'] ) ? $params_request['order-id'] : '',
        		'paymentState' => isset( $params_request['paymentState'] ) ? $params_request['paymentState'] : 'FAILURE'
        		), home_url( '/', is_ssl() ? 'https' : 'http' ) );
        		wc_get_template(
        			'templates/back.php',
        			array(
        				'url' => $url
        			),
        			WOOCOMMERCE_GATEWAY_WCS_BASEDIR,
        			WOOCOMMERCE_GATEWAY_WCS_BASEDIR
        		);
        		exit();
        }

        if ( ! isset( $_REQUEST['order-id'] ) || ! strlen( $params_request['order-id'] ) ) {
			wc_add_notice( __( 'Order-Id missing', 'woocommerce-qenta-checkout-seamless' ), 'error' );
			$this->_logger->notice( __METHOD__ . ': Order-Id missing' );

			header( 'Location: ' . esc_url_raw($redirectUrl) );

			exit();
		}

    $this->_logger->notice( __METHOD__ . ':' . print_r( $params_request, true ) );

		$order_id = $params_request['order-id'];
		$order    = new WC_Order( $order_id );
    $consumerMessage = '';

		switch ( $params_request['paymentState'] ) {
			case QentaCEE\QMore\ReturnFactory::STATE_SUCCESS:
			case QentaCEE\QMore\ReturnFactory::STATE_PENDING:
				$redirectUrl = $this->get_return_url( $order );
				break;

			case QentaCEE\QMore\ReturnFactory::STATE_CANCEL:
				wc_add_notice( __( 'Payment has been cancelled.', 'woocommerce-qenta-checkout-seamless' ), 'error' );
				$redirectUrl = $order->get_cancel_endpoint();
				break;

			case QentaCEE\QMore\ReturnFactory::STATE_FAILURE:
			    // get error messages from order
			    if ( get_post_meta( $order_id, 'wcs_data', true ) ) {
			        $errors = get_post_meta( $order_id, 'wcs_data', true );
			        if ( strpos( $errors, 'error_1_consumerMessage:' ) && strpos( $errors, 'error_1_paySysMessage:' ) ) {
			            $start = strlen( 'error_1_consumerMessage:' ) + strpos( $errors, 'error_1_consumerMessage:' );
			            $end = strpos( $errors, 'error_1_paySysMessage:' ) - $start;
			            $consumerMessage = substr( $errors, $start, $end );
			        }
		        }
		        if( strlen( $consumerMessage ) ) {
			        wc_add_notice( __( $consumerMessage, 'woocommerce-qenta-checkout-seamless' ), 'error' );
		        } else {
				    wc_add_notice( __( 'Payment has failed.', 'woocommerce-qenta-checkout-seamless' ), 'error' );
				}
				$redirectUrl = $order->get_cancel_endpoint();
				break;
			default:
				break;
		}
		header( 'Location: ' . esc_url_raw($redirectUrl) );
		exit();
	}

	/**
	 * Handles thank you text for pending payment
	 *
	 * @since 1.0.0
	 *
	 * @param $var
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public function thankyou_order_received_text( $var, $order ) {
	    if ($order->get_payment_method() === "woocommerce_wcs") {
            $metadata = $order->get_meta( 'wcs_data' );

            $metadata = explode( "\n", $metadata );

            if( is_array( $metadata ) );
            foreach( $metadata as $line ) {
                $line = explode( ":", $line );
                if( isset( $line[0] ) && $line[0] == 'paymentType' ){
                    $paymentClass = 'WC_Gateway_Qenta_Checkout_Seamless_'.
                    str_replace('-', '_', ucfirst(strtolower($line[1])));
                    $paymentClass = new $paymentClass( $this->settings );

                    $order->set_payment_method_title( $paymentClass->get_label() );
                }
            }
            if ( $order->get_status() == 'on-hold' ) {
                $var = '<h3>' . __( 'Payment verification is pending',
                                    'woocommerce-qenta-checkout-seamless' ) . '</h3>' . __(
                           'Your order will be processed as soon as we receive the payment confirmation from your bank.',
                           'woocommerce-qenta-checkout-seamless'
                       );
            }

            return $var;
		}
		return;
	}

	/**
	 * validate input data from payment_fields
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function validate_fields() {
		$args = $this->get_post_data();

		$payment_class = 'WC_Gateway_Qenta_Checkout_Seamless_' . ucfirst( strtolower( str_replace( "-", "_",
		                                                                                              $args['wcs_payment_method'] ) ) );

    if (class_exists($payment_class)) {
        $payment_class = new $payment_class($this->settings);

        if (method_exists($payment_class, 'validate_payment_fields')) {
            $validation = $payment_class->validate_payment_fields($args);
            if (true === $validation) {
                return true;
            }
            wc_add_notice($validation, 'error');

            return;
        }
    }

		return true;
	}

	/**
	* Loads data storage
	 *
	 * @since 1.0.0
	 */
	public function datastorage_return() {
		die( require_once 'includes/datastorage_fallback.php' );
	}

	/**
	 * Handles specific transaction
	 *
	 * @since 1.0.0
	 */
	public function qenta_transaction_do_page() {
    $params_post = array_map( 'sanitize_text_field', $_POST );
    $params_request = array_map( 'sanitize_text_field', $_REQUEST );
		echo "<div class='wrap'>";

		$this->_admin->include_backend_header( $this );

	    if ( empty( $this->settings['woo_wcs_backendpassword'] ) ) {
	        $this->qenta_transactions_error_page( 'No password for backend operations (Toolkit) provided. Please visit your settings!' );
	        return false;
	    }
		$backend_operations = new WC_Gateway_Qenta_Checkout_Seamless_Backend_Operations( $this->settings );

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] == 'POST' ) {

			if ( ! isset( $_POST['wcs-do-bop'] ) || ! wp_verify_nonce( $params_post['wcs-do-bop'], 'wcs-do-bop' ) ) {
				$this->_logger->error( __METHOD__ . ":ERROR:" . __( "Prevented possible CSRF attack." ) );
				die( 'CSRF Protection prevented you from doing this operation.' );
			}

			$operation = $backend_operations->do_backend_operation(
				( isset( $_POST['paymentNumber'] ) ) ? $params_post['paymentNumber'] : $params_post['creditNumber'],
				$params_post['orderNumber'],
				$params_post['currency'],
				( isset( $_POST['amount'] ) ? round( $params_post['amount'], wc_get_rounding_precision() ) : 0 ),
				$params_post['submitWcsBackendOperation'],
				( isset( $_POST['wcOrder'] ) ) ? $params_post['wcOrder'] : null );

			add_settings_error( '', '', $operation['message'], $operation['type'] );
		}

		settings_errors();


		$id_tx = $params_request['id'];

		$tx = $this->_transaction->get( $id_tx );

		if ( empty ( $id_tx ) ) {
			$this->qenta_transactions_do_page();
		}

		$data = $tx;

		$wc_order = new WC_Order( $tx->id_order );

		$data->order_number  = $wc_order->get_meta( 'wcs_order_number' );
		$data->order_details = $backend_operations->get_order_details( $data->order_number )->getOrder()->getData();
		$data->payments      = $backend_operations->get_payments( $data->order_number )->getArray();
		$data->credits       = $backend_operations->get_credits( $data->order_number )->getArray();


		uasort( $data->payments, function ( $a, $b ) {
			$a = $a->getData();
			$b = $b->getData();

			return new DateTime( $a['timeCreated'] ) > new DateTime( $b['timeCreated'] );
		} );

		uasort( $data->credits, function ( $a, $b ) {
			$a = $a->getData();
			$b = $b->getData();

			return new DateTime( $a['timeCreated'] ) > new DateTime( $b['timeCreated'] );
		} );

		$this->_admin->print_transaction_details( $data );
	}

	/**
	 * Handles transaction overview
	 * 
	 * @since 1.0.0
	 */
	public function qenta_transactions_do_page() {
    $params_get = array_map( 'sanitize_text_field', $_GET );
		echo "<div class='wrap woocommerce'>";
		$this->_admin->include_backend_header( $this );

		$transaction_start = ! isset( $_GET['transaction_start'] ) ? 1 : $params_get['transaction_start'];
		$this->_admin->print_transaction_table( $this->_transaction, $transaction_start );
		unset( $_GET['transaction_start'] );
		echo "</div>";
	}

	/**
	 * Handles transaction error overview
	 */
	public function qenta_transactions_error_page( $error_msg ) {
		echo "<div class='wrap woocommerce'>";
		echo htmlentities( $error_msg );
		echo "</div>";
	}

	/**
	 * Opens the support request form
	 *
	 * @since 1.0.0
	 */
	public function do_support_request() {
		$this->_admin->include_backend_header( $this );
		$this->_admin->print_support_form();
	}

}
