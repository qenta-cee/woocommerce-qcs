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


		$this->id = "woocommerce_wcs";
		$this->method_title = "Wirecard Checkout Seamless";


		$this->has_fields = true;
		$this->init_form_fields();
		$this->payment_name = '';
		$this->init_settings();

		$this->title = $this->settings['title'];

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
	 * Initialize Gateway Settings Form Fields
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function init_form_fields() {
		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-wirecard-checkout-seamless' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Wirecard Checkout Seamless ' . $this->payment_name,
					'woocommerce-wirecard-checkout-seamless' ),
				'default' => 'yes'
			),
			'title'      => array(
				'title'       => __( 'Title', 'woocommerce-wirecard-checkout-seamless' ),
				'type'        => 'text',
				'description' => __( 'This controls the titlte which the user sees during checkout',
					'woocommerce-wirecard-checkout-seamless' ),
				'default'     => __( 'Wirecard Checkout Seamless ' . $this->payment_name,
					'woocommerce-wirecard-checkout-seamless' ),
				'desc_tip'    => true
			),
			'customerId' => array(
				'title'   => __( 'CustomerId', 'woocommerce-wirecard-checkout-seamless' ),
				'type'    => 'text',
				'default' => 'D200001'
			)
			//TODO: Add all desired setting fields
		);
	}

	/**
	 * Admin Panel Options.
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
		?>
		<style>
			nav.wcs-tabs > a, nav.wcs-tabs > a:hover, nav.wcs-tabs > a:active, nav.wcs-tabs > a:focus {
				background-color: #fff;
			}

			nav.wcs-tabs > a.nav-tab-active {
				border-bottom: 1px solid #fff;
			}

			.tab-content.panel {
				margin-top:-13px;
				border-radius: 0 0 3px 3px;
				border: 1px solid #ccc;
				border-top: none;
				background-color: #fff;
				padding: 20px 20px;
			}

			.tab-content.panel > .tab-pane {
				 display:none;
			 }

			.tab-content.panel > .tab-pane.active {
				display:block;
			}
		</style>

        <script>
            wpOnload = function () {
                var tabs = document.querySelectorAll("nav > a[data-target]");
                var removeClass = function (el, cls) {
                    var reg = new RegExp('(\\s|^)' + cls + '(\\s|$)');
                    if (el.className.match(reg)) {
                        el.className = el.className.replace(reg, ' ');
                    }
                };
                for (var i = 0; i < tabs.length; i++) {
                    tabs[i].addEventListener('click', function () {
                        removeClass(document.querySelector("nav > a[data-target].nav-tab-active"),'nav-tab-active');
                        removeClass(document.querySelector(".tab-content.panel > .tab-pane.active"), 'active');
                        this.className = this.className + ' nav-tab-active';
                        var tabPane = document.querySelector(".tab-content.panel > .tab-pane" + this.getAttribute('data-target'));
                        tabPane.className = tabPane.className + ' active';
                    });
                }
            }
        </script>
		<h3><?php echo ( ! empty( $this->method_title ) ) ? $this->method_title : __( 'Settings',
																					  'woocommerce-wirecard-checkout-seamless' ); ?></h3>

		<nav class="nav-tab-wrapper woo-nav-tab-wrapper wcs-tabs">
			<a href="javascript:void(0);" data-target="#basicdata" class="nav-tab nav-tab-active"><?=__('Access data', 'woocommerce-wirecard-checkout-seamless')?></a>
			<a href="javascript:void(0);" data-target="#options" class="nav-tab "><?=__('General settings', 'woocommerce-wirecard-checkout-seamless')?></a>
			<a href="javascript:void(0);" data-target="#creditcardoptions" class="nav-tab "><?=__('Credit card', 'woocommerce-wirecard-checkout-seamless')?></a>
			<a href="javascript:void(0);" data-target="#invoiceoptions" class="nav-tab "><?=__('Invoice', 'woocommerce-wirecard-checkout-seamless')?></a>
			<a href="javascript:void(0);" data-target="#installmentoptions" class="nav-tab "><?=__('Installment', 'woocommerce-wirecard-checkout-seamless')?></a>
			<a href="javascript:void(0);" data-target="#standardpayments" class="nav-tab "><?=__('Standard payments', 'woocommerce-wirecard-checkout-seamless')?></a>
			<a href="javascript:void(0);" data-target="#bankingpayments" class="nav-tab "><?=__('Banking payments', 'woocommerce-wirecard-checkout-seamless')?></a>
			<a href="javascript:void(0);" data-target="#alternativepayments" class="nav-tab "><?=__('Alternative payments', 'woocommerce-wirecard-checkout-seamless')?></a>
			<a href="javascript:void(0);" data-target="#mobilepayments" class="nav-tab "><?=__('Mobile payments', 'woocommerce-wirecard-checkout-seamless')?></a>
			<a href="javascript:void(0);" data-target="#voucherpayments" class="nav-tab "><?=__('Voucher payments', 'woocommerce-wirecard-checkout-seamless')?></a>
		</nav>
		<div class="tab-content panel">
			<div class="tab-pane active" id="basicdata"></div>
			<div class="tab-pane" id="options"></div>
			<div class="tab-pane" id="creditcardoptions"></div>
			<div class="tab-pane" id="invoiceoptions"></div>
			<div class="tab-pane" id="installmentoptions"></div>
			<div class="tab-pane" id="standardpayments"></div>
			<div class="tab-pane" id="bankingpayments"></div>
			<div class="tab-pane" id="alternativepayments"></div>
			<div class="tab-pane" id="mobilepayments"></div>
			<div class="tab-pane" id="voucherpayments"></div>
		</div>

		<?php echo ( ! empty( $this->method_description ) ) ? wpautop( $this->method_description ) : ''; ?>
		<table class="form-table">
			<?php $this->generate_settings_html(); // Generate the HTML For the settings form. ?>
		</table>
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

		WC()->session->wirecard_checkout_seamless_redirect_url = array( 'id'  => $order->id,
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
