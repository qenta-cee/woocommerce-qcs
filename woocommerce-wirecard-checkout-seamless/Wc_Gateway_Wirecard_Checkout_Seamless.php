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
 * Main class for the wcs payment plugin
 */
class Wc_Gateway_Wirecard_Checkout_Seamless extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id = 'wirecard_checkout_seamless';
        $this->icon = WOOCOMMERCE_GATEWAY_WCS_URL . 'assets/images/icon.png';
        $this->has_fields = true;
        $this->method_title = __('Wirecard Checkout Seamless', 'woocommerce-wirecard-checkout-seamless');
        $this->method_description = __(
            'Wirecard - Your Full Service Payment Provider - Comprehensive solutions from one single source <br>' .
            'Wirecard is one of the world´s leading providers of outsourcing and white label solutions for electronic payment transactions.',
            'woocommerce-wirecard-checkout-seamless'
        );

        $this->init_form_fields();
        $this->init_settings();
    }

    function init_form_fields()
    {
    }

    function process_payment($order_id)
    {
    }

    /**
     * displays form for e.g. credit card data
     */
    function payment_fields()
    {
    }

    /**
     * validate input data from payment_fields
     *
     * @return boolean
     */
    function validate_fields()
    {
        // call wd_add_notice('text'); if you want to show an error message to user

        // return true if form validation ok
        // return false if validation fails
    }

    /**
     * validate response from server and edit payment informations
     */
    function confirm()
    {
    }
}
