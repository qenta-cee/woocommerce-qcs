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

/**
 * Admin class
 *
 * Handles output on admin panel (settings, transactions, support request)
 *
 * @since 1.0.0
 */
class WC_Gateway_Qenta_Checkout_Seamless_Admin {

	/**
	 * Payment gateway settings
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $_settings;

	/**
	 * WC_Gateway_Qenta_Checkout_Seamless_Admin constructor.
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
	 * @param $gateway WC_Gateway_Qenta_Checkout_Seamless
	 */
	public function print_admin_form_fields( $gateway ) {
		?>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper wcs-tabs">
			<a href="javascript:void(0);" data-target="#basicdata" class="nav-tab nav-tab-active"><?php echo esc_html(__( 'Access data',
			                                                                                              'woocommerce-qenta-checkout-seamless' )); ?></a>
			<a href="javascript:void(0);" data-target="#options" class="nav-tab "><?php echo esc_html(__( 'General settings',
			                                                                              'woocommerce-qenta-checkout-seamless' )); ?></a>
			<a href="javascript:void(0);" data-target="#creditcardoptions" class="nav-tab "><?php echo esc_html(__( 'Credit card',
			                                                                                        'woocommerce-qenta-checkout-seamless' )); ?></a>
			<a href="javascript:void(0);" data-target="#sepaoptions" class="nav-tab "><?php echo esc_html(__( 'SEPA',
			                                                                                  'woocommerce-qenta-checkout-seamless' )); ?></a>
			<a href="javascript:void(0);" data-target="#invoiceoptions" class="nav-tab "><?php echo esc_html(__( 'Invoice',
			                                                                                     'woocommerce-qenta-checkout-seamless' )); ?></a>
			<a href="javascript:void(0);" data-target="#installmentoptions" class="nav-tab "><?php echo esc_html(__( 'Installment',
			                                                                                         'woocommerce-qenta-checkout-seamless' )); ?></a>
			<a href="javascript:void(0);" data-target="#standardpayments" class="nav-tab "><?php echo esc_html(__( 'Standard payments',
			                                                                                       'woocommerce-qenta-checkout-seamless' )); ?></a>
			<a href="javascript:void(0);" data-target="#bankingpayments" class="nav-tab "><?php echo esc_html(__( 'Banking payments',
			                                                                                      'woocommerce-qenta-checkout-seamless' )); ?></a>
			<a href="javascript:void(0);" data-target="#alternativepayments"
			   class="nav-tab "><?php echo esc_html(__( 'Alternative payments', 'woocommerce-qenta-checkout-seamless' )); ?></a>
		</nav>
		<div class="tab-content panel">
			<div class="tab-pane active" id="basicdata">
				<table
					class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'basicdata' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="options">
				<table class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'options' ),
				                                                                false ); ?></table>
			</div>
			<div class="tab-pane" id="creditcardoptions">
				<table
					class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'creditcardoptions' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="sepaoptions">
				<table
					class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'sepaoptions' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="invoiceoptions">
				<table
					class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'invoiceoptions' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="installmentoptions">
				<table
					class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'installmentoptions' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="standardpayments">
				<table
					class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'standardpayments' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="bankingpayments">
				<table
					class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'bankingpayments' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="alternativepayments">
				<table
					class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'alternativepayments' ),
				                                                             false ); ?></table>
			</div>
			<div class="tab-pane" id="mobilepayments">
				<table
					class="form-table"><?php echo $gateway->generate_settings_html( $this->get_settings_fields( 'mobilepayments' ),
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
    wp_enqueue_style('paymentCSS', esc_url_raw( WOOCOMMERCE_GATEWAY_QMORE_URL . "assets/styles/admin.css" ));
    wp_enqueue_script('adminJS', esc_url_raw( WOOCOMMERCE_GATEWAY_QMORE_URL . "assets/scripts/admin.js" ));
		?>
		<h3><?php echo ( ! empty( $gateway->method_title ) ) ? esc_html($gateway->method_title) : esc_html(__( 'Settings',
		                                                                                    'woocommerce-qenta-checkout-seamless' )); ?></h3>
		<div class="woo-wcs-settings-header-wrapper">
			<img src="<?php echo esc_url_raw(WOOCOMMERCE_GATEWAY_QMORE_URL . 'assets/images/qenta-logo.png' ); ?>">
			<p><?php echo esc_html(__( 'Qenta - Your Full Service Payment Provider - Comprehensive solutions from one single source',
			           'woocommerce-qenta-checkout-seamless' )); ?></p>

			<p><?php echo esc_html(__( 'Qenta is one of the world´s leading providers of outsourcing and white label solutions for electronic payment transactions.',
			           'woocommerce-qenta-checkout-seamless' )); ?></p>

			<p><?php echo esc_html(__( 'As independent provider of payment solutions, we accompany our customers along the entire business development. Our payment solutions are perfectly tailored to suit e-Commerce requirements and have made	us Austria´s leading payment service provider. Customization, competence, and commitment.',
			           'woocommerce-qenta-checkout-seamless' )); ?></p>

		</div>
		<div class="woo-wcs-settings-header-wrapper">
			<div class="woo-wcs-backend-links">
				<a class="button-primary" id="wcs-transaction-button"
				   href="?page=qenta_transactions_page&transaction_start=1" <?php echo ( $_GET['page'] == 'qenta_transactions_page' ) ? 'disabled="disabled"' : '' ?>>
					<?php echo esc_html(__( 'Transaction overview', 'woocommerce-qenta-checkout-seamless' )); ?>
				</a>

				<a class="button-primary" id="wcs-support-button"
				   href="?page=qenta_support_request" <?php echo ( $_GET['page'] == 'qenta_support_request' ) ? 'disabled="disabled"' : '' ?>>
					<?php echo esc_html(__( 'Contact support', 'woocommerce-qenta-checkout-seamless' )); ?>
				</a>
				<a class="button-primary" id="wcs-settings-button"
				   href="?page=wc-settings&tab=checkout&section=woocommerce_wcs" <?php echo ( $_GET['page'] == 'wc-settings' ) ? 'disabled="disabled"' : '' ?>>
					<?php echo esc_html(__( 'Qenta settings', 'woocommerce-qenta-checkout-seamless' )); ?>
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
	 * @param WC_Gateway_Qenta_Checkout_Seamless_Transaction $transaction
	 * @param $page
	 */
	public function print_transaction_table( $transaction, $page ) {

		echo '<div class="wrap woocommerce"><div class="postbox">
				<h2 class="wcs-transaction-h2"><span>' . esc_html(__( 'Transaction overview',
		                                                     'woocommerce-qenta-checkout-seamless' )) . '</span></h2>
				<div class="inside">
					<table>';

		$back = esc_html(__( '< Back', 'woocommerce-qenta-checkout-seamless' ));
		$next = esc_html(__( 'Next >', 'woocommerce-qenta-checkout-seamless' ));

		$pages = $transaction->get_rows( $page );

		echo '</table>';

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			echo "<a class='button-primary' href='?page=qenta_transactions_page&transaction_start=" . esc_url_raw($prev_page) . "'>" . esc_html($back) . "</a>";
		}

		if ( $pages < 5 ) {
			for ( $i = 0; $i < $pages; $i ++ ) {
				$pagenr = $i + 1;
				$active = ( $pagenr == $page ) ? ' active' : '';
				$href   = ( $pagenr == $page ) ? 'javascript:void(0)' : "?page=qenta_transactions_page&transaction_start=" . esc_html($pagenr);
				echo "<a class='button-primary" . esc_attr($active) . "' href='" . esc_url_raw($href) . "'>" . esc_html($pagenr) . "</a>";
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
				echo "<option value='" . esc_attr($i) . "' " . esc_attr($selected) . ">" . esc_html($i) . "</option>";
			}
			echo "</select>";
		}

		if ( $page < $pages ) {
			$next_page = $page + 1;
			echo "<a class='button-primary' href='?page=qenta_transactions_page&transaction_start=" . esc_url_raw($next_page) . "'>$next</a>";
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
				<h2 class="wcs-transaction-h2"><span>' . esc_html(__( 'Transaction details',
		                                                     'woocommerce-qenta-checkout-seamless' )) . '</span></h2>
				<div class="inside">
				<table>
					<tr>
						<th>' . esc_html(__( 'Order', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<td><a href="' . esc_url_raw(admin_url( "post.php?post=" . absint( $data->id_order ) )) . '&action=edit">' . esc_html($data->id_order) . '</a></td>
					</tr>
					<tr>
						<th>' . esc_html(__( 'Payment method', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<td>' . esc_html($data->payment_method) . '</td>
					</tr>
					<tr>
						<th>' . esc_html(__( 'Payment state', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<td>' . esc_html($data->payment_state) . '</td>
					</tr>
					<tr>
						<th>' . esc_html(__( 'Amount', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<td>' . esc_html($data->amount) . '</td>
					</tr>
					<tr>
						<th>' . esc_html(__( 'Currency', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<td>' . esc_html($data->currency) . '</td>
					</tr>
					<tr>
						<th>' . esc_html(__( 'Gateway reference number', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<td>' . esc_html($data->gateway_reference) . '</td>
					</tr>
					<tr>
						<th>' . esc_html(__( 'Qenta order number', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<td>' . esc_html($data->order_number) . '</td>
					</tr>
				</table>
				</div>
			</div>';

		if ( $data->order_details ) {
			echo '<div class="postbox ">
				<h2 class="wcs-transaction-h2"><span>' . esc_html(__( 'Qenta order details',
			                                                 'woocommerce-qenta-checkout-seamless' )) . '</span></h2>
				<div class="inside">
				<table>';

			foreach ( $data->order_details as $key => $value ) {
				echo "<tr><th>" . esc_html($key) . "</th><td>" . esc_html($value) . "</td></tr>";
			}

			echo '</table></div></div>';
		}

		echo '<div class="postbox ">
				<h2 class="wcs-transaction-h2"><span>' . esc_html(__( 'Payments', 'woocommerce-qenta-checkout-seamless' )) . '</span></h2>
				<div class="inside">
				<table class="wcs-payments-table">
					<tr>
						<th>' . esc_html(__( 'Number', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Date', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Gateway reference', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Payment state', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Approved', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Deposited', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Currency', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Operations', 'woocommerce-qenta-checkout-seamless' )) . '</th>
					</tr>';

		if ( count( $data->payments ) == 0 ) {
			echo "<tr class='wcs-no-entries'>
					<td colspan='8'>
						<span class='dashicons dashicons-warning'></span><br>
						" . esc_html(__( 'No payments available', 'woocommerce-qenta-checkout-seamless' )) . "
					</td>
				  </tr>";
		}

		foreach ( $data->payments as $payment ) {
			$payment = $payment->getData();

			echo "<td>" . esc_html($payment['paymentNumber']) . "</td>
				    <td>" . esc_html($payment['timeCreated']) . "</td>
			      <td>" . esc_html($payment['gatewayReferenceNumber']) ."</td>
			      <td>" . esc_html($payment['state']) . "</td>
			      <td>" . esc_html($payment['approveAmount']) . "</td>
			      <td>" . esc_html($payment['depositAmount']) . "</td>
			      <td>" . esc_html($payment['currency']) . "</td>
			      <td><form method='post'>";

			echo "<input name='wcs-do-bop' type='hidden' value='" . esc_attr($nonce) . "'>";
			echo "<input type='hidden' name='paymentNumber' value='" . esc_attr($payment['paymentNumber']) ."'>";
			echo "<input type='hidden' name='orderNumber' value='" . esc_attr($payment['orderNumber']) . "'>";
			echo "<input type='hidden' name='currency' value='" . esc_attr($payment['currency']) . "'>";
			echo "<input type='hidden' name='id_tx' value='" . esc_attr($data->id_tx) . "'>";

			// suppres notices for transferFund transactions, otherwise no notices are raised
			$operations_allowed = explode( ",", @$payment['operationsAllowed'] );

			asort( $operations_allowed );

			$brand = $data->order_details['brand'];

			foreach ( $operations_allowed as $operation ) {
				if ( empty( $operation ) ) {
					continue;
				}

				echo "<div class='wcs-op-group'>";

				if ( $brand == 'Invoice' ) {
					echo "<input type='hidden' value='" . esc_attr($data->amount) . "' name='amount'>";
				} elseif ( $operation == 'DEPOSIT' or $operation == 'REFUND' ) {
					echo "<input type='text' autocomplete='off' value='' name='amount'>";
				}

				echo "<button class='button-primary' type='submit' name='submitWcsBackendOperation' value='" . esc_attr($operation) . "'>" . esc_html(__( $operation,
				                                                                                                               'woocommerce-qenta-checkout-seamless' )) . "</button>";
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
				<h2 class="wcs-transaction-h2"><span>' . esc_html(__( 'Credits', 'woocommerce-qenta-checkout-seamless' )) . '</span></h2>
				<div class="inside">
				<table class="wcs-payments-table">
					<tr>
						<th>' . esc_html(__( 'Number', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Date', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Gateway reference', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Credit state', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Amount', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Currency', 'woocommerce-qenta-checkout-seamless' )) . '</th>
						<th>' . esc_html(__( 'Operations', 'woocommerce-qenta-checkout-seamless' )) . '</th>
					</tr>';

		if ( count( $data->credits ) == 0 ) {
			echo "<tr class='wcs-no-entries'>
					<td colspan='8'>
						<span class='dashicons dashicons-warning'></span><br>
						" . esc_html(__( 'No credits available', 'woocommerce-qenta-checkout-seamless' )) . "
					</td>
				  </tr>";
		}

		foreach ( $data->credits as $credit ) {
			$credit = $credit->getData();

			echo "<td>" . esc_html($credit['creditNumber']) . "</td>
				    <td>" . esc_html($credit['timeCreated']) . "</td>
			      <td>" . esc_html($credit['gatewayReferenceNumber']) . "</td>
			      <td>" . esc_html($credit['state']) . "</td>
			      <td>" . esc_html($credit['amount']) . "</td>
			      <td>" . esc_html($credit['currency']) . "</td>
			      <td><form method='post'>";
			echo "<input name='wcs-do-bop' type='hidden' value='" . esc_attr($nonce) . "'>";
			echo "<input type='hidden' name='creditNumber' value='" . esc_attr($credit['creditNumber']) . "'>";
			echo "<input type='hidden' name='orderNumber' value='" . esc_attr($credit['orderNumber']) . "'>";
			echo "<input type='hidden' name='currency' value='" . esc_attr($credit['currency']) . "'>";
			echo "<input type='hidden' name='wcOrder' value='" . esc_attr($data->id_order) . "'>";

			foreach ( explode( ",", $credit['operationsAllowed'] ) as $operation ) {
				if ( empty( $operation ) ) {
					continue;
				}
				echo "<button class='button-primary' type='submit' name='submitWcsBackendOperation' value='" . esc_attr($operation) . "'>" . esc_html(__( $operation,
				                                                                                                               'woocommerce-qenta-checkout-seamless' )) . "</button>";
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
						<?php echo esc_html(__( 'Support Request', 'woocommerce-qenta-checkout-seamless' )); ?>
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
					<form action="?page=qenta_support_request" method="post" name="support-request-form">
						<table>
							<tr>
								<td class="titledesc support-label">
									<label for="support-mail">
										<?php echo esc_html(__( 'To:', 'woocommerce-qenta-checkout-seamless' )); ?>
									</label>
								</td>
								<td class="forminp"><select name="support-mail">
										<option value="support@qenta.com">
											<?php echo esc_html(__( 'Support Team Qenta CEE, Austria',
											        'woocommerce-qenta-checkout-seamless' )); ?>
										</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="titledesc support-label">
									<label for="customer-mail">
										<?php echo esc_html(__( 'Your e-mail address:', 'woocommerce-qenta-checkout-seamless' )); ?>
									</label>
								</td>
								<td class="forminp"><input type="email" name="customer-mail"/></td>
							</tr>
							<tr>
								<td class="titledesc support-label">
									<label for="support-message">
										<?php echo esc_html(__( 'Your message:', 'woocommerce-qenta-checkout-seamless' )); ?>
									</label>
								</td>
								<td class="forminp"><textarea rows="5" cols="70" name="support-message"></textarea></td>
							</tr>
						</table>
						<br/>
						<input type="submit" class="button-primary" name="send-request"
						       value="<?php echo esc_attr(__( 'Send your request', 'woocommerce-qenta-checkout-seamless' )); ?>"/>
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
		$postdata = array_map( 'sanitize_text_field', $_POST );

		$message = "WordPress: " . $wp_version . "\n";
		$message .= "WooCommerce: " . WC()->version . "\n";
		$message .= "PHP: " . phpversion() . "\n";
		$message .= "Pluginname: " . WOOCOMMERCE_GATEWAY_QMORE_NAME . "\n";
		$message .= "Pluginversion: " . WOOCOMMERCE_GATEWAY_QMORE_VERSION . "\n";
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
		$headers = 'From: <' . sanitize_email($from) . '>';
		$subject = 'WooCommerce Support Request';


		if ( empty( $from ) ) {
			add_settings_error( '', '', __( 'Your e-mail address must not be empty.',
			                                'woocommerce-qenta-checkout-seamless' ), 'error' );
		} else {
			$send = wp_mail( sanitize_email($send_to), esc_html($subject), esc_html($message), $headers );

			if ( $send ) {
				add_settings_error( '', '',
				                    __( 'Your request has been sent', 'woocommerce-qenta-checkout-seamless' ),
				                    'updated' );
			} else {
				add_settings_error( '', '',
				                    __( 'Your request could not be sent', 'woocommerce-qenta-checkout-seamless' ),
				                    'error' );
			}
		}
	}
}
