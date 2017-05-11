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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
</head>
<body>
<h3><?php echo __( 'You will be redirected shortly', 'woocommerce-wirecard-checkout-seamless' ); ?></h3>
<p><?php echo __( 'If not, please click ', 'woocommerce-wirecard-checkout-seamless' ); ?>
	<a href="#" onclick="iframeBreakout()"><?php echo __( 'here', 'woocommerce-wirecard-checkout-seamless' ); ?></a>
</p>
<form method="POST" name="redirectForm" action="<?php echo $url; ?>" target="_parent">
	<input type="hidden" name="redirected" value="1"/>
	<?php
	foreach ( $_REQUEST as $k => $v ) {
		printf( '<input type="hidden" name="%s" value="%s" />', htmlspecialchars( $k ), htmlspecialchars( $v ) );
	}
	?>
</form>
<script type="text/javascript">
	function iframeBreakout() {
		document.redirectForm.submit();
	}

	iframeBreakout();
</script>
</body>
</html>