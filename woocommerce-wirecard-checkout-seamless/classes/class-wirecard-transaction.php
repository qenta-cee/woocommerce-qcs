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
	protected $_settings;

	public function __construct( $settings ) {
		global $wpdb;

		$this->_settings = $settings;

		$this->_table_name = $wpdb->base_prefix . 'wirecard_checkout_seamless_tx';

		//field lables for transaction overview
		$this->_fields_list = array(
			'id_tx'    => array(
				'title' => __( "ID", 'woocommerce-wirecard-checkout-seamless' )
			),
			'message'  => array(
				'title' => __( "Status", 'woocommerce-wirecard-checkout-seamless' )
			),
			'amount'   => array(
				'title' => __( "Amount", 'woocommerce-wirecard-checkout-seamless' )
			),
			'currency' => array(
				'title' => __( "Currency", 'woocommerce-wirecard-checkout-seamless' )
			),

			'id_order'          => array(
				'title' => __( "Order number", 'woocommerce-wirecard-checkout-seamless' )
			),
			'gateway_reference' => array(
				'title' => __( "Gateway reference number", 'woocommerce-wirecard-checkout-seamless' )
			),
			'payment_method'    => array(
				'title' => __( "Payment method", 'woocommerce-wirecard-checkout-seamless' )
			),
			'payment_state'     => array(
				'title' => __( "State", 'woocommerce-wirecard-checkout-seamless' )
			),
			'actions'           => array(
				'title' => __( "", 'woocommerce-wirecard-checkout-seamless' )
			)

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
	 * @param null $request
	 * @param null $response
	 *
	 * @return mixed
	 */
	function create(
		$id_order,
		$amount,
		$currency,
		$payment_method,
		$request = null,
		$response = null
	) {
		global $wpdb;

		$wpdb->insert(
			$this->_table_name,
			array(
				'id_order'       => $id_order,
				'amount'         => $amount,
				'currency'       => $currency,
				'payment_method' => $payment_method,
				'payment_state'  => 'CREATED',
				'created'        => current_time( 'mysql', true ),
				'request'        => $request,
				'response'       => $response
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

	/**
	 * get a single transaction
	 *
	 * @since 1.0.0
	 *
	 * @param $id_tx
	 *
	 * @return array|null|object|void
	 */
	function get( $id_tx ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wirecard_checkout_seamless_tx WHERE id_tx = $id_tx" );
	}

	/**
	 * Get transaction id for existing transaction, return false if not existing
	 *
	 * @since 1.0.0
	 *
	 * @param $id_order
	 *
	 * @return int
	 */
	function get_existing_transaction( $id_order ) {
		global $wpdb;

		$transaction = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wirecard_checkout_seamless_tx WHERE id_order = $id_order",
		                               ARRAY_A );
		if ( empty( $transaction ) ) {
			return 0;
		}

		return $transaction["id_tx"];

	}

	/**
	 * Get transaction html table for overview beginning from $start to $stop
	 *
	 * @since 1.0.0
	 *
	 * @param int $page
	 *
	 * @return int $row_count
	 */
	function get_rows( $page = 1 ) {
		global $wpdb;

		$start = ( $page * 20 ) - 19;

		$start --;
		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wirecard_checkout_seamless_tx LIMIT %d,20", $start );
		$rows  = $wpdb->get_results( $query, ARRAY_A );

		$sum_query = $wpdb->prepare( "SELECT CEILING(COUNT(*)/20) as pages FROM {$wpdb->prefix}wirecard_checkout_seamless_tx", null );

		$pages     = $wpdb->get_row( $sum_query );

		if ( $pages == null ) {
			$pages        = new stdClass();
			$pages->pages = 1;
		}

		echo "<tr>";
		foreach ( $this->_fields_list as $field_key => $field_value ) {
			echo "<th>";
			echo $field_value['title'];
			echo "</th>";
		}
		echo "</tr>";

		foreach ( $rows as $row ) {
			echo "<tr>";

			foreach ( $this->_fields_list as $field_key => $field_value ) {
				echo "<td>";
				if ( key_exists( $field_key, $row ) ) {
					echo $row[ $field_key ];
				}
				echo "</td>";
			}

			echo "<td><a href='?page=wirecard_transaction_page&id={$row["id_tx"]}' class='button-primary'>";
			echo __( 'View', 'woocommerce-wirecard-checkout-seamless' );
			echo "</a></td>
			</tr>";
		}

		return $pages->pages;
	}
}
