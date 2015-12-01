<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Invoice');

if (checkUserAccessBoolean('VendorInvoiceCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET',$baseUrl.'vendorInvoiceCreateUI.php', NULL, $titleBarColorVendorInvoice, NULL, 'f067', $titleBarColorVendorInvoice);
}

if (checkUserAccessBoolean('VendorInvoiceView')){
	$ui->addLabelValueLink('View', NULL, 'GET',$baseUrl.'vendorInvoiceView.php', NULL, $titleBarColorVendorInvoice, NULL, 'f06e', $titleBarColorVendorInvoice);
}

if (checkUserAccessBoolean('VendorInvoiceReverse')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET',$baseUrl.'vendorInvoiceReverseUI.php', NULL, $titleBarColorVendorInvoice, NULL, 'f0e2', $titleBarColorVendorInvoice);
}

echo $ui->getObjectAsJSONString();
?>