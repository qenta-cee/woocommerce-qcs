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

define( 'WOOCOMMERCE_GATEWAY_WCS_NAME', 'WirecardCheckoutSeamless' );
define( 'WOOCOMMERCE_GATEWAY_WCS_VERSION', '1.0.0' );

/**
 * Class WC_Gateway_Wirecard_Checkout_Seamless
 */
class WC_Gateway_Wirecard_Checkout_Seamless extends WC_Payment_Gateway {

	public function __construct() {

		$this->id           = "woocommerce_wcs";
		$this->method_title = "Wirecard Checkout Seamless";
		$this->has_fields   = true;
		$this->payment_name = '';
		$this->init_form_fields();
		$this->init_settings();

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

		foreach ( $this->get_settings_fields() as $group => $fields ) {
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
	 * Get all or the corresponding settings fields group
	 *
	 * @param string $which
	 *
	 * @since 1.0.0
	 * @return array
	 */
	function get_settings_fields( $which = null ) {
		include "includes/form_fields.php";
		if ( $which !== null ) {
			return $fields[ $which ];
		}

		return $fields;
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
							class="<?php echo esc_attr( $data['class'] ); ?>" type="checkbox"
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
		?>
		<link rel='stylesheet'
		      href='<?= plugins_url( 'woocommerce-wirecard-checkout-seamless/assets/styles/admin.css' ) ?>'>
		<script src='<?= plugins_url( 'woocommerce-wirecard-checkout-seamless/assets/scripts/admin.js' ) ?>'></script>

		<h3><?php echo ( ! empty( $this->method_title ) ) ? $this->method_title : __( 'Settings',
		                                                                              'woocommerce-wirecard-checkout-seamless' ); ?></h3>
		<div class="woo-wcs-settings-header-wrapper">
			<img src="<?= plugins_url( 'woocommerce-wirecard-checkout-seamless/assets/images/wirecard-logo.png' ) ?>">
			<p><?= __( 'Wirecard - Your Full Service Payment Provider - Comprehensive solutions from one single source',
			           'woocommerce-wirecard-checkout-seamless' ) ?></p>

			<p><?= __( 'Wirecard is one of the world´s leading providers of outsourcing and white label solutions for electronic payment transactions.',
			           'woocommerce-wirecard-checkout-seamless' ) ?></p>

			<p><?= __( 'As independent provider of payment solutions, we accompany our customers along the entire business development. Our payment solutions are perfectly tailored to suit e-Commerce requirements and have made	us Austria´s leading payment service provider. Customization, competence, and commitment.',
			           'woocommerce-wirecard-checkout-seamless' ) ?></p>


		</div>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper wcs-tabs">
			<a href="javascript:void(0);" data-target="#basicdata" class="nav-tab nav-tab-active"><?= __( 'Access data',
			                                                                                              'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#options" class="nav-tab "><?= __( 'General settings',
			                                                                              'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#creditcardoptions" class="nav-tab "><?= __( 'Credit card',
			                                                                                        'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#invoiceoptions" class="nav-tab "><?= __( 'Invoice',
			                                                                                     'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#installmentoptions" class="nav-tab "><?= __( 'Installment',
			                                                                                         'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#standardpayments" class="nav-tab "><?= __( 'Standard payments',
			                                                                                       'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#bankingpayments" class="nav-tab "><?= __( 'Banking payments',
			                                                                                      'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#alternativepayments"
			   class="nav-tab "><?= __( 'Alternative payments', 'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#mobilepayments" class="nav-tab "><?= __( 'Mobile payments',
			                                                                                     'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#voucherpayments" class="nav-tab "><?= __( 'Voucher payments',
			                                                                                      'woocommerce-wirecard-checkout-seamless' ) ?></a>
		</nav>
		<div class="tab-content panel">
			<div class="tab-pane active" id="basicdata">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'basicdata' ), false ); ?></table>
			</div>
			<div class="tab-pane" id="options">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'options' ), false ); ?></table>
			</div>
			<div class="tab-pane" id="creditcardoptions">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'creditcardoptions' ),
				                                          false ); ?></table>
			</div>
			<div class="tab-pane" id="invoiceoptions">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'invoiceoptions' ),
				                                          false ); ?></table>
			</div>
			<div class="tab-pane" id="installmentoptions">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'installmentoptions' ),
				                                          false ); ?></table>
			</div>
			<div class="tab-pane" id="standardpayments">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'standardpayments' ),
				                                          false ); ?></table>
			</div>
			<div class="tab-pane" id="bankingpayments">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'bankingpayments' ),
				                                          false ); ?></table>
			</div>
			<div class="tab-pane" id="alternativepayments">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'alternativepayments' ),
				                                          false ); ?></table>
			</div>
			<div class="tab-pane" id="mobilepayments">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'mobilepayments' ),
				                                          false ); ?></table>
			</div>
			<div class="tab-pane" id="voucherpayments">
				<table><?= $this->generate_settings_html( $this->get_settings_fields( 'voucherpayments' ),
				                                          false ); ?></table>
			</div>
		</div>
		<?php
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

		$order = new WC_Order( $order_id );
		// CCARD for Testing
		$payment_type = WirecardCEE_QMore_PaymentType::CCARD;

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
	 * @param $order
	 * @param $payment_type
	 *
	 * @return string
	 * @throws Exception
	 */
	function initiate_payment( $order, $payment_type ) {

		if ( isset( WC()->session->wirecard_checkout_seamless_redirect_url ) && WC()->session->wirecard_checkout_seamless_redirect_url['id'] == $order->id ) {
			return WC()->session->wirecard_checkout_seamless_redirect_url['url'];
		}

		try {
			$client = new WirecardCEE_QMore_FrontendClient(
				array(
					'CUSTOMER_ID' => 'D200001',
					'SHOP_ID'     => 'seamless',
					'SECRET'      => 'B8AKTPWBRMNBV455FG6M2DANE99WU2',
					'LANGUAGE'    => 'en'
				)
			);

			$version = WirecardCEE_QMore_FrontendClient::generatePluginVersion(
				'wordpress_woocommerce',
				WC()->version,
				WOOCOMMERCE_GATEWAY_WCS_NAME,
				WOOCOMMERCE_GATEWAY_WCS_VERSION
			);

			$client->setPluginVersion( $version );

			//TODO: Create orderReference
			$client->setOrderReference( sprintf( 'Configtest #', uniqid() ) );

			$returnUrl = add_query_arg( 'wc-api', 'WC_Gateway_Wirecard_Checkout_Seamless',
			                            site_url( '/', is_ssl() ? 'https' : 'http' ) );

			//TODO: Create consumerData
			$consumerData = new WirecardCEE_Stdlib_ConsumerData();
			$consumerData->setUserAgent( $_SERVER['HTTP_USER_AGENT'] )->setIpAddress( $_SERVER['REMOTE_ADDR'] );

			//TODO: Add specific values
			$client->setAmount( '10' )
			       ->setCurrency( 'EUR' )
			       ->setPaymentType( $payment_type )
			       ->setOrderDescription( 'Configtest #' . uniqid() )
			       ->setSuccessUrl( $returnUrl )
			       ->setPendingUrl( $returnUrl )
			       ->setCancelUrl( $returnUrl )
			       ->setFailureUrl( $returnUrl )
			       ->setConfirmUrl( $returnUrl )
			       ->setServiceUrl( $returnUrl )
			       ->setConsumerData( $consumerData );

			$client->wooOrderId = $order->id;
			$initResponse       = $client->initiate();

			if ( $initResponse->hasFailed() ) {
				wc_add_notice(
					__( "Response failed! Error: {$initResponse->getError()->getMessage()}", 'woocommerce-wcs' ),
					'error'
				);
			}
		} catch ( Exception $e ) {
			throw ( $e );
		}

		WC()->session->wirecard_checkout_seamless_redirect_url = array(
			'id'  => $order->id,
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
		if ( ! $order->id ) {
			$message = "order with id `$order->id` not found";

			return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
		}

		if ( $order->get_status() == "processing" || $order->get_status() == "completed" ) {
			$message = "cannot change the order with id `$order->id`";

			return WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString( $message );
		}

		// Handle paymentdata for order
		$str = '';
		foreach ( $_POST as $k => $v ) {
			$str .= "$k:$v\n";
		}
		$str = trim( $str );

		update_post_meta( $order->id, 'wcs_data', $str );

		$message = null;
		try {
			//TODO: Use specific secret
			$return = WirecardCEE_QMore_ReturnFactory::getInstance( $_POST, 'B8AKTPWBRMNBV455FG6M2DANE99WU2' );
			if ( ! $return->validate() ) {
				$message = __( 'Validation error: invalid response', 'woocommerce-wcs' );
				$order->update_status( 'failed', $message );

				return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString( $message );
			}

			update_post_meta( $order->id, 'wcs_payment_state', $return->getPaymentState() );

			//TODO: Handle specific paymentstate
			switch ( $return->getPaymentState() ) {
				case WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS:
					update_post_meta( $order->id, 'wcs_gateway_reference_number',
					                  $return->getGatewayReferenceNumber() );
					update_post_meta( $order->id, 'wcs_order_number', $return->getOrderNumber() );
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
	 * displays form for e.g. credit card data
	 */
	function payment_fields() {
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
