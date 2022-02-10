<?php
/**
 * Plugin Name: Qenta Checkout Seamless
 * Plugin URI: https://github.com/qenta-cee/woocommerce-qcs
 * Description: Qenta Checkout Seamless plugin for WooCommerce
 * Version: 2.0.5.1
 * Author: Qenta Payment CEE GmbH
 * Author URI: https://www.qenta.com/
 * License: GPL2
 */

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
if ( ! defined( 'ABSPATH' ) ) {
	// if accessed directly
	exit;
}


define( 'WOOCOMMERCE_GATEWAY_QMORE_BASEDIR', plugin_dir_path( __FILE__ ) );
define( 'WOOCOMMERCE_GATEWAY_QMORE_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook( __FILE__, 'woocommerce_install_qenta_checkout_seamless' );

load_plugin_textdomain(
	'woocommerce-qenta-checkout-seamless', false, dirname( plugin_basename( __FILE__ ) ) . '/languages'
);

add_action( 'plugins_loaded', 'init_woocommerce_wcs_gateway' );

add_action( 'admin_menu', 'qenta_transactions_add_page' );
add_action( 'admin_menu', 'add_qenta_support_request_page' );
add_action( 'wp_footer', 'add_qenta_storage_check' );

/**
 * Intialize the Qenta payment gateway
 *
 * Intialization only possible if WooCommerce base gateway exists
 *
 * @since 1.0.0
 */
function init_woocommerce_wcs_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once( WOOCOMMERCE_GATEWAY_QMORE_BASEDIR . 'classes/class-qenta-gateway.php' );
	require_once( WOOCOMMERCE_GATEWAY_QMORE_BASEDIR . 'vendor/autoload.php' );

	spl_autoload_register(
		function ( $class_name ) {
			if ( strpos( $class_name, "Qenta_Checkout_Seamless" ) ) {
				$method = str_replace( "WC_Gateway_Qenta_Checkout_Seamless_", "", $class_name );
				if ( file_exists( WOOCOMMERCE_GATEWAY_QMORE_BASEDIR . 'classes/payment-methods/class-qenta-' . strtolower( $method ) . ".php" ) ) {
					require_once( WOOCOMMERCE_GATEWAY_QMORE_BASEDIR . 'classes/payment-methods/class-qenta-' . strtolower( $method ) . ".php" );
				}
			}
		} );

	add_filter( 'woocommerce_payment_gateways', 'add_qenta_checkout_seamless', 0 );
	add_filter( 'woocommerce_thankyou_order_received_text',
	            array( new WC_Gateway_Qenta_Checkout_Seamless(), 'thankyou_order_received_text' ), 10, 2 );
}

/**
 * Define possible Qenta gateways for WooCommerce
 *
 * @since 1.0.0
 *
 * @param $methods
 *
 * @return array
 */
function add_qenta_checkout_seamless( $methods ) {
	$methods[] = 'WC_Gateway_Qenta_Checkout_Seamless';

	return $methods;
}

/**
 * Create transactions table during install process
 *
 * @since 1.0.0
 */
function woocommerce_install_qenta_checkout_seamless() {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'qenta_checkout_seamless_tx';

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
	init_config_values();
}

/**
 * add submenu and page for qenta transactions
 *
 * @since 1.0.0
 */
function qenta_transactions_add_page() {
	if ( class_exists( 'WooCommerce' ) ) {
		$parent_slug = 'woocommerce';
	} else {
		$parent_slug = 'options-general.php';
	}

	require_once( WOOCOMMERCE_GATEWAY_QMORE_BASEDIR . 'classes/class-qenta-gateway.php' );

	$gateway = new WC_Gateway_Qenta_Checkout_Seamless();

	// add page to transactions
	add_submenu_page(
		$parent_slug,
		__( 'Qenta Transactions', 'woocommerce-qenta-checkout-seamless' ),
		__( 'Qenta Transactions', 'woocommerce-qenta-checkout-seamless' ),
		'manage_options',
		'qenta_transactions_page',
		array( $gateway, 'qenta_transactions_do_page' )
	);

	// add page to specific transaction
	add_submenu_page(
		null,
		__( 'Qenta Transaction', 'woocommerce-qenta-checkout-seamless' ),
		__( 'Qenta Transaction', 'woocommerce-qenta-checkout-seamless' ),
		'manage_options',
		'qenta_transaction_page',
		array( $gateway, 'qenta_transaction_do_page' )
	);
}

/**
 * Add extra page for qenta support request
 *
 * @since 1.0.0
 */
function add_qenta_support_request_page() {
	require_once( WOOCOMMERCE_GATEWAY_QMORE_BASEDIR . 'classes/class-qenta-gateway.php' );

	add_submenu_page(
		null,
		__( 'Qenta Support Request', 'woocommerce-qenta-checkout-seamless' ),
		__( 'Qenta Support Request', 'woocommerce-qenta-checkout-seamless' ),
		'manage_options',
		'qenta_support_request',
		array( new WC_Gateway_Qenta_Checkout_Seamless(), 'do_support_request' )
	);
}

/**
 * Store default values in the database
 *
 * @since 1.0.0
 */
function init_config_values() {
	require_once WOOCOMMERCE_GATEWAY_QMORE_BASEDIR . 'classes/includes/form_fields.php';

	$settings = array();

	foreach ( $fields as $group => $options ) {
		foreach ( $options as $key => $value ) {
			if ( isset( $value['default'] ) ) {
				$settings[$key] = $value['default'];
			}
		}
	}

	add_option( 'woocommerce_woocommerce_wcs_settings', $settings );
}

function add_qenta_storage_check() {
  // this is to be added to the footer, see wp_register_script arguments
  wp_register_script( 'qentaStorageCheckJS', '', [], '', true );
  $jsQentaStorageCheck = <<<JSCODE
  if ( 'undefined' != typeof QentaCEE_DataStorage ) {
    const originalMethod = QentaCEE_DataStorage.prototype.storePaymentInformation;
    QentaCEE_DataStorage.prototype.storePaymentInformation = function( paymentInformation, callback ) {
        if ( 'undefined' != typeof this.iframes && this.iframes.CCARD && ! this.iframes.CCARD.contentWindow ) {
            this.iframes.CCARD = document.querySelector('iframe');
        }

        return originalMethod.apply( this, arguments );
    }
  }
  JSCODE;
  wp_enqueue_script( 'qentaStorageCheckJS' );
  wp_add_inline_script( 'qentaStorageCheckJS', $jsQentaStorageCheck );
}
