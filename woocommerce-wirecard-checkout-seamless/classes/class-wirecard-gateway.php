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
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-wirecard-transaction.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/payment-methods/class-wirecard-creditcard.php' );
require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/payment-methods/class-wirecard-paypal.php' );

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
		//TODO: remove woocommerce_wcs from payment method, for testing it is enabled
		$this->enabled = "yes";
		$this->_logger = new WC_Logger();

		$this->_admin       = new WC_Gateway_Wirecard_Checkout_Seamless_Admin();
		$this->_config      = new WC_Gateway_Wirecard_Checkout_Seamless_Config();
		$this->_transaction = new WC_Gateway_Wirecard_Checkout_Seamless_Transaction();

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
			'woocommerce_thankyou_' . $this->id,
			array(
				$this,
				'order_received_text'
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
		if ( isset( $_GET['transaction_start'] ) ) {
			$this->_admin->print_transaction_table( $this->_transaction, $_GET['transaction_start']);
			unset( $_GET['transaction_start'] );
		} else {
			$this->_admin->print_admin_form_fields( $this );
		}

	}

	/**
	 * Generate frontend payment fields
	 * INFO: Only for temporary testing
	 *
	 * @since 1.0.0
	 */
	function payment_fields() {
		global $woocommerce;

		/**
		 * TODO: - Implement method specific fields
		 *       - Remove woocommerce_wcs payment
		 *       - Implement labels (method title)
		 * */
		?>
		<input id="wcs_payment_method_changer" type="hidden" value="woocommerce_wcs" name="wcs_payment_method"/>
		<script type="text/javascript">
			function changeWCSPayment(code) {
				var changer = document.getElementById('wcs_payment_method_changer'),
					form_fields = document.getElementsByClassName('payment_box');
				changer.value = code;
			}
		</script>
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
				<?php echo $type->get_label() ?>
				<img src="<?= $type->get_icon() ?>" alt="Wirecard <?= $type->get_payment_type() ?>">
			</label>
		<div class="payment_box payment_method_<?php echo $type->get_payment_type() ?>" style="display:none;">
			<?php
		}
	}


	/**
	 * Array of enabled payment types
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_enabled_payment_types() {
		$types = array();
		foreach ( $this->settings as $k => $v ) {
			if ( strpos( $k, 'enable' ) !== false ) {
				if ( $v == 1 ) {
					$code = str_replace( '_enable', '', $k );
					$code = str_replace( 'wcs_', '', $code );
					//TODO: get name via language file
					$class = 'WC_Gateway_Wirecard_Checkout_Seamless_' . ucfirst( strtolower( $code ) );
					$type  = new $class( $this->settings );

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
	 * Handles payment and processes the order
	 *
	 * @since 1.0.0
	 *
	 * @param $order_id
	 *
	 * @return array
	 */
	function process_payment( $order_id ) {
		global $woocommerce;

		$order = wc_get_order( $order_id );

		//TODO: Errorhandling for payment type
		$payment_type = $order->get_payment_method_title();

		// Payment type for initialization
		if ( isset( $_POST['wcs_payment_method'] ) ) {
			$payment_type = $_POST['wcs_payment_method'];
		}

		$redirect = $this->initiate_payment( $order, $payment_type );

		if ( ! $redirect ) {
			return;
		}

		return array(
			'result'   => 'success',
			'redirect' => $redirect
		);
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
			$config_array = $this->_config->get_client_config( $this );
			$client       = new WirecardCEE_QMore_FrontendClient( $config_array );


			$return_url = add_query_arg( 'wc-api', 'WC_Gateway_Wirecard_Checkout_Seamless',
			                             site_url( '/', is_ssl() ? 'https' : 'http' ) );

			$consumer_data = $this->_config->get_consumer_data( $order, $this );
			$auto_deposit  = $this->get_option( 'woo_wcs_automateddeposit' );
			$service_url   = $this->get_option( 'woo_wcs_serviceurl' );

			// Check if service url is valid
			if ( filter_var( $service_url, FILTER_VALIDATE_URL ) === false ) {
				wc_add_notice( __( "Service URL is invalid", 'woocommerce-wcs' ), 'error' );

				return;
			}

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

			$client->wooOrderId = $order->get_id();

			$initResponse = $client->initiate();

			if ( $initResponse->hasFailed() ) {
				$this->_logger->error( __METHOD__ . ': Initialization response failed!' );
				wc_add_notice(
					__( "Response failed! Error: {$initResponse->getError()->getMessage()}", 'woocommerce-wcs' ),
					'error'
				);
				$this->_transaction->update( array( 'payment_state' => 'INITIATED', 'message' => 'error' ),
				                             array( 'id_order' => $order->get_id() ) );
			} else {

				$this->_transaction->update( array( 'payment_state' => 'INITIATED', 'message' => 'ok' ),
				                             array( 'id_order' => $order->get_id() ) );
			}
		} catch ( Exception $e ) {
			$this->_logger->error( __METHOD__ . ': ' . $e->getMessage() );
			$this->_transaction->update( array( 'payment_state' => 'INITIATED', 'message' => 'error' ),
			                             array( 'id_order' => $order->get_id() ) );
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
		if ( ! isset( $_REQUEST['wooOrderId'] ) || ! strlen( $_REQUEST['wooOrderId'] ) ) {
			$message = 'order-id missing';
			$this->_logger->error( __METHOD__ . ':' . $message );

			print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
		}

		$order_id = $_REQUEST['wooOrderId'];
		$order    = new WC_Order( $order_id );

		if ( ! $order->get_id() ) {
			$message = "order with id `$order->get_id()` not found";
			$this->_logger->error( __METHOD__ . ':' . $message );

			print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
		}

		if ( $order->get_status() == "processing" || $order->get_status() == "completed" ) {
			$message = "cannot change the order with id `$order->get_id()`";
			$this->_logger->error( __METHOD__ . ':' . $message );

			print WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString( $message );
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
				$message = __( 'Validation error: invalid response', 'woocommerce-wcs' );
				$this->_logger->error( __METHOD__ . ':' . $message );
				$order->update_status( 'failed', $message );

				print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
			}

			$this->_logger->notice( __METHOD__ . ':' . print_r( $return, true ) );

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
					$order->payment_complete();
					break;
				case WirecardCEE_QMore_ReturnFactory::STATE_PENDING:
					$order->update_status(
						'on-hold',
						__( 'Awaiting payment notification from 3rd party.', 'woocommerce-wcs' )
					);
					break;

				case WirecardCEE_QMore_ReturnFactory::STATE_CANCEL:
					$order->update_status( 'cancelled', __( 'Payment cancelled.', 'woocommerce-wcs' ) );
					break;

				case WirecardCEE_QMore_ReturnFactory::STATE_FAILURE:
					$order->update_status(
						'failed',
						$return->getErrors()
						       ->getConsumerMessage()
					);
					break;

				default:
					break;
			}
		} catch ( Exception $e ) {
			$order->update_status( 'failed', $e->getMessage() );
			$message = $e->getMessage();
			$this->_logger->error( __METHOD__ . ':' . $message );
		}

		print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
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
			wc_add_notice( __( 'Order-Id missing', 'woocommerce-wcs' ), 'error' );
			$this->_logger->notice( __METHOD__ . ': Order-Id missing' );

			header( 'Location: ' . $redirectUrl );
		}
		$order_id = $_REQUEST['order-id'];
		$order    = new WC_Order( $order_id );

		switch ( $_REQUEST['paymentState'] ) {
			case WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS:
			case WirecardCEE_QMore_ReturnFactory::STATE_PENDING:
				$redirectUrl = $this->get_return_url( $order );
				break;

			case WirecardCEE_QMore_ReturnFactory::STATE_CANCEL:
				wc_add_notice( __( 'Payment has been cancelled.', 'woocommerce-wcs' ), 'error' );
				$redirectUrl = $order->get_cancel_endpoint();
				break;

			case WirecardCEE_QMore_ReturnFactory::STATE_FAILURE:
				wc_add_notice( __( 'Payment has failed.', 'woocommerce-wcs' ), 'error' );
				$redirectUrl = $order->get_cancel_endpoint();

			default:
				break;
		}
		header( 'Location: ' . $redirectUrl );
	}

	/**
	 * Handles extra text for pending payment
	 *
	 * @since 1.0.0
	 *
	 * @param $order_id
	 */
	function order_received_text( $order_id ) {
		$order = new WC_Order( $order_id );
		if ( $order->get_status() == 'on-hold' ) {
			printf(
				'<p>%s</p>',
				__(
					'Your order will be processed as soon as we receive the payment confirmation from your bank.',
					'woocommerce-wcs'
				)
			);
		}
	}

	/**
	 * validate input data from payment_fields
	 *
	 * @return boolean
	 */
	function validate_fields() {
		// call wd_add_notice('text'); if you want to show an error message to user

		// return true if form validation ok
		// return false if validation fails
	}

}
