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


define( 'WOOCOMMERCE_GATEWAY_WCS_BASEDIR', plugin_dir_path( __FILE__ ) );
define( 'WOOCOMMERCE_GATEWAY_WCS_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook( __FILE__, 'woocommerce_install_wirecard_checkout_seamless' );

load_plugin_textdomain(
	'woocommerce-wirecard-checkout-seamless', false, dirname( plugin_basename( __FILE__ ) ) . '/languages'
);

add_action( 'plugins_loaded', 'init_woocommerce_wcs_gateway' );

add_action( 'admin_menu', 'wirecard_transactions_add_page' );
add_action( 'admin_menu', 'add_wirecard_support_request_page' );


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
				$method = str_replace( "WC_Gateway_Wirecard_Checkout_Seamless_", "", $class_name );
				require_once( WOOCOMMERCE_GATEWAY_WCS_BASEDIR . 'classes/payment-methods/class-wirecard-' . strtolower( $method ) . ".php" );
			}
		} );

	add_filter( 'woocommerce_payment_gateways', 'add_wirecard_checkout_seamless', 0 );
	add_filter( 'woocommerce_thankyou_order_received_text',
	            array( new WC_Gateway_Wirecard_Checkout_Seamless(), 'thankyou_order_received_text' ), 10, 2 );
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

/**
 * Create transactions table during install process
 *
 * @since 1.0.0
 */
function woocommerce_install_wirecard_checkout_seamless() {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'wirecard_checkout_seamless_tx';

	$collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		$collate = $wpdb->get_charset_collate();
	}

	$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
		id_tx int(10) unsigned NOT NULL auto_increment,
		id_order int(10) NULL ,
		id_cart int(10) unsigned NOT NULL,
		carthash varchar(255),
		order_reference varchar(128) default NULL,
		payment_name varchar(32) default NULL ,
		payment_method varchar(32) NOT NULL ,
		payment_state varchar(32) NOT NULL ,
		gateway_reference varchar(128) default NULL ,
		amount float NOT NULL ,
		currency varchar(3) NOT NULL ,
		message varchar(255) default NULL ,
		request TEXT default NULL,
		response TEXT default NULL,
		created DATETIME NOT NULL default '0000-00-00 00:00:00' ,
		modified DATETIME default NULL,
 		PRIMARY KEY (id_tx)
	)$collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

}

/**
 * add submenu and page for wirecard transactions
 *
 * @since 1.0.0
 */
function wirecard_transactions_add_page() {
	if ( class_exists( 'WooCommerce' ) ) {
		$parent_slug = 'woocommerce';
	} else {
		$parent_slug = 'options-general.php';
	}

	$gateway = new WC_Gateway_Wirecard_Checkout_Seamless();

	// add page to transactions
	add_submenu_page(
		$parent_slug,
		__( 'Wirecard Transactions', 'woocommerce-wirecard-checkout-seamless' ),
		__( 'Wirecard Transactions', 'woocommerce-wirecard-checkout-seamless' ),
		'manage_options',
		'wirecard_transactions_page',
		array( $gateway, 'wirecard_transactions_do_page' )
	);

	// add page to specific transaction
	add_submenu_page(
		null,
		__( 'Wirecard Transaction', 'woocommerce-wirecard-checkout-seamless' ),
		__( 'Wirecard Transaction', 'woocommerce-wirecard-checkout-seamless' ),
		'manage_options',
		'wirecard_transaction_page',
		array( $gateway, 'wirecard_transaction_do_page' )
	);
}

/**
 * Add extra page for wirecard support request
 *
 * @since 1.0.0
 */
function add_wirecard_support_request_page() {
	add_submenu_page(
		null,
		__( 'Wirecard Support Request', 'woocommerce-wirecard-checkout-seamless' ),
		__( 'Wirecard Support Request', 'woocommerce-wirecard-checkout-seamless' ),
		'manage_options',
		'wirecard_support_request',
		array( new WC_Gateway_Wirecard_Checkout_Seamless(), 'do_support_request' )
	);
}
