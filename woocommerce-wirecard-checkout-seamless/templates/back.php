<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
</head>
<body>
<h3><?php __( 'You will be redirected shortly' , 'woocommerce-wirecard-checkout-seamless' ); ?></h3>
<p><?php __( 'If not, please click ', 'woocommerce-wirecard-checkout-seamless' ); ?><a href="#" onclick="iframeBreakout()"><?php __( 'here', 'woocommerce-wirecard-checkout-seamless' ); ?></a></p>
<form method="POST" name="redirectForm" action="<?php echo $url; ?>" target="_parent">
	<input type="hidden" name="redirected" value="1" />
	<?php
	foreach ($_REQUEST as $k => $v)
	{
		printf('<input type="hidden" name="%s" value="%s" />', htmlspecialchars($k), htmlspecialchars($v));
	}
	?>
</form>
<script type="text/javascript">
	function iframeBreakout()
	{
		document.redirectForm.submit();
	}

	iframeBreakout();
</script>
</body>
</html>