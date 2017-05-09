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

require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-wirecard-admin.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-wirecard-config.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-wirecard-datastorage.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-wirecard-transaction.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-wirecard-backend-operations.php' );

/**
 * Class WC_Gateway_Wirecard_Checkout_Seamless
 */
class WC_Gateway_Wirecard_Checkout_Seamless extends WC_Payment_Gateway {

	protected $_admin;
	protected $_config;
	protected $_logger;
	protected $_transaction;

	public function __construct() {

		$this->id           = "woocommerce_wcs";
		$this->method_title = "Wirecard Checkout Seamless";
		$this->has_fields   = true;
		$this->payment_name = '';
		$this->init_form_fields();
		$this->init_settings();

		$this->_logger      = new WC_Logger();
		$this->_admin       = new WC_Gateway_Wirecard_Checkout_Seamless_Admin( $this->settings );
		$this->_config      = new WC_Gateway_Wirecard_Checkout_Seamless_Config( $this->settings );
		$this->_transaction = new WC_Gateway_Wirecard_Checkout_Seamless_Transaction( $this->settings );
		$this->supports     = array( 'refunds' );

		// if any of the payment types are enabled, set this to "yes", otherwise "no"
		$this->enabled = count( $this->get_enabled_payment_types( false ) ) > 0 ? "yes" : "no";
		$this->title   = 'Wirecard Checkout Seamless';


		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );

		// Payment listener/API hook
		add_action(
			'woocommerce_api_wc_gateway_wirecard_checkout_seamless',
			array(
				$this,
				'confirm_request'
			)
		);
		add_action(
			'woocommerce_api_wc_gateway_wirecard_checkout_seamless_return',
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
	 * @access public
	 * @return void
	 */
	function init_form_fields() {
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
						$class = 'WC_Gateway_Wirecard_Checkout_Seamless_' . ucfirst( strtolower( str_replace( "-", "_",
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
	 * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
	 *
	 * @since 1.0.0
	 * @return bool was anything saved?
	 */
	function process_admin_options() {
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
	function generate_switch_html( $key, $data ) {
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
	 * INFO: Only for temporary testing
	 *
	 * @since 1.0.0
	 */
	function payment_fields() {
		$dataStorage = new WC_Gateway_Wirecard_Checkout_Seamless_Data_Storage( $this->settings );

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
							echo "<img src='{$icon}' alt='Wirecard {$type->get_payment_type()}'>";
						}
					} else {
						echo "<img src='{$type->get_icon()}' alt='Wirecard {$type->get_payment_type()}'>";
					} ?>
				</label>
			<div
				class="payment_box payment_method_wcs_<?= ( $type->has_payment_fields() ) ? $type->get_payment_type() : "" ?>"
				style="display:none;">
				<?php
				echo $type->has_payment_fields() ? $type->get_payment_fields( $response->getStorageId() ) : null;
			}

		} catch ( Exception $e ) {
			$this->_logger->emergency( __METHOD__ . ":" . print_r( $e, true ) );
		}
	}

	/**
	 * Process a refund if supported.
	 *
	 * @param  int $order_id
	 * @param  float $amount
	 * @param  string $reason
	 *
	 * @return bool True or false based on success, or a WP_Error object
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$backend_operations = new WC_Gateway_Wirecard_Checkout_Seamless_Backend_Operations( $this->settings );

		return $backend_operations->refund();
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
	function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$payment_type = $_POST['wcs_payment_method'];
		WC()->session->wirecard_checkout_seamless_payment_type = $payment_type;

		$page_url = $order->get_checkout_payment_url(true);
		$page_url = add_query_arg( 'key', $order->get_order_key(), $page_url );
		$page_url = add_query_arg( 'order-pay', $order_id, $page_url );
		$page_url = add_query_arg( 'storage-id', $_POST['storageId'], $page_url );

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
	function payment_page( $order_id ) {
		$order = new WC_Order( $order_id );

		$iframeUrl = $this->initiate_payment( $order, WC()->session->wirecard_checkout_seamless_payment_type );
		?>
			<iframe src="<?php echo $iframeUrl ?>" width="100%" height="700px" border="0" frameborder="0">
				<p>Your browser does not support iframes.</p>
			</iframe>
		<?php
	}

	/**
	 * Initialization of Wirecard payment
	 *
	 * @since 1.0.0
	 *
	 * @param $order WC_Order
	 * @param $payment_type
	 *
	 * @return string
	 * @throws Exception
	 */
	function initiate_payment( $order, $payment_type ) {

		try {
			$config_array = $this->_config->get_client_config();
			$client       = new WirecardCEE_QMore_FrontendClient( $config_array );


			$return_url = add_query_arg( 'wc-api', 'WC_Gateway_Wirecard_Checkout_Seamless',
			                             site_url( '/', is_ssl() ? 'https' : 'http' ) );

			$consumer_data = $this->_config->get_consumer_data( $order, $this );
			$auto_deposit  = $this->get_option( 'woo_wcs_automateddeposit' );
			$service_url   = $this->get_option( 'woo_wcs_serviceurl' );

			// Check if service url is valid
			if ( filter_var( $service_url, FILTER_VALIDATE_URL ) === false ) {
				wc_add_notice( __( "Service URL is invalid", 'woocommerce-wirecard-checkout-seamless' ), 'error' );

				return;
			}


			$cart = new WC_Cart();
			$cart->get_cart_from_session();

			$transaction_id = $this->_transaction->create( $order->get_id(), $order->get_total(),
			                                               get_woocommerce_currency(), $payment_type );

			$client->setPluginVersion( $this->_config->get_plugin_version() );
			$client->setOrderReference( $this->_config->get_order_reference( $order ) );

			$client->setAmount( $order->get_total() )
			       ->setCurrency( get_woocommerce_currency() )
			       ->setPaymentType( $payment_type )
			       ->setOrderDescription( $this->_config->get_order_description( $order ) )
			       ->setSuccessUrl( $this->create_return_url( $order, 'SUCCESS' ) )
			       ->setPendingUrl( $this->create_return_url( $order, 'PENDING' ) )
			       ->setCancelUrl( $this->create_return_url( $order, 'CANCEL' ) )
			       ->setFailureUrl( $this->create_return_url( $order, 'FAILURE' ) )
			       ->setConfirmUrl( $return_url )
			       ->setServiceUrl( $service_url )
			       ->setAutoDeposit( $auto_deposit )
			       ->setConsumerData( $consumer_data )
			       ->setStorageId( $_GET['storage-id'] )
			       ->setOrderIdent( md5( implode( "", ( array_keys( $cart->cart_contents ) ) ) ) )
			       ->createConsumerMerchantCrmId( $order->get_billing_email() );

			$this->_config->set_customer_statement( $client, $this );

			if ( $this->get_option( 'woo_wcs_notificationemail' ) ) {
				$client->setConfirmMail( get_bloginfo( 'admin_email' ) );
			}

			if ( $this->get_option( 'woo_wcs_transactionid' ) == 'gatewayreferencenumber' ) {
				//TODO: shop-specific order number
			}

			if ( $this->get_option( 'woo_wcs_forwardbasketdata' ) ) {
				$client->setBasket( $this->_config->get_shopping_basket() );
			}

			$client->wooOrderId    = $order->get_id();
			$client->transactionId = $transaction_id;

			$initResponse = $client->initiate();

			$this->_transaction->update( array(
				                             'request' => serialize( $client->getRequestData() )
			                             ), array( 'id_tx' => $transaction_id ) );

			if ( $initResponse->hasFailed() ) {


				foreach ( $initResponse->getErrors() as $error ) {
					wc_add_notice( __( "Response failed! Error: {$error->getConsumerMessage()}",
					                   'woocommerce-wirecard-checkout-seamless' ),
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

		return $initResponse->getRedirectUrl();
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
	function create_return_url( $order, $payment_state ) {
		$return_url = add_query_arg( array(
			                             'wc-api'       => 'WC_Gateway_Wirecard_Checkout_Seamless_Return',
			                             'order-id'     => $order->get_id(),
			                             'paymentState' => $payment_state
		                             ),
		                             site_url( '/', is_ssl() ? 'https' : 'http' ) );

		return $return_url;
	}

	/**
	 * Validate response from server and edit payment informations
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function confirm_request() {
		$message = null;

		if ( ! isset( $_REQUEST['wooOrderId'] ) || ! strlen( $_REQUEST['wooOrderId'] ) ) {
			$message = 'order-id missing';
			$this->_logger->error( __METHOD__ . ':' . $message );

			print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
			die();
		}

		$order_id       = $_REQUEST['wooOrderId'];
		$transaction_id = $_REQUEST['transactionId'];
		$order          = new WC_Order( $order_id );

		if ( ! $order->get_id() ) {
			$message = "order with id `$order->get_id()` not found";
			$this->_logger->error( __METHOD__ . ':' . $message );


			print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
			die();
		}

		if ( $order->get_status() == "processing" || $order->get_status() == "completed" ) {
			$message = "cannot change the order with id `$order->get_id()`";
			$this->_logger->error( __METHOD__ . ':' . $message );


			print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
			die();
		}

		//save updated payment data in extra field
		if ( get_post_meta( $order->get_id(), 'wcs_data', true ) ) {
			add_post_meta( $order->get_id(), 'wcs_updated_data', $this->create_payment_data(), false );
		} else {
			add_post_meta( $order->get_id(), 'wcs_data', $this->create_payment_data(), false );
		}

		$message = null;
		try {
			$return = WirecardCEE_QMore_ReturnFactory::getInstance(
				$_POST,
				$this->_config->get_client_secret( $this )
			);
			if ( ! $return->validate() ) {

				$message = __( 'Validation error: invalid response', 'woocommerce-wirecard-checkout-seamless' );
				$this->_logger->error( __METHOD__ . ':' . $message );
				$order->update_status( 'failed', $message );

				print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
				die();
			}

			$this->_logger->info( __METHOD__ . ':' . print_r( $return->getReturned(), true ) );

			//save new payment state in updated field
			if ( get_post_meta( $order->get_id(), 'wcs_payment_state', true ) ) {
				add_post_meta( $order->get_id(), 'wcs_updated_payment_state', $return->getPaymentState(), false );
			} else {
				add_post_meta( $order->get_id(), 'wcs_payment_state', $return->getPaymentState(), false );
			}

			switch ( $return->getPaymentState() ) {
				case WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS:
					update_post_meta( $order->get_id(), 'wcs_gateway_reference_number',
					                  $return->getGatewayReferenceNumber() );
					update_post_meta( $order->get_id(), 'wcs_order_number', $return->getOrderNumber() );
					$this->_transaction->update( array(
						                             'payment_state'     => $return->getPaymentState(),
						                             'message'           => 'ok',
						                             'response'          => serialize( $return->getReturned() ),
						                             'gateway_reference' => $return->getGatewayReferenceNumber(),
						                             'modified'          => current_time( 'mysql', true )
					                             ),
					                             array( 'id_tx' => $transaction_id ) );
					$order->payment_complete();
					print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
					die();

				case WirecardCEE_QMore_ReturnFactory::STATE_PENDING:
					$order->update_status(
						'on-hold',
						__( 'Awaiting payment notification from 3rd party.', 'woocommerce-wirecard-checkout-seamless' )
					);
					$this->_transaction->update( array(
						                             'payment_state' => $return->getPaymentState(),
						                             'message'       => 'ok',
						                             'modified'      => current_time( 'mysql', true )
					                             ),
					                             array( 'id_tx' => $transaction_id ) );

					print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
					die();

				case WirecardCEE_QMore_ReturnFactory::STATE_CANCEL:
					$order->update_status( 'cancelled',
					                       __( 'Payment cancelled.', 'woocommerce-wirecard-checkout-seamless' ) );
					$this->_transaction->update( array(
						                             'payment_state'     => $return->getPaymentState(),
						                             'message'           => 'ok',
						                             'gateway_reference' => $return->getGatewayReferenceNumber(),
						                             'modified'          => current_time( 'mysql', true )
					                             ),
					                             array( 'id_tx' => $transaction_id ) );

					print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
					die();

				case WirecardCEE_QMore_ReturnFactory::STATE_FAILURE:
					$errors = array();
					foreach ( $return->getErrors() as $error ) {
						$errors[] = $error->getConsumerMessage();
						$message  = $error->getConsumerMessage();
					}
					$order->update_status(
						'failed',
						join( '<br/>', $errors )
					);
					$this->_transaction->update( array(
						                             'payment_state'     => $return->getPaymentState(),
						                             'message'           => 'error',
						                             'gateway_reference' => $return->getGatewayReferenceNumber(),
						                             'modified'          => current_time( 'mysql', true )
					                             ),
					                             array( 'id_tx' => $transaction_id ) );

					print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
					die();

				default:
					break;
			}
		} catch ( Exception $e ) {
			$order->update_status( 'failed', $e->getMessage() );
			$message = $e->getMessage();
			$this->_logger->error( __METHOD__ . ':' . $message );
			$this->_transaction->update( array(
				                             'payment_state'     => $return->getPaymentState(),
				                             'message'           => 'error',
				                             'gateway_reference' => $return->getGatewayReferenceNumber(),
				                             'modified'          => current_time( 'mysql', true )
			                             ),
			                             array( 'id_tx' => $transaction_id ) );
		}

		print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
		die();
	}

	/**
	 * Create payment data for orderoverview
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function create_payment_data() {
		$data = '';
		foreach ( $_POST as $key => $value ) {
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
	function return_request() {
		$redirectUrl = $this->get_return_url();

		$this->_logger->notice( __METHOD__ . ':' . print_r( $_REQUEST, true ) );
		if ( ! isset( $_REQUEST['order-id'] ) || ! strlen( $_REQUEST['order-id'] ) ) {
			wc_add_notice( __( 'Order-Id missing', 'woocommerce-wirecard-checkout-seamless' ), 'error' );
			$this->_logger->notice( __METHOD__ . ': Order-Id missing' );

			header( 'Location: ' . $redirectUrl );
		}

		if ( !array_key_exists( 'redirected', $_REQUEST ) ) {
        	$url = add_query_arg( array(
        		'wc-api' => 'WC_Gateway_Wirecard_Checkout_Seamless_Return',
        		'order-id' => $_REQUEST['order-id'],
        		'paymetState' => $_REQUEST['paymentState']
        		), site_url( '/', is_ssl() ? 'https' : 'http' ) );
        		wc_get_template(
        			'templates/back.php',
        			array(
        				'url' => $url
        			),
        			WOOCOMMERCE_GATEWAY_WCS_BASEDIR,
        			WOOCOMMERCE_GATEWAY_WCS_BASEDIR
        		);
        		die();
        }

		$order_id = $_REQUEST['order-id'];
		$order    = new WC_Order( $order_id );

		switch ( $_REQUEST['paymentState'] ) {
			case WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS:
			case WirecardCEE_QMore_ReturnFactory::STATE_PENDING:
				$redirectUrl = $this->get_return_url( $order );
				break;

			case WirecardCEE_QMore_ReturnFactory::STATE_CANCEL:
				wc_add_notice( __( 'Payment has been cancelled.', 'woocommerce-wirecard-checkout-seamless' ), 'error' );
				$redirectUrl = $order->get_cancel_endpoint();
				break;

			case WirecardCEE_QMore_ReturnFactory::STATE_FAILURE:
				wc_add_notice( __( 'Payment has failed.', 'woocommerce-wirecard-checkout-seamless' ), 'error' );
				$redirectUrl = $order->get_cancel_endpoint();
				break;
			default:
				break;
		}
		header( 'Location: ' . $redirectUrl );
	}

	/**
	 * Handles thank you text for pending payment
	 *
	 * @since 1.0.0
	 *
	 * @param $var
	 * @param $order
	 *
	 * @return string
	 */
	function thankyou_order_received_text( $var, $order ) {
		if ( $order->get_status() == 'on-hold' ) {
			$var = '<h3>' . __( 'Payment verification is pending',
			                    'woocommerce-wirecard-checkout-seamless' ) . '</h3>' . __(
				       'Your order will be processed as soon as we receive the payment confirmation from your bank.',
				       'woocommerce-wirecard-checkout-seamless'
			       );
		}

		return $var;
	}

	/**
	 * validate input data from payment_fields
	 *
	 * @return boolean
	 */
	function validate_fields() {
		$args = $this->get_post_data();

		$payment_class = 'WC_Gateway_Wirecard_Checkout_Seamless_' . ucfirst( strtolower( str_replace( "-", "_",
		                                                                                              $args['wcs_payment_method'] ) ) );
		$payment_class = new $payment_class( $this->settings );

		if ( method_exists( $payment_class, 'validate_payment_fields' ) ) {
			$validation = $payment_class->validate_payment_fields( $args );
			if ( $validation === true ) {
				return true;
			} else {
				wc_add_notice( $validation, 'error' );

				return;
			}
		}

		return true;
	}

	function datastorage_return() {
		die( require_once 'includes/datastorage_fallback.php' );
	}

	public function wirecard_transaction_do_page() {

		$backend_operations = new WC_Gateway_Wirecard_Checkout_Seamless_Backend_Operations( $this->settings );

		echo "<div class='wrap'>";

		$this->_admin->include_backend_header( $this );


		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] == 'POST' ) {

			if ( ! isset( $_POST['wcs-do-bop'] ) || ! wp_verify_nonce( $_POST['wcs-do-bop'], 'wcs-do-bop' ) ) {
				$this->_logger->error( __METHOD__ . ":ERROR:" . __( "Prevented possible CSRF attack." ) );
				die( 'CSRF Protection prevented you from doing this operation.' );
			}

			$operation = $backend_operations->do_backend_operation(
				( isset( $_POST['paymentNumber'] ) ) ? $_POST['paymentNumber'] : $_POST['creditNumber'],
				$_POST['orderNumber'],
				$_POST['currency'],
				( isset( $_POST['amount'] ) ? round( $_POST['amount'], wc_get_rounding_precision() ) : 0 ),
				$_POST['submitWcsBackendOperation'],
				( isset( $_POST['wcOrder'] ) ) ? $_POST['wcOrder'] : null );

			add_settings_error( '', '', $operation['message'], $operation['type'] );
		}

		settings_errors();


		$id_tx = $_REQUEST['id'];

		$tx = $this->_transaction->get( $id_tx );

		if ( empty ( $id_tx ) ) {
			$this->wirecard_transactions_do_page();
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

	public function wirecard_transactions_do_page() {
		echo "<div class='wrap woocommerce'>";
		$this->_admin->include_backend_header( $this );

		$transaction_start = ! isset( $_GET['transaction_start'] ) ? 1 : $_GET['transaction_start'];
		$this->_admin->print_transaction_table( $this->_transaction, $transaction_start );
		unset( $_GET['transaction_start'] );
		echo "</div>";
	}

	/**
	 * Opens the support request form
	 *
	 * @since 1.0.0
	 */
	function do_support_request() {
		$this->_admin->include_backend_header( $this );
		$this->_admin->print_support_form();
	}

}
