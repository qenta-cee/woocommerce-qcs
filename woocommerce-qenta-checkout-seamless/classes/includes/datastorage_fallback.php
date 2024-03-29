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
$response = isset( $_POST['response'] ) ? sanitize_text_field($_POST['response']) : '';
// arguments for wp_register_script to allow a no-dependency inline script and add it to the header
wp_register_script( 'setResponseJS', '' );
$jsSetReponse = <<<JSCODE
function setResponse(response) {
  if (typeof parent.QentaCEE_Fallback_Request_Object == 'object') {
    parent.QentaCEE_Fallback_Request_Object.setResponseText(response);
  }
  else {
    console.log('Not a valid fallback call.');
  }
}
JSCODE;
wp_enqueue_script( 'setResponseJS' );
wp_add_inline_script( 'setResponseJS', $jsSetReponse );
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
</head>
<body onload='setResponse("<?php echo esc_attr($response); ?>");'>
</body>
</html>
