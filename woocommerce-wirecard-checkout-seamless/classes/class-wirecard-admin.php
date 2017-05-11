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
 * Admin class
 *
 * Handles output on admin panel (settings, transactions, support request)
 *
 * @since 1.0.0
 */
class WC_Gateway_Wirecard_Checkout_Seamless_Admin {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $_settings;

	/**
	 * WC_Gateway_Wirecard_Checkout_Seamless_Admin constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 */
	public function __construct( $settings ) {
		$this->_settings = $settings;
	}

	/**
	 * Handles form output for admin panel
	 *
	 * @since 1.0.0
	 *
	 * @param $gateway WC_Gateway_Wirecard_Checkout_Seamless
	 */
	public function print_admin_form_fields( $gateway ) {
		?>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper wcs-tabs">
			<a href="javascript:void(0);" data-target="#basicdata" class="nav-tab nav-tab-active"><?= __( 'Access data',
			                                                                                              'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#options" class="nav-tab "><?= __( 'General settings',
			                                                                              'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#creditcardoptions" class="nav-tab "><?= __( 'Credit card',
			                                                                                        'woocommerce-wirecard-checkout-seamless' ) ?></a>
			<a href="javascript:void(0);" data-target="#sepaoptions" class="nav-tab "><?= __( 'SEPA',
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
	 * @since 1.0.0
	 *
	 * @param string $which
	 *
	 * @return array
	 */
	public function get_settings_fields( $which = null ) {
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
	public function include_backend_header( $gateway ) {
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
		<div class="woo-wcs-settings-header-wrapper">
			<div class="woo-wcs-backend-links">
				<a class="button-primary" id="wcs-transaction-button"
				   href="?page=wirecard_transactions_page&transaction_start=1" <?= ( $_GET['page'] == 'wirecard_transactions_page' ) ? 'disabled="disabled"' : '' ?>>
					<?= __( 'Transaction overview', 'woocommerce-wirecard-checkout-seamless' ) ?>
				</a>

				<a class="button-primary" id="wcs-support-button"
				   href="?page=wirecard_support_request" <?= ( $_GET['page'] == 'wirecard_support_request' ) ? 'disabled="disabled"' : '' ?>>
					<?= __( 'Contact support', 'woocommerce-wirecard-checkout-seamless' ) ?>
				</a>
				<a class="button-primary" id="wcs-settings-button"
				   href="?page=wc-settings&tab=checkout&section=woocommerce_wcs" <?= ( $_GET['page'] == 'wc-settings' ) ? 'disabled="disabled"' : '' ?>>
					<?= __( 'Wirecard settings', 'woocommerce-wirecard-checkout-seamless' ) ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Handles transaction output in admin panel
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Gateway_Wirecard_Checkout_Seamless_Transaction $transaction
	 * @param $page
	 */
	public function print_transaction_table( $transaction, $page ) {

		echo '<div class="wrap woocommerce"><div class="postbox">
				<h2 class="wcs-transaction-h2"><span>' . __( 'Transaction overview',
		                                                     'woocommerce-wirecard-checkout-seamless' ) . '</span></h2>
				<div class="inside">
					<table>';

		$back = __( '< Back', 'woocommerce-wirecard-checkout-seamless' );
		$next = __( 'Next >', 'woocommerce-wirecard-checkout-seamless' );

		$pages = $transaction->get_rows( $page );

		echo '</table>';

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			echo "<a class='button-primary' href='?page=wirecard_transactions_page&transaction_start=$prev_page'>$back</a>";
		}

		if ( $pages < 5 ) {
			for ( $i = 0; $i < $pages; $i ++ ) {
				$pagenr = $i + 1;
				$active = ( $pagenr == $page ) ? ' active' : '';
				$href   = ( $pagenr == $page ) ? 'javascript:void(0)' : "?page=wirecard_transactions_page&transaction_start=$pagenr";
				echo "<a class='button-primary$active' href='$href'>$pagenr</a>";
			}
		}

		if ( $page < $pages && $pages > 4 ) {
			echo "<select onchange='goToWctPage(this.value)'>";
			$start = $page - 10;
			if ( $start < 1 ) {
				$start = 1;
			}

			$stop = $page + 10;
			if ( $stop > $pages ) {
				$stop = $pages;
			}
			for ( $i = $start; $i < $stop + 1; $i ++ ) {
				$selected = ( $i == $page ) ? "selected='selected'" : '';
				echo "<option value='$i' $selected>$i</option>";
			}
			echo "</select>";
			?>


			<script language="javascript" type="text/javascript">
				var start = 1;
				function goToWctPage(page) {
					start = "?page=wirecard_transactions_page&transaction_start=" + page;
					window.location.href = start;
				}
			</script>

			<?php
		}

		if ( $page < $pages ) {
			$next_page = $page + 1;
			echo "<a class='button-primary' href='?page=wirecard_transactions_page&transaction_start=$next_page'>$next</a>";
		}
		?>
		</div></div></div>
		<?php
	}

	/**
	 * Handles transaction detail output in admin panel
	 *
	 * @since 1.0.0
	 *
	 * @param $data
	 */
	public function print_transaction_details( $data ) {

		$nonce = wp_create_nonce( 'wcs-do-bop' );

		echo "<div class='postbox' style='border: 0;'><h2 style='margin: 0;'></h2></div>";

		echo '<div class="postbox">
				<h2 class="wcs-transaction-h2"><span>' . __( 'Transaction details',
		                                                     'woocommerce-wirecard-checkout-seamless' ) . '</span></h2>
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
				<h2 class="wcs-transaction-h2"><span>' . __( 'Wirecard order details',
			                                                 'woocommerce-wirecard-checkout-seamless' ) . '</span></h2>
				<div class="inside">
				<table>';

			foreach ( $data->order_details as $key => $value ) {
				echo "<tr><th>$key</th><td>$value</td></tr>";
			}

			echo '</table></div></div>';
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

			echo "<td> {$payment['paymentNumber']}</td>
				  <td>{$payment['timeCreated']}</td>
			      <td>{$payment['gatewayReferenceNumber']}</td>
			      <td>{$payment['state']}</td>
			      <td>{$payment['approveAmount']}</td>
			      <td>{$payment['depositAmount']}</td>
			      <td>{$payment['currency']}</td>
			      <td><form method='post'>";

			echo "<input name='wcs-do-bop' type='hidden' value='$nonce'>";
			echo "<input type='hidden' name='paymentNumber' value='{$payment['paymentNumber']}'>";
			echo "<input type='hidden' name='orderNumber' value='{$payment['orderNumber']}'>";
			echo "<input type='hidden' name='currency' value='{$payment['currency']}'>";

			// suppres notices for transferFund transactions, otherwise no notices are raised
			$operations_allowed = explode( ",", @$payment['operationsAllowed'] );

			asort( $operations_allowed );

			foreach ( $operations_allowed as $operation ) {
				if ( empty( $operation ) ) {
					continue;
				}

				echo "<div class='wcs-op-group'>";
				if ( $operation == 'DEPOSIT' or $operation == 'REFUND' ) {
					echo "<input type='text' autocomplete='off' value='' name='amount'>";
				}
				echo "<button class='button-primary' type='submit' name='submitWcsBackendOperation' value='$operation'>" . __( $operation,
				                                                                                                               'woocommerce-wirecard-checkout-seamless' ) . "</button>";
				echo "</div>";
			}

			echo "</form></td>
				  </tr>";
		}

		echo '</table>
				</div>
			</div>';

		// credits
		echo '<div class="postbox ">
				<h2 class="wcs-transaction-h2"><span>' . __( 'Credits', 'woocommerce-wirecard-checkout-seamless' ) . '</span></h2>
				<div class="inside">
				<table class="wcs-payments-table">
					<tr>
						<th>' . __( 'Number', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Date', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Gateway reference', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Credit state', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Amount', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Currency', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
						<th>' . __( 'Operations', 'woocommerce-wirecard-checkout-seamless' ) . '</th>
					</tr>';

		if ( count( $data->credits ) == 0 ) {
			echo "<tr class='wcs-no-entries'>
					<td colspan='8'>
						<span class='dashicons dashicons-warning'></span><br>
						" . __( 'No credits available', 'woocommerce-wirecard-checkout-seamless' ) . "
					</td>
				  </tr>";
		}

		foreach ( $data->credits as $credit ) {
			$credit = $credit->getData();

			echo "<td>{$credit['creditNumber']}</td>
				  <td>{$credit['timeCreated']}</td>
			      <td>{$credit['gatewayReferenceNumber']}</td>
			      <td>{$credit['state']}</td>
			      <td>{$credit['amount']}</td>
			      <td>{$credit['currency']}</td>
			      <td><form method='post'>";
			echo "<input name='wcs-do-bop' type='hidden' value='$nonce'>";
			echo "<input type='hidden' name='creditNumber' value='{$credit['creditNumber']}'>";
			echo "<input type='hidden' name='orderNumber' value='{$credit['orderNumber']}'>";
			echo "<input type='hidden' name='currency' value='{$credit['currency']}'>";
			echo "<input type='hidden' name='wcOrder' value='{$data->id_order}'>";

			foreach ( explode( ",", $credit['operationsAllowed'] ) as $operation ) {
				if ( empty( $operation ) ) {
					continue;
				}
				echo "<button class='button-primary' type='submit' name='submitWcsBackendOperation' value='$operation'>" . __( $operation,
				                                                                                                               'woocommerce-wirecard-checkout-seamless' ) . "</button>";
			}

			echo "</form></td>
				  </tr>";
		}

		echo '</table>
				</div>
			</div>';
	}


	/**
	 * Handles support requests
	 *
	 * @since 1.0.0
	 */
	public function print_support_form() {
		?>
		<div class="wrap woocommerce">
			<div class="postbox">
				<h2 class="wcs-transaction-h2">
					<span>
						<?= __( 'Support Request', 'woocommerce-wirecard-checkout-seamless' ) ?>
					</span>
				</h2>
				<div class="inside">
					<?php
					if ( isset( $_POST['send-request'] ) ) {
						$this->create_support_request();
						echo '<br/>';
					}

					settings_errors();
					?>
					<form action="?page=wirecard_support_request" method="post" name="support-request-form">
						<table>
							<tr>
								<td class="titledesc support-label">
									<label for="support-mail">
										<?= __( 'To:', 'woocommerce-wirecard-checkout-seamless' ) ?>
									</label>
								</td>
								<td class="forminp"><select name="support-mail">
										<option value="support.at@wirecard.com">
											<?= __( 'Support Team Wirecard CEE, Austria',
											        'woocommerce-wirecard-checkout-seamless' ) ?>
										</option>
										<option value="support@wirecard.com">
											<?= __( 'Support Team Wirecard AG, Germany',
											        'woocommerce-wirecard-checkout-seamless' ) ?>
										</option>
										<option value="support.sg@wirecard.com">
											<?= __( 'Support Team Wirecard Singapore',
											        'woocommerce-wirecard-checkout-seamless' ) ?>
										</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="titledesc support-label">
									<label for="customer-mail">
										<?= __( 'Your e-mail address:', 'woocommerce-wirecard-checkout-seamless' ) ?>
									</label>
								</td>
								<td class="forminp"><input type="email" name="customer-mail"/></td>
							</tr>
							<tr>
								<td class="titledesc support-label">
									<label for="support-message">
										<?= __( 'Your message:', 'woocommerce-wirecard-checkout-seamless' ) ?>
									</label>
								</td>
								<td class="forminp"><textarea rows="5" cols="70" name="support-message"></textarea></td>
							</tr>
						</table>
						<br/>
						<input type="submit" class="button-primary" name="send-request"
						       value="<?= __( 'Send your request', 'woocommerce-wirecard-checkout-seamless' ) ?>"/>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Create support request with config data
	 *
	 * @since 1.0.0
	 */
	public function create_support_request() {
		global $wp_version;
		$postdata = $_POST;

		$message = "WordPress: " . $wp_version . "\n";
		$message .= "WooCommerce: " . WC()->version . "\n";
		$message .= "PHP: " . phpversion() . "\n";
		$message .= "Pluginname: " . WOOCOMMERCE_GATEWAY_WCS_NAME . "\n";
		$message .= "Pluginversion: " . WOOCOMMERCE_GATEWAY_WCS_VERSION . "\n";
		$message .= "-----------------------------------------\n";
		$message .= "Message: \n" . strip_tags( $postdata['support-message'] ) . "\n";
		$message .= "-----------------------------------------\n";

		foreach ( $this->get_settings_fields() as $group => $fields ) {
			foreach ( $fields as $key => $field ) {
				if ( $key == 'woo_wcs_secret' ) {
					continue;
				}
				if ( $key == 'woo_wcs_customerid' ) {
				}
				$message .= $field['title'] . ": ";
				$message .= $this->_settings[ $key ] . "\n";
			}
		}
		$send_to = $postdata['support-mail'];
		$from    = $postdata['customer-mail'];
		$headers = 'From: <' . $from . '>';
		$subject = 'WooCommerce Support Request';


		if ( empty( $from ) ) {
			add_settings_error( '', '', __( 'Your e-mail address must not be empty.',
			                                'woocommerce-wirecard-checkout-seamless' ), 'error' );
		} else {
			$send = wp_mail( $send_to, $subject, $message, $headers );

			if ( $send ) {
				add_settings_error( '', '',
				                    __( 'Your request has been sent', 'woocommerce-wirecard-checkout-seamless' ),
				                    'updated' );
			} else {
				add_settings_error( '', '',
				                    __( 'Your request could not be sent', 'woocommerce-wirecard-checkout-seamless' ),
				                    'error' );
			}
		}
	}
}
