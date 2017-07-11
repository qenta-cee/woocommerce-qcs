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
			'default'     => 'production',
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
			'default'     => 'D200001',
			'description' => __(
				'Customer number you received from Wirecard (customerId, i.e. D2#####).',
				'woocommerce-wirecard-checkout-seamless'
			)
		),
		'woo_wcs_shopid'          => array(
			'title'       => __( 'Shop ID', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'text',
			'default'     => 'seamless',
			'description' => __(
				'Shop identifier in case of more than one shop.', 'woocommerce-wirecard-checkout-seamless'
			)
		),
		'woo_wcs_secret'          => array(
			'title'       => __( 'Secret', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'text',
			'default'     => 'B8AKTPWBRMNBV455FG6M2DANE99WU2',
			'description' => __(
				'String which you received from Wirecard for signing and validating data to prove their authenticity.',
				'woocommerce-wirecard-checkout-seamless'
			)
		),
		'woo_wcs_serviceurl'      => array(
			'title'       => __( 'URL to contact page', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'text',
			'default'     => '',
			'description' => __(
				'URL to web page containing your contact information (imprint).',
				'woocommerce-wirecard-checkout-seamless'
			)
		),
		'woo_wcs_backendpassword' => array(
			'title'       => __( 'Back-end password', 'woocommerce-wirecard-checkout-seamless' ),
			'type'        => 'text',
			'default'     => 'jcv45z',
			'description' => __(
				'Password for back-end operations (Toolkit).',
				'woocommerce-wirecard-checkout-seamless'
			)
		)
	),
	'options'             => array(
		'woo_wcs_forwardconsumershippingdata'   => array(
			'title'       => __( 'Forward consumer shipping data', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __(
				'Forwarding shipping data about your consumer to the respective financial service provider.',
				'woocommerce-wirecard-checkout-seamless'
			),
			'default'     => 1,
			'type'        => 'switch'
		),
		'woo_wcs_forwardconsumerbillingdata'    => array(
			'title'       => __( 'Forward consumer billing data', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __(
				'Forwarding billing data about your consumer to the respective financial service provider.',
				'woocommerce-wirecard-checkout-seamless'
			),
			'default'     => 1,
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
			'default'     => 0,
			'description' => __(
				'Enabling an automated deposit of payments.<br>Please contact our sales teams to activate this feature.',
				'woocommerce-wirecard-checkout-seamless'
			),
			'type'        => 'switch'
		),
		'woo_wcs_payolutionterms'               => array(
			'title'       => __( 'payolution terms', 'woocommerce-wirecard-checkout-seamless' ),
			'default'     => 1,
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
	'sepaoptions'         => array(
		'woo_wcs_sepa_display_bic_field' => array(
			'type'        => 'switch',
			'default'     => 1,
			'title'       => __( 'Display BIC field', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Display input field to enter the BIC. Note that this field is not mandatory for your consumer even if it is enabled.',
			                     'woocommerce-wirecard-checkout-seamless' )
		)
	),
	'creditcardoptions'   => array(
		'woo_wcs_saqacompliance'                         => array(
			'type'        => 'switch',
			'default'     => 0,
			'title'       => __( 'SAQ A compliance', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Selecting \'NO\', the stringent SAQ A-EP is applicable. Selecting \'YES\', Wirecard Checkout Seamless is integrated with the \'PCI DSS SAQ A Compliance\' feature and SAQ A is applicable.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_allowmotoforgroup'                      => array(
			'type'        => 'multiselect',
			'title'       => __( 'Allowing MoTo for roles', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Credit Card - Mail Order and Telephone Order (MoTo) must never be offered to any consumer in your online shop.',
			                     'woocommerce-wirecard-checkout-seamless' ),
			'options'     => $user_roles
		),
		'woo_wcs_iframecssurl'                           => array(
			'title'       => __( 'Iframe CSS-URL', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Entry of a name for the CSS file in order to customize the iframe input fields when using the \'PCI DSS SAQ A Compliance\' feature. File must be placed in the \'view/css\' directory of the plugin.',
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
			'default'     => 1,
			'title'       => __( 'Display card holder field', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Display input field to enter the card holder name in your credit card form during the checkout process.',
			                     'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_cc_display_cvc_field'                   => array(
			'type'        => 'switch',
			'default'     => 1,
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
		'woo_wcs_invoiceprovider'                    => array(
			'type'    => 'select',
			'default' => 'payolution',
			'title'   => __( 'Invoice provider', 'woocommerce-wirecard-checkout-seamless' ),
			'options' => array( 'wirecard' => 'Wirecard', 'ratepay' => 'RatePay', 'payolution' => 'payolution' )
		),
		'woo_wcs_invoice_billing_shipping_equal'     => array(
			'type'        => 'switch',
			'default'     => 1,
			'title'       => __( 'Billing/shipping address must be identical', 'woocommerce-wirecard-checkout-seamless' ),
			'description' => __( 'Only applicable for payolution.', 'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_invoice_allowed_billing_countries'  => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed billing countries', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => $countries,
			'default'        => array( 'AT', 'DE', 'CH' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_invoice_allowed_shipping_countries' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed shipping countries', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => $countries,
			'default'        => array( 'AT', 'DE', 'CH' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_invoice_accepted_currencies'        => array(
			'type'           => 'multiselect',
			'title'          => __( 'Accepted currencies', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => get_woocommerce_currencies(),
			'multiple'       => true,
			'default'        => array( 'EUR' ),
			'select_buttons' => true
		),
		'woo_wcs_invoice_min_amount'                 => array(
			'title'   => __( 'Minimum amount', 'woocommerce-wirecard-checkout-seamless' ),
			'default' => 10
		),
		'woo_wcs_invoice_max_amount'                 => array(
			'title'   => __( 'Maximum amount', 'woocommerce-wirecard-checkout-seamless' ),
			'default' => 3500
		),
		'woo_wcs_invoice_min_basket_size'            => array(
			'title' => __( 'Minimum basket size', 'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_invoice_max_basket_size'            => array(
			'title' => __( 'Maximum basket size', 'woocommerce-wirecard-checkout-seamless' )
		)
	),
	'installmentoptions'  => array(
		'woo_wcs_installmentprovider'                    => array(
			'type'    => 'select',
			'default' => 'payolution',
			'title'   => __( 'Installment provider', 'woocommerce-wirecard-checkout-seamless' ),
			'options' => array( 'ratepay' => 'RatePay', 'payolution' => 'payolution' )
		),
		'woo_wcs_installment_billing_shipping_equal'     => array(
			'type'    => 'switch',
			'default' => 1,
			'title'   => __( 'Billing/shipping address must be identical', 'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_installment_allowed_billing_countries'  => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed billing countries', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => $countries,
			'default'        => array( 'AT', 'DE', 'CH' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_installment_allowed_shipping_countries' => array(
			'type'           => 'multiselect',
			'title'          => __( 'Allowed shipping countries', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => $countries,
			'default'        => array( 'AT', 'DE', 'CH' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_installment_accepted_currencies'        => array(
			'type'           => 'multiselect',
			'title'          => __( 'Accepted currencies', 'woocommerce-wirecard-checkout-seamless' ),
			'options'        => get_woocommerce_currencies(),
			'default'        => array( 'EUR' ),
			'multiple'       => true,
			'select_buttons' => true
		),
		'woo_wcs_installment_min_amount'                 => array(
			'title'   => __( 'Minimum amount', 'woocommerce-wirecard-checkout-seamless' ),
			'default' => 150
		),
		'woo_wcs_installment_max_amount'                 => array(
			'title' => __( 'Maximum amount', 'woocommerce-wirecard-checkout-seamless' ),
			'default' => 3500
		),
		'woo_wcs_installment_min_basket_size'            => array(
			'title' => __( 'Minimum basket size', 'woocommerce-wirecard-checkout-seamless' )
		),
		'woo_wcs_installment_max_basket_size'            => array(
			'title' => __( 'Maximum basket size', 'woocommerce-wirecard-checkout-seamless' )
		)
	),
	'standardpayments'    => array(
		'wcs_ccard_enable'              => array(
			'title' => __( 'Credit Card', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_ccard-moto_enable'         => array(
			'title' => __( 'Credit Card - Mail Order and Telephone Order', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_maestro_enable'            => array(
			'title' => __( 'Maestro SecureCode', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_sofortueberweisung_enable' => array(
			'title' => __( 'SOFORT Banking', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_paypal_enable'             => array(
			'title' => __( 'PayPal', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_sepa-dd_enable'            => array(
			'title' => __( 'SEPA Direct Debit', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_invoice_enable'            => array(
			'title' => __( 'Invoice', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_invoiceb2b_enable'         => array(
			'title' => __( 'Invoice B2B', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		)
	),
	'bankingpayments'     => array(
		'wcs_eps_enable'                   => array(
			'title' => __( 'eps-Überweisung', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_idl_enable'                   => array(
			'title' => __( 'iDEAL', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_giropay_enable'               => array(
			'title' => __( 'giropay', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_tatrapay_enable'              => array(
			'title' => __( 'TatraPay', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_trustpay_enable'              => array(
			'title' => __( 'TrustPay', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_bancontact_mistercash_enable' => array(
			'title' => __( 'Bancontact', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_poli_enable'                  => array(
			'title' => __( 'POLi', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_przelewy24_enable'            => array(
			'title' => __( 'Przelewy24', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_ekonto_enable'                => array(
			'title' => __( 'eKonto', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_trustly_enable'               => array(
			'title' => __( 'Trustly', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		)
	),
	'alternativepayments' => array(
		'wcs_psc_enable'          => array(
			'title' => __( 'paysafecard', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_epay_bg_enable'      => array(
			'title' => __( 'ePay.bg', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_installment_enable'  => array(
			'title' => __( 'Installment', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_moneta_enable'       => array(
			'title' => __( 'moneta.ru', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		),
		'wcs_skrillwallet_enable' => array(
			'title' => __( 'Skrill Digital Wallet', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		)
	),
	'mobilepayments'      => array(
		'wcs_pbx_enable' => array(
			'title' => __( 'paybox', 'woocommerce-wirecard-checkout-seamless' ),
			'type'  => 'switch'
		)
	)
);