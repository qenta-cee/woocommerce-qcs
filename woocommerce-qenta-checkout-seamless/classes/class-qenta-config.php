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

define( 'WOOCOMMERCE_GATEWAY_WCS_NAME', 'QentaCheckoutSeamless' );
define( 'WOOCOMMERCE_GATEWAY_WCS_VERSION', '2.0.0' );

/**
 * Config class
 *
 * Handles configuration settings, basketcreation and addressinformation
 *
 * @since 1.0.0
 */
class WC_Gateway_Qenta_Checkout_Seamless_Config {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $_settings;

	/**
	 * Test/Demo configurations
	 *
	 * @since 1.0.0
	 * @access protected
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
	 * WC_Gateway_Qenta_Checkout_Seamless_Config constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 */
	public function __construct( $settings ) {
		$this->_settings = $settings;
	}

	/**
	 * Handles configuration modes and returns config array for FrontendClient
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 *
	 */
	public function get_client_config() {
		$config_mode = $this->_settings['woo_wcs_configuration'];

		if ( array_key_exists( $config_mode, $this->_presets ) ) {
			return Array(
				'CUSTOMER_ID' => $this->_presets[ $config_mode ]['customer_id'],
				'SHOP_ID'     => $this->_presets[ $config_mode ]['shop_id'],
				'SECRET'      => $this->_presets[ $config_mode ]['secret'],
				'LANGUAGE'    => $this->get_language_code(),
			);
		} else {
			return Array(
				'CUSTOMER_ID' => trim( $this->_settings['woo_wcs_customerid'] ),
				'SHOP_ID'     => trim( $this->_settings['woo_wcs_shopid'] ),
				'SECRET'      => trim( $this->_settings['woo_wcs_secret'] ),
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
	public function get_language_code() {
		$locale = get_locale();
		$parts  = explode( '_', $locale );

		return $parts[0];
	}

	/**
	 * Get client secret from config or optionvalue
	 *
	 * @since 1.0.0
	 *
	 * @param $gateway
	 *
	 * @return string
	 */
	public function get_client_secret( $gateway ) {
		$config_mode = $gateway->get_option( 'woo_wcs_configuration' );
		if ( array_key_exists( $config_mode, $this->_presets ) ) {
			return $this->_presets[ $config_mode ]['secret'];
		} else {
			return trim( $gateway->get_option( 'woo_wcs_secret' ) );
		}
	}

	/**
	 * Get client secret from config or optionvalue
	 *
	 * @since 1.0.3
	 *
	 * @param $gateway
	 *
	 * @return string
	 */
	public function get_client_customer_id( $gateway ) {
		$config_mode = $gateway->get_option( 'woo_wcs_configuration' );
		if ( array_key_exists( $config_mode, $this->_presets ) ) {
			return $this->_presets[ $config_mode ]['customer_id'];
		} else {
			return trim( $gateway->get_option( 'woo_wcs_customerid' ) );
		}
	}

	/**
	 * Get client backend password from config or optionvalue
	 *
	 * @since 1.0.0
	 */
	public function get_backend_password() {
		$config_mode = $this->_settings['woo_wcs_configuration'];

		if ( array_key_exists( $config_mode, $this->_presets ) ) {
			return $this->_presets[ $config_mode ]['backendpw'];
		} else {
			return $this->_settings['woo_wcs_backendpassword'];
		}
	}

	/**
	 * Create order description
	 *
	 * @param $order WC_Order
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_order_description( $order ) {
		return sprintf( '%s %s %s', $order->get_billing_email(), $order->get_billing_first_name(),
		                $order->get_billing_last_name() );
	}

	/**
	 * Generate pluginversion
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_plugin_version() {
		return QentaCEE_QMore_FrontendClient::generatePluginVersion(
			'woocommerce',
			WC()->version,
			WOOCOMMERCE_GATEWAY_WCS_NAME,
			WOOCOMMERCE_GATEWAY_WCS_VERSION
		);
	}

	/**
	 * Generate order reference
	 *
	 * @param $order WC_Order
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_order_reference( $order ) {
		return sprintf( '%010s', substr( $order->get_id(), - 10 ) );
	}

	/**
	 * Generate consumer data
	 *
	 * @param $order WC_Order
	 * @param $gateway
	 *
	 * @since 1.0.0
	 * @return QentaCEE_Stdlib_ConsumerData
	 */
	public function get_consumer_data( $order, $gateway, $checkout_data ) {
		$consumerData = new QentaCEE_Stdlib_ConsumerData();

		$consumerData->setIpAddress( $order->get_customer_ip_address() );
		$consumerData->setUserAgent( $_SERVER['HTTP_USER_AGENT'] );

		$user_data = get_userdata( $order->get_user_id() );
		$email = isset( $user_data->user_email ) ? $user_data->user_email : '';

		if ( $gateway->get_option( 'woo_wcs_forwardconsumerbillingdata' ) || $this->force_consumer_data( $checkout_data['wcs_payment_method'] ) ) {
			$billing_address = $this->get_address_data( $order, 'billing' );
			$consumerData->addAddressInformation( $billing_address );

			if (!strlen($email)) {
				$email = $order->get_billing_email();
			}
		}
		if ( $gateway->get_option( 'woo_wcs_forwardconsumershippingdata' ) || $this->force_consumer_data( $checkout_data['wcs_payment_method'] ) ) {
			$shipping_address = $this->get_address_data( $order, 'shipping' );
			$consumerData->addAddressInformation( $shipping_address );
		}
		if ($checkout_data['wcs_payment_method'] == QentaCEE_Stdlib_PaymentTypeAbstract::INVOICE ||
            $checkout_data['wcs_payment_method'] == QentaCEE_Stdlib_PaymentTypeAbstract::INSTALLMENT) {
			$birth_date = new DateTime( $checkout_data['dob_year'] . '-' . $checkout_data['dob_month'] . '-' . $checkout_data['dob_day'] );
			$consumerData->setBirthDate($birth_date);
		}
		$consumerData->setEmail( $email );

		return $consumerData;
	}

    /**
     * Force sending data for invoice and installment
     *
     * @param $payment_method
     *
     * @since 1.0.16
     * @return bool
     */
	public function force_consumer_data( $payment_method ) {
	    switch ( $payment_method ) {
            case QentaCEE_Stdlib_PaymentTypeAbstract::INVOICE:
            case QentaCEE_Stdlib_PaymentTypeAbstract::INSTALLMENT:
                return true;
            default:
                return false;
        }
    }

    /**
     * Force sending basket data for invoice and installment via ratepay
     *
     * @param $payment_method
     * @param $gateway
     *
     * @since 1.0.16
     * @return bool
     */
    public function force_basket_data( $payment_method, $gateway ) {
	    switch ( $payment_method ) {
            case QentaCEE_Stdlib_PaymentTypeAbstract::INVOICE:
                if ( 'payolution' != $gateway->get_option('woo_wcs_invoiceprovider') ) {
                    return true;
                }
                return false;
            case QentaCEE_Stdlib_PaymentTypeAbstract::INSTALLMENT:
                if ( 'payolution' != $gateway->get_option('woo_wcs_installmentprovider') ) {
                    return true;
                }
                return false;
            default:
                return false;
        }
    }

	/**
	 * Generate address data (shipping or billing)
	 *
	 * @param $order
	 * @param string $type
	 *
	 * @since 1.0.0
	 * @return QentaCEE_Stdlib_ConsumerData_Address
	 */
	public function get_address_data( $order, $type = 'billing' ) {
		switch ( $type ) {
			case 'shipping':
				$address = new QentaCEE_Stdlib_ConsumerData_Address( QentaCEE_Stdlib_ConsumerData_Address::TYPE_SHIPPING );
				$address->setFirstname( $order->get_shipping_first_name() )
				        ->setLastname( $order->get_shipping_last_name() )
				        ->setAddress1( $order->get_shipping_address_1() )
				        ->setAddress2( $order->get_shipping_address_2() )
				        ->setCity( $order->get_shipping_city() )
				        ->setState( $order->get_shipping_state() )
				        ->setZipCode( $order->get_shipping_postcode() )
				        ->setCountry( $order->get_shipping_country() );
				break;
			case 'billing':
			default:
				$address = new QentaCEE_Stdlib_ConsumerData_Address( QentaCEE_Stdlib_ConsumerData_Address::TYPE_BILLING );
				$address->setFirstname( $order->get_billing_first_name() )
				        ->setLastname( $order->get_billing_last_name() )
				        ->setAddress1( $order->get_billing_address_1() )
				        ->setAddress2( $order->get_billing_address_2() )
				        ->setCity( $order->get_billing_city() )
				        ->setState( $order->get_billing_state() )
				        ->setZipCode( $order->get_billing_postcode() )
				        ->setCountry( $order->get_billing_country() )
				        ->setPhone( $order->get_billing_phone() );
				break;
		}

		return $address;
	}

	/**
	 * Generate customer statement
	 *
	 * @since 1.0.0
	 *
	 * @param $order
	 * @param $payment_type
	 *
	 * @return string
	 */
	public function get_customer_statement( $order, $payment_type ) {
	    $shop_name = get_bloginfo('name');
        $order_reference = strval( intval( $this->get_order_reference( $order ) ) );

        if ( $payment_type == QentaCEE_QMore_PaymentType::POLI ) {
            return sprintf( '%9s', substr( get_bloginfo( 'name' ), 0, 9 ) );
        }

        $length = strlen( $shop_name . " " . $order_reference );

        if ( $length > 20 ) {
            $shop_name = substr($shop_name, 0, 20 - strlen(" " . $order_reference));
        }

        else if ( $length < 20 ) {
            $order_reference = str_pad($order_reference, (20 - $length) + strlen($order_reference), '0', STR_PAD_LEFT);
        }

        return $shop_name . " " . $order_reference;
	}

	/**
	 * Generate shopping basket
	 *
	 * @since 1.0.0
	 * @return QentaCEE_Stdlib_Basket
	 */
	public function get_shopping_basket( $order_amount = 0 ) {
		global $woocommerce;

		$cart = $woocommerce->cart;

		$basket = new QentaCEE_Stdlib_Basket();

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$article_nr = $cart_item['product_id'];
			if ( $cart_item['data']->get_sku() != '' ) {
				$article_nr = $cart_item['data']->get_sku();
			}

			$attachment_ids = $cart_item['data']->get_gallery_image_ids();
			foreach ( $attachment_ids as $attachment_id ) {
				$image_url = wp_get_attachment_image_url( $attachment_id );
			}

			$item            = new QentaCEE_Stdlib_Basket_Item( $article_nr );
			$item_net_amount = $cart_item['line_total'];
			$item_tax_amount = $cart_item['line_tax'];
			$item_quantity   = $cart_item['quantity'];

			// Calculate amounts per unit
			$item_unit_net_amount   = $item_net_amount / $item_quantity;
			$item_unit_tax_amount   = $item_tax_amount / $item_quantity;
			$item_unit_gross_amount = wc_format_decimal( $item_unit_net_amount + $item_unit_tax_amount, wc_get_price_decimals() );

			$item->setUnitGrossAmount( $item_unit_gross_amount )
			     ->setUnitNetAmount( wc_format_decimal( $item_unit_net_amount, wc_get_price_decimals() ) )
			     ->setUnitTaxAmount( wc_format_decimal( $item_unit_tax_amount, wc_get_price_decimals() ) )
			     ->setUnitTaxRate( number_format( ( $item_unit_tax_amount / $item_unit_net_amount ), 2, '.', '' ) * 100 )
			     ->setDescription( substr( strip_tags( $cart_item['data']->get_short_description() ), 0, 127 ) )
			     ->setName( substr( strip_tags( $cart_item['data']->get_name() ), 0, 127 ) )
			     ->setImageUrl( isset( $image_url ) ? $image_url : '' );

			$basket->addItem( $item, $item_quantity );
		}

		// Add shipping to the basket
		if ( isset( $cart->shipping_total ) && $cart->shipping_total > 0 ) {
			$item = new QentaCEE_Stdlib_Basket_Item( 'shipping' );
			$item->setUnitGrossAmount( wc_format_decimal( $cart->shipping_total + $cart->shipping_tax_total,
			                                              wc_get_price_decimals() ) )
			     ->setUnitNetAmount( wc_format_decimal( $cart->shipping_total, wc_get_price_decimals() ) )
			     ->setUnitTaxAmount( wc_format_decimal( $cart->shipping_tax_total, wc_get_price_decimals() ) )
			     ->setUnitTaxRate( number_format( ( $cart->shipping_tax_total / $cart->shipping_total ), 2, '.', '' ) * 100 )
			     ->setName( 'Shipping' )
			     ->setDescription( 'Shipping' );
			$basket->addItem( $item );
		}

		if ( $order_amount > 0 ) {
            $rounding_difference = $this->get_rounding_difference( $basket, $order_amount );
            if ( $rounding_difference != 0 ) {
                $item = new QentaCEE_Stdlib_Basket_Item( 'rounding' );
                $item->setUnitGrossAmount( wc_format_decimal( $rounding_difference, wc_get_price_decimals() ) )
                    ->setUnitNetAmount( wc_format_decimal( $rounding_difference, wc_get_price_decimals() ) )
                    ->setUnitTaxAmount( 0 )
                    ->setUnitTaxRate( 0 )
                    ->setName( 'Rounding' )
                    ->setDescription( 'Rounding' );
                $basket->addItem( $item );
            }
        }

		return $basket;
	}

    /**
     * Calculate rounding differences
     * @param QentaCEE_Stdlib_Basket $basket
     * @param float $total_amount
     * @return float
     */
	public function get_rounding_difference( QentaCEE_Stdlib_Basket $basket, $total_amount ) {
        $total_amount_rounded = 0;
        $amount_difference    = 0;
        $basket_data          = $basket->getData();
        $basket_items         = $basket_data['basketItems'];

        for ( $count = 1; $count <= $basket_items; $count++ ) {
            $prefix_key = 'basketItem'.$count;
            $total_amount_rounded += $basket_data[$prefix_key . 'unitGrossAmount'] * $basket_data[$prefix_key . 'quantity'];
        }

        if ( $total_amount > $total_amount_rounded || $total_amount_rounded > $total_amount ) {
            $amount_difference = $total_amount - $total_amount_rounded;
        }

        return wc_format_decimal( $amount_difference, wc_get_price_decimals() );
    }
}
