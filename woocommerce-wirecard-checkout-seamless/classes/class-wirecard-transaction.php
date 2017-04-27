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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Transaction
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Transaction {

	protected $_table_name;
	protected $_fields_list;

	public function __construct() {
		global $wpdb;

		$this->_table_name = $wpdb->base_prefix . 'wirecard_checkout_seamless_tx';

		//field lables for transaction overview
		$this->_fields_list = array(
			'id_tx'    => array(
				'title' => __( "ID", 'woocommerce-wcs' )
			),
			'message'  => array(
				'title' => __( "Status", 'woocommerce-wcs' )
			),
			'amount'   => array(
				'title' => __( "Amount", 'woocommerce-wcs' )
			),
			'currency' => array(
				'title' => __( "Currency", 'woocommerce-wcs' )
			),

			'id_order'          => array(
				'title' => __( "Order number", 'woocommerce-wcs' )
			),
			'gateway_reference' => array(
				'title' => __( "Gateway reference number", 'woocommerce-wcs' )
			),
			'payment_method'    => array(
				'title' => __( "Payment method", 'woocommerce-wcs' )
			),
			'payment_state'     => array(
				'title' => __( "State", 'woocommerce-wcs' )
			),

		);
	}

	/**
	 * Create basic transaction entry
	 *
	 * @since 1.0.0
	 *
	 * @param $id_order
	 * @param $amount
	 * @param $currency
	 * @param $payment_method
	 *
	 * @return mixed
	 */
	function create( $id_order, $amount, $currency, $payment_method ) {
		global $wpdb;

		$wpdb->insert(
			$this->_table_name,
			array(
				'id_order'       => $id_order,
				'amount'         => $amount,
				'currency'       => $currency,
				'payment_method' => $payment_method,
				'payment_state'  => 'CREATED',
				'created'        => current_time( 'mysql', true )
			)
		);

		return $wpdb->insert_id;
	}

	/**
	 * Update transaction table with $data array
	 *
	 * @since 1.0.0
	 *
	 * @param $data
	 * @param $identifier
	 */
	function update( $data, $identifier ) {
		global $wpdb;

		//update transaction entry
		$update = $wpdb->update(
			$this->_table_name,
			$data,
			$identifier
		);

		//return $update;
	}

	function get( $id_tx ) {
		//return transaction entry row
	}

	/**
	 * Get transaction html table for overview beginning from $start to $stop
	 *
	 * @since 1.0.0
	 *
	 * @param int $start
	 * @param int $stop
	 */
	function get_rows( $start = 0, $stop = 20 ) {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wirecard_checkout_seamless_tx LIMIT %d,%d", $start,
		                         $stop );
		$rows  = $wpdb->get_results( $query, ARRAY_A );

		?>
		<tr><?php
		foreach ( $this->_fields_list as $field_key => $field_value ) {
			?>
			<th><?php echo $field_value['title']; ?></th><?php
		}
		?></tr><?php

		foreach ( $rows as $row ) {
			?>
			<tr><?php
			foreach ( $this->_fields_list as $field_key => $field_value ) {
				?>
				<td>
				<?php if ( key_exists( $field_key, $row ) ) {
					echo $row[ $field_key ];
				}
				?></td><?php
			}
			?></tr><?php
		}
	}
}
