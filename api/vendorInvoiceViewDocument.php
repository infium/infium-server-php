<?php
require('config.php');
require('functionRenderVendorInvoice.php');

if (!(checkUserAccessBoolean('VendorInvoiceView'))){
	sendMessageToClient('User has not access to this');
	exit();
}

header('Content-type: text/html');
header('Show-Print-Icon: true');

echo renderVendorInvoice($_GET['Number']);
?>