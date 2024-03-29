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

 function changeWCSPayment(code) {
  var changer = document.getElementById('wcs_payment_method_changer');
  changer.value = code;
  qenta_wcs.build_iframe(code.toLowerCase());
}

const umlautMap = {
  '\u00dc': 'UE',
  '\u00c4': 'AE',
  '\u00d6': 'OE',
  '\u00fc': 'ue',
  '\u00e4': 'ae',
  '\u00f6': 'oe',
  '\u00df': 'ss',
}

// thx to https://stackoverflow.com/a/54346022
function replaceUmlaute(str) {
  return str
    .replace(/[\u00dc|\u00c4|\u00d6][a-z]/g, (a) => {
      const big = umlautMap[a.slice(0, 1)];
      return big.charAt(0) + big.charAt(1).toLowerCase() + a.slice(1);
    })
    .replace(new RegExp('['+Object.keys(umlautMap).join('|')+']',"g"),
      (a) => umlautMap[a]
    );
}

if (!Element.prototype.trigger) {
  Element.prototype.trigger = function (event) {
    var ev;

    try {
      if (this.dispatchEvent && CustomEvent) {
        ev = new CustomEvent(event, { detail: event + ' fired!' });
        this.dispatchEvent(ev);
      }
      else {
        throw "CustomEvent Not supported";
      }
    }
    catch (e) {
      if (document.createEvent) {
        ev = document.createEvent('HTMLEvents');
        ev.initEvent(event, true, true);

        this.dispatchEvent(event);
      }
      else {
        ev = document.createEventObject();
        ev.eventType = event;
        this.fireEvent('on' + event.eventType, event);
      }
    }
  }
}



if (!Element.prototype.matches) {
  Element.prototype.matches =
    Element.prototype.msMatchesSelector ||
    Element.prototype.webkitMatchesSelector;
}

if (!Element.prototype.closest) {
  Element.prototype.closest = function (s) {
    var el = this;

    do {
      if (Element.prototype.matches.call(el, s)) return el;
      el = el.parentElement || el.parentNode;
    } while (el !== null && el.nodeType === 1);
    return null;
  };
}

let qenta_wcs = {
  event_stop: function (event) {
    event.stopPropagation();
    event.stopImmediatePropagation();
    event.preventDefault();
    return false;
  },
  data: {},
  data_storage: new QentaCEE_DataStorage(),
  prepare_data: function (serializedArray) {
    for (let i = 0; i < serializedArray.length; i++) {
      this.data[serializedArray[i].name] = serializedArray[i].value
    }
  },
  get_data: function (which) {
    return (this.data.hasOwnProperty(which)) ? this.data[which] : false;
  },
  callback: function (response) {
    if (response.getStatus() === 0) {
      document.woo_wcs_ok = true;
      jQuery(function ($) {
        $(form).submit();
      });
      return true;
    }

    let errors = response.getErrors();

    errors = errors.map(function (error) {
      return "• " + error.consumerMessage;
    });

    Array.from(document.querySelectorAll('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message')).forEach(function (el) {
      el.remove();
    });

    var noticeGroup = document.createElement('div');
    noticeGroup.classList.add('woocommerce-NoticeGroup');
    noticeGroup.classList.add('woocommerce-NoticeGroup-checkout');

    var errorMessage = document.createElement('div');
    errorMessage.classList.add('woocommerce-error');
    errorMessage.innerHTML = errors.join('<br>');
    noticeGroup.appendChild(errorMessage);
    
    document.querySelector('form.woocommerce-checkout').prepend(noticeGroup);
    document.querySelector('form.woocommerce-checkout').classList.remove("processing");
    document.querySelector('form.woocommerce-checkout').querySelectorAll('.input-text, select, input[type=checkbox]').forEach(function (el) {
      el.blur();
    });
    document.querySelector('form.woocommerce-checkout').scrollIntoView({
      behavior: 'smooth'
    });
    document.getElementsByTagName('body')[0].trigger('checkout_error');

    return false;
  },
  build_iframe: function (type) {
    var containerName = 'woocommerce_wcs_iframe_' + type;
    var container = document.getElementById(containerName);
    if (container && container.querySelectorAll('iframe').length === 0) {
      switch (type) {
        case 'ccard':
          this.data_storage.buildIframeCreditCard(containerName, '100%', '170px');
          break;
        case 'ccard_moto':
          this.data_storage.buildIframeCreditCardMoto(containerName, '100%', '170px');
          break;
        case 'maestro':
          this.data_storage.buildIframeMaestro(containerName, '100%', '170px');
          break;
      }
      container.querySelector('iframe').addEventListener('load', (event) => {
        container.classList.remove('iframe-loading');
      });
    }
  },
  store_card: function (type) {
    let has_iframe = false;
    var ccard = document.getElementById('payment_method_wcs_CCARD');
    var ccard_moto = document.getElementById('payment_method_wcs_CCARD-MOTO');
    var maestro = document.getElementById('payment_method_wcs_MAESTRO');
    if ((type == 'CCARD' ? ccard
      : ((type == 'CCARD_MOTO')
        ? ccard_moto
        : maestro
      )
    ).parentNode.querySelectorAll('iframe').length > 0) {
      has_iframe = true;
    }

    let payment_information = null;

    if (!has_iframe) {
      payment_information = {
        pan: this.get_data(type + 'cardnumber').replace(/\s/g, ''),
        expirationMonth: this.get_data(type + 'expirationMonth'),
        expirationYear: this.get_data(type + 'expirationYear')
      };

      if (this.get_data(type + 'cardholder'))
        payment_information.cardholdername = replaceUmlaute(
          this.get_data(type + 'cardholder')
            .trim()
          )
          .replace(/[^a-z\ ]/gi, '')
          .replace(/\ +/, ' ');
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
    let payment_information = {
      bankAccountIban: this.get_data('bankAccountIban'),
      accountOwner: this.get_data('accountOwner'),
      bankBic: this.get_data('bankBic')
    };
    this.data_storage.storeSepaDdInformation(payment_information, qenta_wcs.callback);
  },
  store_paybox: function () {
    let payment_information = {
      payerPayboxNumber: this.get_data('payerPayboxNumber').replace(/\s/g, '')
    };
    this.data_storage.storePayboxInformation(payment_information, qenta_wcs.callback);
  },
  store_giropay: function () {
    let payment_information = {
      bankAccount: this.get_data('woo_wcs_giropay_accountnumber').replace(/\s/g, ''),
      bankNumber: this.get_data('woo_wcs_giropay_banknumber').replace(/\s/g, '')
    };
    if (this.get_data('woo_wcs_giropay_accountowner'))
      payment_information.accountOwner = this.get_data('woo_wcs_giropay_accountowner');

    this.data_storage.storeGiropayInformation(payment_information, qenta_wcs.callback);
  }
}

var form = document.querySelector('form.woocommerce-checkout');

form.addEventListener('submit', (event) => {
  var ccard = document.getElementById('payment_method_wcs_CCARD');
  var ccard_moto = document.getElementById('payment_method_wcs_CCARD-MOTO');
  var maestro = document.getElementById('payment_method_wcs_MAESTRO');
  var sepa_dd = document.getElementById('payment_method_wcs_SEPA-DD');
  var paybox = document.getElementById('payment_method_wcs_PBX');
  var giropay = document.getElementById('payment_method_wcs_GIROPAY');

  var pmArray = Array.from(document.querySelectorAll('input.input-radio.qcs_payment_method_list'));

  var pmSelected = !!pmArray.filter(function (pm) {
    return pm.checked;
  }).length;

  if(pmSelected === false) {
    event.preventDefault();
    return false;
  }

  if (document.woo_wcs_ok) {
    return true;
  }

  let serialized_array = [];
  document.querySelector('form.woocommerce-checkout').querySelector('input:checked').parentNode.querySelectorAll('fieldset input').forEach(function (element) {
    if (element.getAttribute('name') != null)
      serialized_array.push({ name: element.getAttribute('name'), value: element.value });
  });

  qenta_wcs.prepare_data(serialized_array);

  if (ccard && ccard.checked) {
    qenta_wcs.store_card('CCARD');
    qenta_wcs.event_stop(event);
  }
  else if (ccard_moto && ccard_moto.checked) {
    qenta_wcs.store_card('CCARD_MOTO');
    qenta_wcs.event_stop(event);
  }
  else if (maestro && maestro.checked) {
    qenta_wcs.store_card('MAESTRO');
    qenta_wcs.event_stop(event);
  }
  else if (sepa_dd && sepa_dd.checked) {
    qenta_wcs.store_sepadd();
    qenta_wcs.event_stop(event);
  }
  else if (giropay && giropay.checked) {
    qenta_wcs.store_giropay();
    qenta_wcs.event_stop(event);
  }

});

setTimeout(() => {
  try {
    qenta_wcs.build_iframe('ccard');
  }
  catch (e) { }
}, 3500);
