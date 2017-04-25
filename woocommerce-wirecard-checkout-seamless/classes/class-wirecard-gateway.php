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

/**
 * Class WC_Gateway_Wirecard_Checkout_Seamless
 */
class WC_Gateway_Wirecard_Checkout_Seamless extends WC_Payment_Gateway {

	protected $_admin;
	protected $_config;

	public function __construct() {

		$this->id           = "woocommerce_wcs";
		$this->method_title = "Wirecard Checkout Seamless";
		$this->has_fields   = true;
		$this->payment_name = '';
		$this->init_form_fields();
		$this->init_settings();
		//TODO: remove woocommerce_wcs from payment method, for testing it is enabled
		$this->enabled = "yes";

		$this->_admin  = new WC_Gateway_Wirecard_Checkout_Seamless_Admin();
		$this->_config = new WC_Gateway_Wirecard_Checkout_Seamless_Config( $this->settings );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );

		// Payment listener/API hook
		add_action(
			'woocommerce_api_wc_gateway_wirecard_checkout_seamless',
			array(
				$this,
				'dispatch_callback'
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
		$dataStorage->init();
		/**
		 * TODO: - Implement method specific fields
		 *       - Remove woocommerce_wcs payment
		 *       - Implement labels (method title)
		 * */
		?>
		<input id="wcs_payment_method_changer" type="hidden" value="woocommerce_wcs" name="wcs_payment_method"/>
		<script type="text/javascript">
			function changeWCSPayment(code) {
				var changer = document.getElementById('wcs_payment_method_changer');
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
			echo $type->has_payment_fields() ? $type->get_payment_fields() : null;
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
					$code  = str_replace( '_enable', '', $k );
					$code  = str_replace( 'wcs_', '', $code );
					$class = 'WC_Gateway_Wirecard_Checkout_Seamless_' . ucfirst( strtolower( str_replace( "-", "_",
					                                                                                      $code ) ) );
					$type  = new $class( $this->settings );

					if ( method_exists( $type, 'get_risk' ) ) {
						$riskvalue = $type->get_risk();
						if ( ! $riskvalue ) {
							continue;
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
			$config_array = $this->_config->get_client_config();
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

			$client->setPluginVersion( $this->_config->get_plugin_version() );
			$client->setOrderReference( $this->_config->get_order_reference( $order ) );

			$client->setAmount( $order->get_total() )
			       ->setCurrency( get_woocommerce_currency() )
			       ->setPaymentType( $payment_type )
			       ->setOrderDescription( $this->_config->get_order_description( $order ) )
			       ->setSuccessUrl( $return_url )
			       ->setPendingUrl( $return_url )
			       ->setCancelUrl( $return_url )
			       ->setFailureUrl( $return_url )
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
				foreach ( $initResponse->getErrors() as $error ) {
					wc_add_notice( __( "Response failed! Error: {$error->getConsumerMessage()}", 'woocommerce-wcs' ),
					               'error' );
				}
			}
		} catch ( Exception $e ) {
			throw ( $e );
		}

		WC()->session->wirecard_checkout_seamless_redirect_url = array(
			'id'  => $order->get_id(),
			'url' => $initResponse->getRedirectUrl()
		);

		return $initResponse->getRedirectUrl();
	}

	function dispatch_callback() {
		if ( isset( WC()->session->chosen_payment_method ) ) {
			$redirectUrl = $this->get_return_url();
			header( 'Location: ' . $redirectUrl );
		} else {
			print $this->confirm();
		}
		die();
	}

	/**
	 * Handle return URL
	 *
	 * @since 1.0.0
	 *
	 * @param null $order
	 *
	 * @return mixed|void
	 */
	public function get_return_url( $order = null ) {
		if ( $order ) {
			$return_url = $order->get_checkout_order_received_url();
		} else {
			$return_url = wc_get_endpoint_url( 'order-received', '', wc_get_page_permalink( 'checkout' ) );
		}

		if ( is_ssl() || get_option( 'woocommerce_force_ssl_checkout' ) == 'yes' ) {
			$return_url = str_replace( 'http:', 'https:', $return_url );
		}

		return apply_filters( 'woocommerce_get_return_url', $return_url, $order );
	}

	/**
	 * Validate response from server and edit payment informations
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function confirm() {
		if ( ! isset( $_REQUEST['wooOrderId'] ) || ! strlen( $_REQUEST['wooOrderId'] ) ) {
			$message = 'order-id missing';

			return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
		}
		$order_id = $_REQUEST['wooOrderId'];
		$order    = new WC_Order( $order_id );
		if ( ! $order->get_id() ) {
			$message = "order with id `$order->get_id()` not found";

			return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
		}

		if ( $order->get_status() == "processing" || $order->get_status() == "completed" ) {
			$message = "cannot change the order with id `$order->get_id()`";

			return WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString( $message );
		}

		// Handle paymentdata for order
		$str = '';
		foreach ( $_POST as $k => $v ) {
			$str .= "$k:$v\n";
		}
		$str = trim( $str );

		update_post_meta( $order->get_id(), 'wcs_data', $str );

		$message = null;
		try {
			//TODO: Use specific secret
			$return = WirecardCEE_QMore_ReturnFactory::getInstance( $_POST, 'B8AKTPWBRMNBV455FG6M2DANE99WU2' );
			if ( ! $return->validate() ) {
				$message = __( 'Validation error: invalid response', 'woocommerce-wcs' );
				$order->update_status( 'failed', $message );

				return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
			}

			update_post_meta( $order->get_id(), 'wcs_payment_state', $return->getPaymentState() );

			//TODO: Handle specific paymentstate
			switch ( $return->getPaymentState() ) {
				case WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS:
					update_post_meta( $order->get_id(), 'wcs_gateway_reference_number',
					                  $return->getGatewayReferenceNumber() );
					update_post_meta( $order->get_id(), 'wcs_order_number', $return->getOrderNumber() );
					$order->payment_complete();
					break;
				default:
					break;
			}
		} catch ( Exception $e ) {
			$order->update_status( 'failed', $e->getMessage() );
			$message = $e->getMessage();
		}

		return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
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

}
