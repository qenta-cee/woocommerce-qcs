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

$user_roles = array();
foreach ( get_editable_roles() as $role => $details ) {
	$user_roles[ $role ] = translate_user_role( $details['name'] );
}

$countries_obj = new WC_Countries();
$countries     = $countries_obj->__get( 'countries' );

$fields = array(
	'basicdata'           => array(
		'woo_wcs_configuration'   => array(
			'title'       => __( 'Configuration', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'select',
			'description' => __(
				'For integration, select predefined configuration settings or \'Production\' for live systems ',
				'woocommerce-wirecard-checkout-seamless'
			),
			'options'     => array(
				'production' => __( 'Production', 'woocommerce-wirecard-checkout-seamless' ),
				'demo'       => __( 'Demo', 'woocommerce-wirecard-checkout-seamless' ),
				'test'       => __( 'Test', 'woocommerce-wirecard-checkout-seamless' ),
				'test3d'     => __( 'Test 3D', 'woocommerce-wirecard-checkout-seamless' )
			)
		),
		'woo_wcs_customerid'      => array(
			'title'       => __( 'Customer ID', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'text',
			'description' => __(
				'Customer number you received from Wirecard (customerId, i.e. D2#####).',
				'woocommerce-wirecard-checkout-seamless'
			)
		),
		'woo_wcs_shopid'          => array(
			'title'       => __( 'Shop ID', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'text',
			'description' => __(
				'Shop identifier in case of more than one shop.', 'woocommerce-wirecard-checkout-seamless'
			)
		),
		'woo_wcs_secret'          => array(
			'title'       => __( 'Secret', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'text',
			'description' => __(
				'String which you received from Wirecard for signing and validating data to prove their authenticity.',
				'woocommerce-wirecard-checkout-seamless'
			)
		),
		'woo_wcs_backendpassword' => array(
			'title'       => __( 'Back-end password', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'text',
			'description' => __(
				'Password for back-end operations (Toolkit).',
				'woocommerce-wirecard-checkout-seamless'
			)
		)
	),
	'options'             => array(
		'woo_wcs_createorders'                  => array(
			'title'       => __( 'Create orders', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __(
				'Selecting \'Always\', orders are created even if the payment process leads to failed payment.<br>Selecting \'Only for successful payments\', orders are created if the payment process was successful. ',
				'woocommerce-wirecard-checkout-seamless'
			),
			'type'        => 'select',
			'options'     => array(
				'always'            => __( 'Always', 'woocommerce-wirecard-checkout-seamless' ),
				'onlyforsuccessful' => __(
					'Only for successful payments', 'woocommerce-wirecard-checkout-seamless'
				)
			)
		),
		'woo_wcs_transactionid'                 => array(
			'title'       => __( 'Transaction ID', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'select',
			'options'     => array(
				'wirecardordernumber'    => __(
					'Wirecard order number', 'woocommerce-wirecard-checkout-seamless'
				),
				'gatewayreferencenumber' => __(
					'Gateway reference number', 'woocommerce-wirecard-checkout-seamless'
				)
			),
			'description' => __(
				'Wirecard order number: Unique number defined by Wirecard identifying the payment.<br>
Gateway reference number: Reference number defined by the processor or acquirer. ',
				'woocommerce-wirecard-checkout-seamless'
			)
		),
		'woo_wcs_shopreferenceinpostingcontext' => array(
			'title'             => __( 'Shop reference in posting text', 'woocommerce-wirecard-checkout-seamless' ),
			'description'       => __(
				'Reference to your online shop on your consumer\'s bank statement, limited to 9 characters.',
				'woocommerce-wirecard-checkout-seamless'
			),
			'custom_attributes' => array( 'maxlength' => 9 )
		),
		'woo_wcs_forwardconsumershippingdata'   => array(
			'title'       => __( 'Forward consumer shipping data', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __(
				'Forwarding shipping data about your consumer to the respective financial service provider.',
				'woocommerce-wirecard-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_forwardconsumerbillingdata'    => array(
			'title'       => __( 'Forward consumer billing data', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __(
				'Forwarding billing data about your consumer to the respective financial service provider.',
				'woocommerce-wirecard-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_forwardbasketdata'             => array(
			'title'       => __( 'Forward basket data', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __(
				'Forwarding basket data to the respective financial service provider. ',
				'woocommerce-wirecard-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_notificationemail'             => array(
			'title'       => __( 'Notification e-mail', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __(
				'Receiving notification by e-mail regarding the orders of your consumers if an error occurred in the communication between Wirecard and your online shop.<br>Please contact our sales teams to activate this feature.',
				'woocommerce-wirecard-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_automateddeposit'              => array(
			'title'       => __( 'Automated deposit', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __(
				'Enabling an automated deposit of payments.<br>Please contact our sales teams to activate this feature.',
				'woocommerce-wirecard-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_payolutionterms'               => array(
			'title'       => __( 'payolution terms', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __(
				'Consumer must accept payolution terms during the checkout process.',
				'woocommerce-wirecard-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_payolutionmid'                 => array(
			'title'       => __( 'payolution mID', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Your payolution merchant ID, non-base64-encoded.',
			                     'woocommerce-wirecard-checkout-seamless' )
		)
	),
	'creditcardoptions'   => array(
		'woo_wcs_saqacompliance'                         => array(
			'type'        => 'switch',
			'title'       => __( 'SAQ A compliance', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Selecting \'NO\', the stringent SAQ A-EP is applicable. Selecting \'YES\', Wirecard Checkout Seamless is integrated with the \'PCI DSS SAQ A Compliance\' feature and SAQ A is applicable.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_allowmotoforgroup'                      => array(
			'type'        => 'select',
			'title'       => __( 'Allowing MoTo for group', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Credit Card - Mail Order and Telephone Order (MoTo) must never be offered to any consumer in your online shop.',
			                     'woocommerce-wirecard-checkout-seamless' ),
			'options'     => $user_roles
		),
		'woo_wcs_iframecssurl'                           => array(
			'title'       => __( 'Credit card number placeholder text', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Placeholder text for the credit card number field.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_number_placeholder_text'             => array(
			'title'       => __( 'Credit card number placeholder text', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Placeholder text for the credit card number field.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_holder_placeholder_text'             => array(
			'title'       => __( 'Card holder placeholder text', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Placeholder text for the card holder field.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_cvc_placeholder_text'                => array(
			'title'       => __( 'CVC placeholder text', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Placeholder text for the CVC field.', 'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_issue_number_placeholder_text'       => array(
			'title'       => __( 'Issue number placeholder text', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Placeholder text for the issue number field.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_display_issue_date_placeholder_text' => array(
			'type'        => 'switch',
			'title'       => __( 'Display issue date placeholder text', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Display placeholder text for the issue date field. Only applicable if the \'PCI DSS SAQ A Compliance\' feature is enabled.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_display_exp_date_field'              => array(
			'type'        => 'switch',
			'title'       => __( 'Display expiration date field', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Display input field to enter the expiration date in your credit card form during the checkout process.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_display_cardholder_field'            => array(
			'type'        => 'switch',
			'title'       => __( 'Display card holder field', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Display input field to enter the card holder name in your credit card form during the checkout process.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_display_cvc_field'                   => array(
			'type'        => 'switch',
			'title'       => __( 'Display CVC field', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Display input field to enter the CVC in your credit card form during the checkout process.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_display_issue_date_field'            => array(
			'type'        => 'switch',
			'title'       => __( 'Display issue date field', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Display input field to enter the credit card issue date in your credit card form during the checkout process. Some credit cards do not have an issue date.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_display_issue_number_field'          => array(
			'type'        => 'switch',
			'title'       => __( 'Display issue number field', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Display input field to enter the credit card issue number in your credit card form during the checkout process. Some credit cards do not have an issue number.',
			                     'woocommerce-wirecard-checkout-seamless' )
		)
	),
	'invoiceoptions'      => array(
		'woo_wcs_invoiceprovider'                   => array(
			'type'    => 'select',
			'title'   => __( 'Invoice provider', 'woocommerce-wirecard-checkout-seamless' ),
			'options' => array( 'wirecard' => 'Wirecard', 'ratepay' => 'RatePay', 'payolution' => 'payolution' )
		),
		'woo_wcs_invoice_billing_shipping_equal'    => array(
			'type'  => 'switch',
			'title' => __( 'Billing/shipping address musst be identical', 'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_invoice_allowed_billing_countries' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed billing countries', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => $countries,
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_invoice_allowed_shipping_countries' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed shipping countries', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => $countries,
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_invoice_accepted_currencies' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Accepted currencies', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => get_woocommerce_currencies(),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_invoice_min_age' => array(
			'title' => __('Minimum age', 'woocommerce-wirecard-checkout-seamless'),
			'description' => __( 'Only applicable for RatePay', 'woocommerce-wirecard-checkout-seamless'),
			'custom_attributes' => array('maxlength' => 3 )
		),
		'woo_wcs_invoice_min_amount' => array(
			'title' => __('Minimum amount', 'woocommerce-wirecard-checkout-seamless')
		),
		'woo_wcs_invoice_max_amount' => array(
			'title' => __('Maximum amount', 'woocommerce-wirecard-checkout-seamless')
		),
		'woo_wcs_invoice_min_basket_size' => array(
			'title' => __('Minimum basket size', 'woocommerce-wirecard-checkout-seamless')
		),
		'woo_wcs_invoice_max_basket_size' => array(
			'title' => __('Maximum basket size', 'woocommerce-wirecard-checkout-seamless')
		)
	),
	'installmentoptions'       => array(
		'woo_wcs_installmentprovider'                   => array(
			'type'    => 'select',
			'title'   => __( 'Installment provider', 'woocommerce-wirecard-checkout-seamless' ),
			'options' => array( 'wirecard' => 'Wirecard', 'ratepay' => 'RatePay', 'payolution' => 'payolution' )
		),
		'woo_wcs_installment_billing_shipping_equal'    => array(
			'type'  => 'switch',
			'title' => __( 'Billing/shipping address musst be identical', 'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_installment_allowed_billing_countries' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed billing countries', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => $countries,
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_installment_allowed_shipping_countries' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed shipping countries', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => $countries,
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_installment_accepted_currencies' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Accepted currencies', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => get_woocommerce_currencies(),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_installment_min_age' => array(
			'title' => __('Minimum age', 'woocommerce-wirecard-checkout-seamless'),
			'description' => __( 'Only applicable for RatePay', 'woocommerce-wirecard-checkout-seamless'),
			'custom_attributes' => array('maxlength' => 3 )
		),
		'woo_wcs_installment_min_amount' => array(
			'title' => __('Minimum amount', 'woocommerce-wirecard-checkout-seamless')
		),
		'woo_wcs_installment_max_amount' => array(
			'title' => __('Maximum amount', 'woocommerce-wirecard-checkout-seamless')
		),
		'woo_wcs_installment_min_basket_size' => array(
			'title' => __('Minimum basket size', 'woocommerce-wirecard-checkout-seamless')
		),
		'woo_wcs_installment_max_basket_size' => array(
			'title' => __('Maximum basket size', 'woocommerce-wirecard-checkout-seamless')
		)
	),
	'standardpayments'    => array(),
	'bankingpayments'     => array(),
	'alternativepayments' => array(),
	'mobilepayments'      => array(),
	'voucherpayments'     => array()
);