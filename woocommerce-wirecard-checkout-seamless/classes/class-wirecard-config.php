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
		'demo' => array(
			'customer_id' => 'D200001',
			'shop_id' => 'seamless',
			'secret' => 'B8AKTPWBRMNBV455FG6M2DANE99WU2',
			'backendpw' => 'jcv45z'
		),
		'test' => array(
			'customer_id' => 'D200411',
			'shop_id' => 'seamless',
			'secret' => 'CHCSH7UGHVVX2P7EHDHSY4T2S4CGYK4QBE4M5YUUG2ND5BEZWNRZW5EJYVJQ',
			'backendpw' => '2g4f9q2m'
		),
		'test3d' => array(
			'customer_id' => 'D200411',
			'shop_id' => 'seamless3D',
			'secret' => 'DP4TMTPQQWFJW34647RM798E9A5X7E8ATP462Z4VGZK53YEJ3JWXS98B9P4F',
			'backendpw' => '2g4f9q2m'
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
		$config_mode = $gateway->get_option('woo_wcs_configuration');

		if( array_key_exists($config_mode, $this->_presets) ) {
			return Array(
				'CUSTOMER_ID' => $this->_presets[$config_mode]['customer_id'],
				'SHOP_ID' => $this->_presets[$config_mode]['shop_id'],
				'SECRET' => $this->_presets[$config_mode]['secret'],
				'LANGUAGE' => $this->get_language_code(),
			);
		}
		else{
			return Array(
				'CUSTOMER_ID' => trim($gateway->get_option('woo_wcs_customerid')),
				'SHOP_ID' => trim($gateway->get_option('woo_wcs_shopid')),
				'SECRET' => trim($gateway->get_option('woo_wcs_secret')),
				'LANGUAGE' => $this->get_language_code(),
			);
		}
	}

	/**
	 * Extract language code from locale settings
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	function get_language_code()
	{
		$locale = get_locale();
		$parts = explode('_', $locale);
		return $parts[0];
	}
}
