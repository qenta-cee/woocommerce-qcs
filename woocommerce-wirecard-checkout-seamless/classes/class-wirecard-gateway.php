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

define('WOOCOMMERCE_GATEWAY_WCS_NAME', 'WirecardCheckoutSeamless');
define('WOOCOMMERCE_GATEWAY_WCS_VERSION', '1.0.0');

/**
 * Class WC_Gateway_Wirecard_Checkout_Seamless
 */
class WC_Gateway_Wirecard_Checkout_Seamless extends WC_Payment_Gateway {

	public function __construct() {
		$this->has_fields = true;
		$this->init_form_fields();
		$this->payment_name = '';
		$this->init_settings();

		$this->title = $this->settings['title'];

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
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
		<h3><?php echo ( ! empty( $this->method_title ) ) ? $this->method_title : __( 'Settings',
				'woocommerce-wirecard-checkout-seamless' ); ?></h3>
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

		//TODO: Check for response state and implement behavior according to this

		/* if payment fails add notice
		wc_add_notice( __('Payment error:', 'woothemes') . $error_message, 'error' );
		return; */

		// Create order
		//TODO: Create only for success and pending
		$order = new WC_Order( $order_id );
		//$payment_type = WirecardCEE_QMore_PaymentType::CCARD;
		//$redirect = $this->initiate_payment($order, $payment_type);

		// Update order status
		$order->update_status( 'on-hold', __( 'Awaiting cheque payment', 'woocommerce' ) );
		// Complete order if auto deposit
		// $order->payment_complete();

		//TODO: Add order note for internal comment
		// $order->add_order_note( __('Here should be the response information', 'woothemes') );

		// Reduce stock levels
		$order->reduce_order_stock();

		// Remove cart
		$woocommerce->cart->empty_cart();

		// Return thankyou redirect
		//TODO: Implement redirection for pending, cancel, failure
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order )
		);
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

	/**
	 * validate response from server and edit payment informations
	 */
	function confirm() {
	}
}
