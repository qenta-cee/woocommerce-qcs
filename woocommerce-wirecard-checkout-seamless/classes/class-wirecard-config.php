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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Config
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Config {

	/**
	 * Test/Demo configurations
	 *
	 * @var array
	 */
	protected $_presets = array(
		'demo'   => array(
			'customer_id' => 'D200001',
			'shop_id'     => 'seamless',
			'secret'      => 'B8AKTPWBRMNBV455FG6M2DANE99WU2',
			'backendpw'   => 'jcv45z'
		),
		'test'   => array(
			'customer_id' => 'D200411',
			'shop_id'     => 'seamless',
			'secret'      => 'CHCSH7UGHVVX2P7EHDHSY4T2S4CGYK4QBE4M5YUUG2ND5BEZWNRZW5EJYVJQ',
			'backendpw'   => '2g4f9q2m'
		),
		'test3d' => array(
			'customer_id' => 'D200411',
			'shop_id'     => 'seamless3D',
			'secret'      => 'DP4TMTPQQWFJW34647RM798E9A5X7E8ATP462Z4VGZK53YEJ3JWXS98B9P4F',
			'backendpw'   => '2g4f9q2m'
		)
	);

	/**
	 * Handles configuration modi and returns config array for FrontendClient
	 *
	 * @param $gateway
	 *
	 * @since 1.0.0
	 * @return array
	 */
	function get_client_config( $gateway ) {
		$config_mode = $gateway->get_option( 'woo_wcs_configuration' );

		if ( array_key_exists( $config_mode, $this->_presets ) ) {
			return Array(
				'CUSTOMER_ID' => $this->_presets[ $config_mode ]['customer_id'],
				'SHOP_ID'     => $this->_presets[ $config_mode ]['shop_id'],
				'SECRET'      => $this->_presets[ $config_mode ]['secret'],
				'LANGUAGE'    => $this->get_language_code(),
			);
		} else {
			return Array(
				'CUSTOMER_ID' => trim( $gateway->get_option( 'woo_wcs_customerid' ) ),
				'SHOP_ID'     => trim( $gateway->get_option( 'woo_wcs_shopid' ) ),
				'SECRET'      => trim( $gateway->get_option( 'woo_wcs_secret' ) ),
				'LANGUAGE'    => $this->get_language_code(),
			);
		}
	}

	/**
	 * Extract language code from locale settings
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	function get_language_code() {
		$locale = get_locale();
		$parts  = explode( '_', $locale );

		return $parts[0];
	}

	/**
	 * Create order description
	 *
	 * @param $order
	 *
	 * @since 1.0.0
	 * @return string
	 */
	function get_order_description( $order ) {
		return sprintf( '%s %s %s', $order->billing_email, $order->billing_first_name, $order->billing_last_name );
	}

	/**
	 * Generate pluginversion
	 *
	 * @since 1.0.0
	 * @return string
	 */
	function get_plugin_version() {
		return WirecardCEE_QMore_FrontendClient::generatePluginVersion(
			'woocommerce',
			WC()->version,
			WOOCOMMERCE_GATEWAY_WCS_NAME,
			WOOCOMMERCE_GATEWAY_WCS_VERSION
		);
	}

	/**
	 * Generate order reference
	 *
	 * @param $order
	 *
	 * @since 1.0.0
	 * @return string
	 */
	function get_order_reference( $order ) {
		return sprintf( '%010d', $order->get_id() );
	}

	/**
	 * Generate consumer data
	 *
	 * @param $order
	 * @param $gateway
	 *
	 * @since 1.0.0
	 * @return WirecardCEE_Stdlib_ConsumerData
	 */
	function get_consumer_data( $order, $gateway ) {
		$consumerData = new WirecardCEE_Stdlib_ConsumerData();

		$consumerData->setIpAddress( $order->get_customer_ip_address() );
		$consumerData->setUserAgent( $_SERVER['HTTP_USER_AGENT'] );

		$user_data = get_userdata( $order->user_id );
		$consumerData->setEmail( isset( $user_data->user_email ) ? $user_data->user_email : '' );

		//TODO: check for birthday
		//$consumerData->setBirthDate(Date);
		//TODO: check for company info

		if ( $gateway->get_option( 'woo_wcs_forwardconsumerbillingdata' ) ) {
			$billing_address = $this->get_address_data( $order, 'billing' );
			$consumerData->addAddressInformation( $billing_address );
		}
		if ( $gateway->get_option( 'woo_wcs_forwardconsumershippingdata' ) ) {
			$shipping_address = $this->get_address_data( $order, 'shipping' );
			$consumerData->addAddressInformation( $shipping_address );
		}

		return $consumerData;
	}

	/**
	 * Generate address data (shipping or billing)
	 *
	 * @param $order
	 * @param string $type
	 *
	 * @since 1.0.0
	 * @return WirecardCEE_Stdlib_ConsumerData_Address
	 */
	function get_address_data( $order, $type = 'billing' ) {
		switch ( $type ) {
			case 'shipping':
				$address = new WirecardCEE_Stdlib_ConsumerData_Address( WirecardCEE_Stdlib_ConsumerData_Address::TYPE_SHIPPING );
				$address->setFirstname( $order->shipping_first_name )
				        ->setLastname( $order->shipping_last_name )
				        ->setAddress1( $order->shipping_address_1 )
				        ->setAddress2( $order->shipping_address_2 )
				        ->setCity( $order->shipping_city )
				        ->setState( $order->shipping_state )
				        ->setZipCode( $order->shipping_postcode )
				        ->setCountry( $order->shipping_country );
				break;
			case 'billing':
			default:
				$address = new WirecardCEE_Stdlib_ConsumerData_Address( WirecardCEE_Stdlib_ConsumerData_Address::TYPE_BILLING );
				$address->setFirstname( $order->billing_first_name )
				        ->setLastname( $order->billing_last_name )
				        ->setAddress1( $order->billing_address_1 )
				        ->setAddress2( $order->billing_address_2 )
				        ->setCity( $order->billing_city )
				        ->setState( $order->billing_state )
				        ->setZipCode( $order->billing_postcode )
				        ->setCountry( $order->billing_country )
				        ->setPhone( $order->billing_phone );
				break;
		}

		return $address;
	}

	/**
	 * Generate customer statement
	 *
	 * @param $client
	 * @param $gateway
	 *
	 * @since 1.0.0
	 */
	function set_customer_statement( $client, $gateway ) {
		$prefix = $gateway->get_option( 'woo_wcs_shopreferenceinpostingcontext' );
		if ( ! isset( $prefix ) ) {
			$prefix = null;
		}
		$client->generateCustomerStatement( $prefix, get_bloginfo( 'name' ) );
	}

	/**
	 * Generate shopping basket
	 *
	 * @since 1.0.0
	 * @return WirecardCEE_Stdlib_Basket
	 */
	function get_shopping_basket() {
		global $woocommerce;

		$cart = $woocommerce->cart;

		$basket = new WirecardCEE_Stdlib_Basket();

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$article_nr = $cart_item['product_id'];
			if ( $cart_item['data']->get_sku() != '' ) {
				$article_nr = $cart_item['data']->get_sku();
			}

			$attachment_ids = $cart_item['data']->get_gallery_image_ids();
			foreach ( $attachment_ids as $attachment_id ) {
				$image_url = wp_get_attachment_image_url( $attachment_id );
			}

			$item            = new WirecardCEE_Stdlib_Basket_Item( $article_nr );
			$item_net_amount = $cart_item['line_total'];
			$item_tax_amount = $cart_item['line_tax'];
			$item_quantity   = $cart_item['quantity'];

			// Calculate amounts per unit
			$item_unit_net_amount   = $item_net_amount / $item_quantity;
			$item_unit_tax_amount   = $item_tax_amount / $item_quantity;
			$item_unit_gross_amount = wc_format_decimal( $item_unit_net_amount + $item_unit_tax_amount,
				wc_get_price_decimals() );

			$item->setUnitGrossAmount( $item_unit_gross_amount )
			     ->setUnitNetAmount( wc_format_decimal( $item_unit_net_amount, wc_get_price_decimals() ) )
			     ->setUnitTaxAmount( wc_format_decimal( $item_unit_tax_amount, wc_get_price_decimals() ) )
			     ->setUnitTaxRate( number_format( ( $item_unit_tax_amount / $item_unit_net_amount ), 2, '.', '' ) )
			     ->setDescription( substr( strip_tags( $cart_item['data']->get_short_description() ), 0, 127 ) )
			     ->setName( substr( strip_tags( $cart_item['data']->get_name() ), 0, 127 ) )
			     ->setImageUrl( isset( $image_url ) ? $image_url : '' );

			$basket->addItem( $item, $item_quantity );
		}

		return $basket;
	}
}
