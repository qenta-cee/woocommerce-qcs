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

// Compatibility for newer wordpress versions where the includes are different
if ( ! function_exists( 'get_editable_roles' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/user.php' );
}

$user_roles = array();
foreach ( get_editable_roles() as $role => $details ) {
	$user_roles[ $role ] = translate_user_role( $details['name'] );
}

$countries_obj = new WC_Countries();
$countries     = $countries_obj->__get( 'countries' );

$fields = array(
	'basicdata'           => array(
		'woo_wcs_configuration'   => array(
			'title'       => __( 'Configuration', 'woocommerce-qenta-checkout-seamless' ),
			'type'        => 'select',
			'default'     => 'demo',
			'description' => __(
				'For integration, select predefined configuration settings or \'Production\' for live systems ',
				'woocommerce-qenta-checkout-seamless'
			),
			'options'     => array(
				'production' => __( 'Production', 'woocommerce-qenta-checkout-seamless' ),
				'demo'       => __( 'Demo', 'woocommerce-qenta-checkout-seamless' ),
				'test3d'     => __( 'Test 3D', 'woocommerce-qenta-checkout-seamless' )
			)
		),
		'woo_wcs_customerid'      => array(
			'title'       => __( 'Customer ID', 'woocommerce-qenta-checkout-seamless' ),
			'type'        => 'text',
			'default'     => 'D200001',
			'description' => __(
				'Customer number you received from Qenta (customerId, i.e. D2#####).',
				'woocommerce-qenta-checkout-seamless'
			)
		),
		'woo_wcs_shopid'          => array(
			'title'       => __( 'Shop ID', 'woocommerce-qenta-checkout-seamless' ),
			'type'        => 'text',
			'default'     => 'seamless',
			'description' => __(
				'Shop identifier in case of more than one shop.', 'woocommerce-qenta-checkout-seamless'
			)
		),
		'woo_wcs_secret'          => array(
			'title'       => __( 'Secret', 'woocommerce-qenta-checkout-seamless' ),
			'type'        => 'text',
			'default'     => 'B8AKTPWBRMNBV455FG6M2DANE99WU2',
			'description' => __(
				'String which you received from Qenta for signing and validating data to prove their authenticity.',
				'woocommerce-qenta-checkout-seamless'
			)
		),
		'woo_wcs_serviceurl'      => array(
			'title'       => __( 'URL to contact page', 'woocommerce-qenta-checkout-seamless' ),
			'type'        => 'text',
			'default'     => '',
			'description' => __(
				'URL to web page containing your contact information (imprint).',
				'woocommerce-qenta-checkout-seamless'
			)
		),
		'woo_wcs_backendpassword' => array(
			'title'       => __( 'Back-end password', 'woocommerce-qenta-checkout-seamless' ),
			'type'        => 'text',
			'default'     => 'jcv45z',
			'description' => __(
				'Password for back-end operations (Toolkit).',
				'woocommerce-qenta-checkout-seamless'
			)
		)
	),
	'options'             => array(
		'woo_wcs_forwardconsumershippingdata'   => array(
			'title'       => __( 'Forward consumer shipping data', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __(
				'Forwarding shipping data about your consumer to the respective financial service provider.',
				'woocommerce-qenta-checkout-seamless'
			),
			'default'     => 1,
			'type'        => 'switch'
		),
		'woo_wcs_forwardconsumerbillingdata'    => array(
			'title'       => __( 'Forward consumer billing data', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __(
				'Forwarding billing data about your consumer to the respective financial service provider.',
				'woocommerce-qenta-checkout-seamless'
			),
			'default'     => 1,
			'type'        => 'switch'
		),
		'woo_wcs_forwardbasketdata'             => array(
			'title'       => __( 'Forward basket data', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __(
				'Forwarding basket data to the respective financial service provider. ',
				'woocommerce-qenta-checkout-seamless'
			),
      'default'     => 1,
			'type'        => 'switch'
		),
		'woo_wcs_notificationemail'             => array(
			'title'       => __( 'Notification e-mail', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __(
				'Receiving notification by e-mail regarding the orders of your consumers if an error occurred in the communication between Qenta and your online shop.<br>Please contact our sales teams to activate this feature.',
				'woocommerce-qenta-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_automateddeposit'              => array(
			'title'       => __( 'Automated deposit', 'woocommerce-qenta-checkout-seamless' ),
			'default'     => 0,
			'description' => __(
				'Enabling an automated deposit of payments.<br>Please contact our sales teams to activate this feature.',
				'woocommerce-qenta-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_payolutionterms'               => array(
			'title'       => __( 'payolution terms', 'woocommerce-qenta-checkout-seamless' ),
			'default'     => 1,
			'description' => __(
				'Consumer must accept payolution terms during the checkout process.',
				'woocommerce-qenta-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_payolutionmid'                 => array(
			'title'       => __( 'payolution mID', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Your payolution merchant ID, non-base64-encoded.',
			                     'woocommerce-qenta-checkout-seamless' )
		)
	),
	'sepaoptions'         => array(
		'woo_wcs_sepa_display_bic_field' => array(
			'type'        => 'switch',
			'default'     => 1,
			'title'       => __( 'Display BIC field', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Display input field to enter the BIC. Note that this field is not mandatory for your consumer even if it is enabled.',
			                     'woocommerce-qenta-checkout-seamless' )
		)
	),
	'creditcardoptions'   => array(
		'woo_wcs_saqacompliance'                         => array(
			'type'        => 'switch',
			'default'     => 1,
			'title'       => __( 'SAQ A compliance', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Selecting \'NO\', the stringent SAQ A-EP is applicable. Selecting \'YES\', Qenta Checkout Seamless is integrated with the \'PCI DSS SAQ A Compliance\' feature and SAQ A is applicable.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_allowmotoforgroup'                      => array(
			'type'        => 'multiselect',
			'title'       => __( 'Allowing MoTo for roles', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Credit Card - Mail Order and Telephone Order (MoTo) must never be offered to any consumer in your online shop.',
			                     'woocommerce-qenta-checkout-seamless' ),
			'options'     => $user_roles
		),
		'woo_wcs_iframecssurl'                           => array(
			'title'       => __( 'Iframe CSS-URL', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Entry of a name for the CSS file in order to customize the iframe input fields when using the \'PCI DSS SAQ A Compliance\' feature. File must be placed in the \'view/css\' directory of the plugin. Complete URL is required e.g. https://www.servername.com/iframe.css.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_number_placeholder_text'             => array(
			'title'       => __( 'Credit card number placeholder text', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Placeholder text for the credit card number field.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_holder_placeholder_text'             => array(
			'title'       => __( 'Card holder placeholder text', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Placeholder text for the card holder field.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_cvc_placeholder_text'                => array(
			'title'       => __( 'CVC placeholder text', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Placeholder text for the CVC field.', 'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_issue_number_placeholder_text'       => array(
			'title'       => __( 'Issue number placeholder text', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Placeholder text for the issue number field.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_display_issue_date_placeholder_text' => array(
			'type'        => 'switch',
			'title'       => __( 'Display issue date placeholder text', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Display placeholder text for the issue date field. Only applicable if the \'PCI DSS SAQ A Compliance\' feature is enabled.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_display_exp_date_field'              => array(
			'type'        => 'switch',
      'default'     => 1,
			'title'       => __( 'Display expiration date field', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Display input field to enter the expiration date in your credit card form during the checkout process.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_display_cardholder_field'            => array(
			'type'        => 'switch',
			'default'     => 1,
			'title'       => __( 'Display card holder field', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Display input field to enter the card holder name in your credit card form during the checkout process.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_display_cvc_field'                   => array(
			'type'        => 'switch',
			'default'     => 1,
			'title'       => __( 'Display CVC field', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Display input field to enter the CVC in your credit card form during the checkout process.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_display_issue_date_field'            => array(
			'type'        => 'switch',
			'title'       => __( 'Display issue date field', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Display input field to enter the credit card issue date in your credit card form during the checkout process. Some credit cards do not have an issue date.',
			                     'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_cc_display_issue_number_field'          => array(
			'type'        => 'switch',
			'title'       => __( 'Display issue number field', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Display input field to enter the credit card issue number in your credit card form during the checkout process. Some credit cards do not have an issue number.',
			                     'woocommerce-qenta-checkout-seamless' )
		)
	),
	'invoiceoptions'      => array(
		'woo_wcs_invoiceprovider'                    => array(
			'type'    => 'select',
			'default' => 'payolution',
			'title'   => __( 'Invoice provider', 'woocommerce-qenta-checkout-seamless' ),
			'options' => array( 'qenta' => 'Qenta', 'ratepay' => 'RatePay', 'payolution' => 'payolution' )
		),
		'woo_wcs_invoice_billing_shipping_equal'     => array(
			'type'        => 'switch',
			'default'     => 1,
			'title'       => __( 'Billing/shipping address must be identical', 'woocommerce-qenta-checkout-seamless' ),
			'description' => __( 'Only applicable for payolution.', 'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_invoice_allowed_billing_countries'  => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed billing countries', 'woocommerce-qenta-checkout-seamless' ),
			'options'        => $countries,
			'default'        => array( 'AT', 'DE', 'CH' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_invoice_allowed_shipping_countries' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed shipping countries', 'woocommerce-qenta-checkout-seamless' ),
			'options'        => $countries,
			'default'        => array( 'AT', 'DE', 'CH' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_invoice_accepted_currencies'        => array(
			'type'           => 'multiselect',
			'title'          => __( 'Accepted currencies', 'woocommerce-qenta-checkout-seamless' ),
			'options'        => get_woocommerce_currencies(),
			'multiple'       => true,
			'default'        => array( 'EUR' ),
			'select_buttons' => true
		),
		'woo_wcs_invoice_min_amount'                 => array(
			'title'   => __( 'Minimum amount', 'woocommerce-qenta-checkout-seamless' ),
			'default' => 10
		),
		'woo_wcs_invoice_max_amount'                 => array(
			'title'   => __( 'Maximum amount', 'woocommerce-qenta-checkout-seamless' ),
			'default' => 3500
		)
	),
	'installmentoptions'  => array(
		'woo_wcs_installmentprovider'                    => array(
			'type'    => 'select',
			'default' => 'payolution',
			'title'   => __( 'Installment provider', 'woocommerce-qenta-checkout-seamless' ),
			'options' => array( 'ratepay' => 'RatePay', 'payolution' => 'payolution' )
		),
		'woo_wcs_installment_billing_shipping_equal'     => array(
			'type'    => 'switch',
			'default' => 1,
			'title'   => __( 'Billing/shipping address must be identical', 'woocommerce-qenta-checkout-seamless' )
		),
		'woo_wcs_installment_allowed_billing_countries'  => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed billing countries', 'woocommerce-qenta-checkout-seamless' ),
			'options'        => $countries,
			'default'        => array( 'AT', 'DE', 'CH' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_installment_allowed_shipping_countries' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed shipping countries', 'woocommerce-qenta-checkout-seamless' ),
			'options'        => $countries,
			'default'        => array( 'AT', 'DE', 'CH' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_installment_accepted_currencies'        => array(
			'type'           => 'multiselect',
			'title'          => __( 'Accepted currencies', 'woocommerce-qenta-checkout-seamless' ),
			'options'        => get_woocommerce_currencies(),
			'default'        => array( 'EUR' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_installment_min_amount'                 => array(
			'title'   => __( 'Minimum amount', 'woocommerce-qenta-checkout-seamless' ),
			'default' => 150
		),
		'woo_wcs_installment_max_amount'                 => array(
			'title' => __( 'Maximum amount', 'woocommerce-qenta-checkout-seamless' ),
			'default' => 3500
		)
	),
	'standardpayments'    => array(
		'wcs_ccard_enable'              => array(
			'title' => __( 'Credit Card', 'woocommerce-qenta-checkout-seamless' ),
      'default'     => 1,
			'type'  => 'switch'
		),
		'wcs_ccard-moto_enable'         => array(
			'title' => __( 'Credit Card - Mail Order and Telephone Order', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_maestro_enable'            => array(
			'title' => __( 'Maestro SecureCode', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_sofortueberweisung_enable' => array(
			'title' => __( 'SOFORT Banking', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_paypal_enable'             => array(
			'title' => __( 'PayPal', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_sepa-dd_enable'            => array(
			'title' => __( 'SEPA Direct Debit', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_invoice_enable'            => array(
			'title' => __( 'Invoice', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_invoiceb2b_enable'         => array(
			'title' => __( 'Invoice B2B', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		)
	),
	'bankingpayments'     => array(
		'wcs_eps_enable'                   => array(
			'title' => __( 'eps-Überweisung', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_idl_enable'                   => array(
			'title' => __( 'iDEAL', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_giropay_enable'               => array(
			'title' => __( 'giropay', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_przelewy24_enable'            => array(
			'title' => __( 'Przelewy24', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		)
	),
	'alternativepayments' => array(
		'wcs_psc_enable'          => array(
			'title' => __( 'paysafecard', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_installment_enable'  => array(
			'title' => __( 'Installment', 'woocommerce-qenta-checkout-seamless' ),
			'type'  => 'switch'
		)
	)
);
