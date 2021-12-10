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

jQuery(function ($) {

    var ccard = '#payment_method_wcs_CCARD',
        ccard_moto = '#payment_method_wcs_CCARD-MOTO',
        maestro = '#payment_method_wcs_MAESTRO',
        sepa_dd = '#payment_method_wcs_SEPA-DD',
        paybox = '#payment_method_wcs_PBX',
        giropay = '#payment_method_wcs_GIROPAY',
        form = "form.woocommerce-checkout"

    document.addEventListener("DOMContentLoaded", function(event) {
        if ($(ccard).parent().find("div#woocommerce_wcs_iframe_ccard").length > 0)
            qenta_wcs.build_iframe('ccard');
        if ($(ccard_moto).parent().find("div#woocommerce_wcs_iframe_ccard_moto").length > 0)
            qenta_wcs.build_iframe('ccard_moto');
        if ($(maestro).parent().find("div#woocommerce_wcs_iframe_maestro").length > 0)
            qenta_wcs.build_iframe('maestro');
    });

    $(form).on('submit', function (event) {
        if ($('input[name=woo_wcs_ok]', this).length > 0)
            return true;

        var serialized_array = [];
        $(this).find('input:checked').parent().find('fieldset input').each(function () {
            if ($(this).attr('name') != null)
                serialized_array.push({ name : $(this).attr('name'), value : $(this).val()});
        });

        qenta_wcs.prepare_data(serialized_array);

        if ($(ccard).length > 0 && $(ccard).is(':checked')) {
            qenta_wcs.store_card('CCARD');

            qenta_wcs.event_stop(event);
        }
        else if ($(ccard_moto).length > 0 && $(ccard_moto).is(':checked')) {
            qenta_wcs.store_card('CCARD_MOTO');

            qenta_wcs.event_stop(event);
        }
        else if ($(maestro).length > 0 && $(maestro).is(':checked')) {
            qenta_wcs.store_card('MAESTRO');

            qenta_wcs.event_stop(event);
        }
        else if ($(sepa_dd).length > 0 && $(sepa_dd).is(':checked')) {
            qenta_wcs.store_sepadd();

            qenta_wcs.event_stop(event);
        }
        else if($(paybox).length > 0 && $(paybox).is(':checked')) {
            qenta_wcs.store_paybox();

            qenta_wcs.event_stop(event);
        }
        else if($(giropay).length > 0 && $(giropay).is(':checked')) {
            qenta_wcs.store_giropay();
            qenta_wcs.event_stop(event);
        }


    });



    var qenta_wcs = {
        event_stop : function(event){
            event.stopPropagation();
            event.stopImmediatePropagation();
            event.preventDefault();
            return false;
        },
        data: {},
        data_storage: new WirecardCEE_DataStorage(),
        prepare_data: function (serializedArray) {
            for (var i = 0; i < serializedArray.length; i++) {
                this.data[serializedArray[i].name] = serializedArray[i].value
            }
        },
        get_data: function (which) {
            return (this.data.hasOwnProperty(which)) ? this.data[which] : false;
        },
        callback: function (response) {

            if (response.getStatus() === 0) {
                $(form).append('<input type="hidden" name="woo_wcs_ok" value="bla">');
                $(form).submit();
                return true;
            }

            var errors = response.getErrors();

            errors = errors.map(function (error) {
                return "&bull; " + error.consumerMessage;
            });

            $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
            $(form).prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout"><div class="woocommerce-error">' + errors.join("<br>") + '</div></div>');
            $(form).removeClass('processing').unblock();
            $(form).find('.input-text, select, input:checkbox').blur();
            $('html,body').animate({
                scrollTop: ( $(form).offset().top - 100 )
            }, 1000);
            $(document.body).trigger('checkout_error');

            return false;
        },
        build_iframe: function (type) {
            switch (type) {
                case 'ccard' :
                    this.data_storage.buildIframeCreditCard('woocommerce_wcs_iframe_ccard', '100%', '200px');
                    break;
                case 'ccard_moto' :
                    this.data_storage.buildIframeCreditCardMoto('woocommerce_wcs_iframe_ccard_moto', '100%', '200px');
                    break;
                case 'maestro' :
                    this.data_storage.buildIframeMaestro('woocommerce_wcs_iframe_maestro', '100%', '200px');
                    break;
            }
        },
        store_card: function (type) {
            var has_iframe = false;

            if ((type == 'CCARD' ? $(ccard)
                        : ((type == 'CCARD_MOTO')
                            ? $(ccard_moto)
                            : $(maestro)
                    )
                ).parent().find('iframe').length > 0) {
                has_iframe = true;
            }

            var payment_information = null;

            if (!has_iframe) {
                payment_information = {
                    pan: this.get_data(type + 'cardnumber').replace(/\s/g, ''),
                    expirationMonth: this.get_data(type + 'expirationMonth'),
                    expirationYear: this.get_data(type + 'expirationYear')
                };

                if (this.get_data(type + 'cardholder'))
                    payment_information.cardholdername = this.get_data(type + 'cardholder');
                if (this.get_data(type + 'issueMonth'))
                    payment_information.issueMonth = this.get_data(type + 'issueMonth');
                if (this.get_data(type + 'issueYear'))
                    payment_information.issueYear = this.get_data(type + 'issueYear');
                if (this.get_data(type + 'issueNumber'))
                    payment_information.issueNumber = this.get_data(type + 'issueNumber');
                if (this.get_data(type + 'cvc'))
                    payment_information.cardverifycode = this.get_data(type + 'cvc');
            }


            switch (type) {
                case "CCARD":
                    this.data_storage.storeCreditCardInformation(payment_information, qenta_wcs.callback);
                    break;
                case "CCARD_MOTO":
                    this.data_storage.storeCreditCardMotoInformation(payment_information, qenta_wcs.callback);
                    break;
                case "MAESTRO":
                    this.data_storage.storeMaestroInformation(payment_information, qenta_wcs.callback);
                    break;
            }

        },
        store_sepadd: function () {
            var payment_information = {
                bankAccountIban: this.get_data('bankAccountIban'),
                accountOwner: this.get_data('accountOwner'),
                bankBic: this.get_data('bankBic')
            };
            this.data_storage.storeSepaDdInformation(payment_information, qenta_wcs.callback);
        },
        store_paybox: function () {
            var payment_information = {
                payerPayboxNumber: this.get_data('payerPayboxNumber').replace(/\s/g, '')
            };
            this.data_storage.storePayboxInformation(payment_information, qenta_wcs.callback);
        },
        store_giropay: function () {
            var payment_information = {
                bankAccount: this.get_data('woo_wcs_giropay_accountnumber').replace(/\s/g, ''),
                bankNumber: this.get_data('woo_wcs_giropay_banknumber').replace(/\s/g, '')
            };
            if (this.get_data('woo_wcs_giropay_accountowner'))
                payment_information.accountOwner = this.get_data('woo_wcs_giropay_accountowner');

            this.data_storage.storeGiropayInformation(payment_information, qenta_wcs.callback);
        }
    }

});
