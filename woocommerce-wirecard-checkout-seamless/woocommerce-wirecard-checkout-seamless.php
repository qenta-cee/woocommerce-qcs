<?php
/**
 * Plugin Name: Wirecard Checkout Seamless
 * Plugin URI: https://github.com/wirecard/woocommerce-wcs
 * Description: Wirecard Checkout Seamless plugin for WooCommerce
 * Version: 1.0.0
 * Author: Wirecard
 * Author URI: https://www.wirecard.at/
 * License: GPL2
 */

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

if ( ! defined( 'ABSPATH' ) ) {
	// if accessed directly
	exit;
}

/**
 *
 */
define( 'WOOCOMMERCE_GATEWAY_WCS_BASEDIR', plugin_dir_path( __FILE__ ) );
define( 'WOOCOMMERCE_GATEWAY_WCS_URL', plugin_dir_url( __FILE__ ) );

load_plugin_textdomain(
	'woocommerce-wirecard-checkout-seamless', false, dirname( plugin_basename( __FILE__ ) ) . '/languages'
);

add_action( 'plugins_loaded', 'init_woocommerce_wcs_gateway' );

/**
 * Intialize the Wirecard payment gateway
 *
 * Intialization only possible if WooCommerce base gateway exists
 *
 * @since 1.0.0
 */
function init_woocommerce_wcs_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/class-wirecard-gateway.php' );
	require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'vendor/autoload.php' );

	spl_autoload_register(
		function ( $class_name ) {
			if ( strpos( $class_name, "Wirecard_Checkout_Seamless" ) ) {
				$method = str_replace("WC_Gateway_Wirecard_Checkout_Seamless_", "", $class_name );
				require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/payment-methods/class-wirecard-' . $method . ".php" );
			}
		} );

	add_filter( 'woocommerce_payment_gateways', 'add_wirecard_checkout_seamless', 0 );
}

/**
 * Define possible Wirecard gateways for WooCommerce
 *
 * @since 1.0.0
 *
 * @param $methods
 *
 * @return array
 */
function add_wirecard_checkout_seamless( $methods ) {
	$methods[] = 'WC_Gateway_Wirecard_Checkout_Seamless';

	return $methods;
}
