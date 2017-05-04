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
 * Class WC_Gateway_Wirecard_Checkout_Seamless_Admin
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Admin {

	/**
	 * Handles form output for admin panel
	 *
	 * @param $gateway WC_Gateway_Wirecard_Checkout_Seamless
	 *
	 * @since 1.0.0
	 */

	function print_admin_form_fields( $gateway ) {
		?>
		<div class="woo-wcs-settings-header-wrapper">
			<div class="woo-wcs-backend-links">
				<a class="button-primary"
				   href="?page=wirecard_transactions_page&transaction_start=1">
					<?= __( 'Transaction Overview', 'woocommerce-wirecard-checkout-seamless' ) ?>
				</a>

				<a class="button-primary" href="javascript:void(0);">
					<?= __( 'Contact support', 'woocommerce_wirecard_checkout_seamless' ) ?>
				</a>
			</div>
		</div>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper wcs-tabs">
			<a href="javascript:void(0);" data-target="#basicdata" class="nav-tab nav-tab-active"><?= __( 'Access data',
			                                                                                              'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#options" class="nav-tab "><?= __( 'General settings',
			                                                                              'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#creditcardoptions" class="nav-tab "><?= __( 'Credit card',
			                                                                                        'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#sepaoptions" class="nav-tab "><?= __( 'Sepa',
			                                                                                  'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#invoiceoptions" class="nav-tab "><?= __( 'Invoice',
			                                                                                     'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#installmentoptions" class="nav-tab "><?= __( 'Installment',
			                                                                                         'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#standardpayments" class="nav-tab "><?= __( 'Standard payments',
			                                                                                       'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#bankingpayments" class="nav-tab "><?= __( 'Banking payments',
			                                                                                      'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#alternativepayments"
			   class="nav-tab "><?= __( 'Alternative payments', 'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#mobilepayments" class="nav-tab "><?= __( 'Mobile payments',
			                                                                                     'woocommerce-wirecard-checkout-seamless' ) ?></a>
		</nav>
		<div class="tab-content panel">
			<div class="tab-pane active" id="basicdata">
				<table
					class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'basicdata' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="options">
				<table class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'options' ),
				                                                                false ); ?></table>
			</div>
			<div class="tab-pane" id="creditcardoptions">
				<table
					class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'creditcardoptions' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="sepaoptions">
				<table
					class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'sepaoptions' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="invoiceoptions">
				<table
					class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'invoiceoptions' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="installmentoptions">
				<table
					class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'installmentoptions' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="standardpayments">
				<table
					class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'standardpayments' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="bankingpayments">
				<table
					class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'bankingpayments' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="alternativepayments">
				<table
					class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'alternativepayments' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="mobilepayments">
				<table
					class="form-table"><?= $gateway->generate_settings_html( $this->get_settings_fields( 'mobilepayments' ),
				                                                             false ); ?></table>
			</div>
		</div>
		<?php
	}

	/**
	 * Get all or the corresponding settings fields group
	 *
	 * @param string $which
	 *
	 * @since 1.0.0
	 * @return array
	 */
	function get_settings_fields( $which = null ) {
		include "includes/form_fields.php";
		if ( $which !== null ) {
			return $fields[ $which ];
		}

		return $fields;
	}

	/**
	 * Prints basic Checkout Seamless header for admin
	 *
	 * @since 1.0.0
	 *
	 * @param $gateway
	 */
	function include_backend_header( $gateway ) {
		?>
		<link rel='stylesheet'
		      href='<?= plugins_url( 'woocommerce-wirecard-checkout-seamless/assets/styles/admin.css' ) ?>'>
		<script src='<?= plugins_url( 'woocommerce-wirecard-checkout-seamless/assets/scripts/admin.js' ) ?>'></script>
		<h3><?php echo ( ! empty( $gateway->method_title ) ) ? $gateway->method_title : __( 'Settings',
		                                                                                    'woocommerce-wirecard-checkout-seamless' ); ?></h3>

		<div class="woo-wcs-settings-header-wrapper">
			<img src="<?= plugins_url( 'woocommerce-wirecard-checkout-seamless/assets/images/wirecard-logo.png' ) ?>">
			<p><?= __( 'Wirecard - Your Full Service Payment Provider - Comprehensive solutions from one single source',
			           'woocommerce-wirecard-checkout-seamless' ) ?></p>

			<p><?= __( 'Wirecard is one of the world´s leading providers of outsourcing and white label solutions for electronic payment transactions.',
			           'woocommerce-wirecard-checkout-seamless' ) ?></p>

			<p><?= __( 'As independent provider of payment solutions, we accompany our customers along the entire business development. Our payment solutions are perfectly tailored to suit e-Commerce requirements and have made	us Austria´s leading payment service provider. Customization, competence, and commitment.',
			           'woocommerce-wirecard-checkout-seamless' ) ?></p>

		</div>
		<?php
	}

	/**
	 * Handles transaction output in admin panel
	 *
	 * @since 1.0.0
	 *
	 * @param $gateway
	 */
	function print_transaction_table( $transaction, $start ) {
		?>
		<div class="woo-wcs-backend-links">
			<a class="button-primary"
			   href="?page=wc-settings&tab=checkout&section=woocommerce_wcs">
				<?= __( 'Back to Settings', 'woocommerce-wirecard-checkout-seamless' ) ?>
			</a>
		</div>

		<nav class="nav-tab-wrapper woo-nav-tab-wrapper wcs-tabs">
			<a href="javascript:void(0);" data-target="#transaction-table"
			   class="nav-tab nav-tab-active"><?= __( 'Transaction Overview',
			                                          'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#backend-operations"
			   class="nav-tab "><?= __( 'Backend Operations',
			                            'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#fund-transfer" class="nav-tab "><?= __( 'Fund Transfer',
			                                                                                    'woocommerce-wirecard-checkout-seamless' ) ?></a>
		</nav>
		<div class="tab-content panel">
			<div class="tab-pane active" id="transaction-table">
				<table><?php $more = $transaction->get_rows( $start, 20 + $start ); ?></table>
				<?php
				if ( $start > 20 ) {
					?>
					<a class="button-primary"
					   href="?page=wc-settings&tab=checkout&section=woocommerce_wcs&transaction_start=<?php echo( $start - 20 ); ?>">
						<?= __( 'Back', 'woocommerce-wirecard-checkout-seamless' ) ?>
					</a>
					<?php
				}
				if ( $start + 20 < $more ) {
					?>
					<a class="button-primary"
					   href="?page=wc-settings&tab=checkout&section=woocommerce_wcs&transaction_start=<?php echo( $start + 20 ); ?>">
						<?= __( 'Next', 'woocommerce-wirecard-checkout-seamless' ) ?>
					</a>

					<input type="number" name="transaction_start" onchange="setStartValue(this.value)" min="0"
					       max="<?php echo $more; ?>"/>

					<script language="javascript" type="text/javascript">
						var start = 1;
						function setStartValue(data) {
							start = "?page=wc-settings&tab=checkout&section=woocommerce_wcs&transaction_start=" + data;
							document.getElementById("wcs-transaction-start").setAttribute("href", start);
						}
					</script>
					<a class="button-primary" id="wcs-transaction-start"
					   href="?page=wc-settings&tab=checkout&section=woocommerce_wcs&transaction_start=1">
						<?= __( 'Get transactions starting at ', 'woocommerce-wirecard-checkout-seamless' ) ?>
					</a>
					<?php
				}
				?>
			</div>
			<div class="tab-pane" id="backend-operations">
				<div>No content yet</div>
			</div>
			<div class="tab-pane" id="fund-transfer">
				<div>No content yet</div>
			</div>
		</div>
		<?php
	}

	public function print_transaction_details( $data ) {
		echo "<div class='woo-wcs-backend-links'>
			<a class='button-primary' href='?page=wirecard_transactions_page'>
				" . __( 'Back to Transactions', 'woocommerce-wirecard-checkout-seamless' ) . "
			</a>
		</div>
		
		<br>";

		echo "<div class='postbox' style='border: 0;'><h2 style='margin: 0;'></h2></div>";

		echo '<div class="postbox">
				<h2 class="wcs-transaction-h2"><span>' . __( 'Transaction details', 'woocommerce-wirecard-checkout-seamless' ) . '</span></h2>
				<div class="inside">
				<table>
					<tr>
						<th>' . __( 'Order', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<td><a href="' . admin_url( "post.php?post=" . absint( $data->id_order ) ) . '&action=edit">' . $data->id_order . '</a></td>
					</tr>
					<tr>
						<th>' . __( 'Payment method', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<td>' . $data->payment_method . '</td>
					</tr>
					<tr>
						<th>' . __( 'Payment state', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<td>' . $data->payment_state . '</td>
					</tr>
					<tr>
						<th>' . __( 'Amount', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<td>' . $data->amount . '</td>
					</tr>
					<tr>
						<th>' . __( 'Currency', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<td>' . $data->currency . '</td>
					</tr>
					<tr>
						<th>' . __( 'Gateway reference number', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<td>' . $data->gateway_reference . '</td>
					</tr>
					<tr>
						<th>' . __( 'Wirecard order number', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<td>' . $data->order_number . '</td>
					</tr>
				</table>
				</div>
			</div>';

		if ( $data->order_details ) {
			echo '<div class="postbox ">
				<h2 class="wcs-transaction-h2"><span>' . __( 'Wirecard order details', 'woocommerce-wirecard-checkout-seamless' ) . '</span></h2>
				<div class="inside">
				<table>';

			foreach ( $data->order_details as $key => $value ) {
				echo "<tr><th>$key</th><td>$value</td></tr>";
			}

			echo '</table>
				</div>
			</div>';
		}

		echo '<div class="postbox ">
				<h2 class="wcs-transaction-h2"><span>' . __( 'Payments', 'woocommerce-wirecard-checkout-seamless' ) . '</span></h2>
				<div class="inside">
				<table class="wcs-payments-table">
					<tr>
						<th>' . __( 'Number', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Date', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Gateway reference', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Payment state', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Approved', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Deposited', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Currency', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Operations', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
					</tr>';

		if ( count( $data->payments ) == 0 ) {
			echo "<tr class='wcs-no-entries'>
					<td colspan='8'>
						<span class='dashicons dashicons-warning'></span><br>
						" . __( 'No payments available', 'woocommerce-wirecard-checkout-seamless' ) . "
					</td>
				  </tr>";
		}

		foreach ( $data->payments as $payment ) {
			$payment = $payment->getData();

			echo "<td>{$payment['paymentNumber']}</td>
				  <td>{$payment['timeCreated']}</td>
			      <td>{$payment['gatewayReferenceNumber']}</td>
			      <td>{$payment['state']}</td>
			      <td>{$payment['approveAmount']}</td>
			      <td>{$payment['depositAmount']}</td>
			      <td>{$payment['currency']}</td>
			      <td><form method='post'>";

			echo "<input type='hidden' name='paymentNumber' value='{$payment['paymentNumber']}'>";
			echo "<input type='hidden' name='orderNumber' value='{$payment['orderNumber']}'>";
			echo "<input type='hidden' name='currency' value='{$payment['currency']}'>";

			foreach ( explode( ",", $payment['operationsAllowed'] ) as $operation ) {
				if ( empty( $operation ) ) {
					continue;
				}

				if ( $operation == 'DEPOSIT' or $operation == 'REFUND' ) {
					echo "<input type='text' autocomplete='off' value='' name='amount'>";
				}
				echo "<button class='button-primary' type='submit' name='submitWcsBackendOperation' value='$operation'>$operation</button>";
			}

			echo "</form></td>
				  </tr>";
		}

		echo '</table>
				</div>
			</div>';
	}

}
