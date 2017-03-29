<?php
/**
 * Plugin Name: Wirecard Checkout Seamless
 * Plugin URI: https://github.com/wirecard/woocommerce-wcs
 * Description: This is a payment plugin
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

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	// if woocommerce not available
	return;
}

/**
 *
 */
define( 'WOOCOMMERCE_GATEWAY_WCS_BASEDIR', plugin_dir_path( __FILE__ ) );
define( 'WOOCOMMERCE_GATEWAY_WCS_URL', plugin_dir_url( __FILE__ ) );

load_plugin_textdomain(
	'woocommerce-wirecard-checkout-seamless', false, dirname( plugin_basename( __FILE__ ) ) . '/languages'
);

register_activation_hook( __FILE__, 'woocommerce_install_wcs_gateway' );

register_uninstall_hook( __FILE__, 'woocommerce_uninstall_wsc_gateway' );

add_action( 'plugins_loaded', 'init_woocommerce_wcs_gateway');


function woocommerce_install_wirecard_checkout_page()
{
	// no custom table is needed, we use update_post_meta()
}


function woocommerce_uninstall_wirecard_checkout_page()
{
}

/**
 * include the Wc_Gateway_WIrecard_Checkout_Seamless.php
 */
function init_woocommerce_wcs_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'Wc_Gateway_Wirecard_Checkout_Seamless.php' );

	add_filter( 'woocommerce_payment_gateways', 'add_wirecard_checkout_seamless', 0);
}

/**
 * this method allows our plugin to be recognized as a payment gateway
 *
 * @param $methods
 *
 * @return array
 */
function add_wirecard_checkout_seamless( $methods ) {
	$methods[] = 'Wc_Gateway_Wirecard_Checkout_Seamless';

	return $methods;
}