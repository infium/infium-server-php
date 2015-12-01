<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Vendor');

if (checkUserAccessBoolean('VendorInvoiceCreate')||checkUserAccessBoolean('VendorInvoiceReverse')||checkUserAccessBoolean('VendorInvoiceView')){
	$ui->addLabelValueLink('Invoice', NULL, 'GET', $baseUrl.'vendorInvoice.php', NULL, $titleBarColorVendorInvoice, NULL, 'f0f6', $titleBarColorVendorInvoice);
}

if (checkUserAccessBoolean('VendorPaymentListCreate')||checkUserAccessBoolean('VendorPaymentListReverse')||checkUserAccessBoolean('VendorPaymentListView')){
	$ui->addLabelValueLink('Payment list', NULL, 'GET', $baseUrl.'vendorPaymentList.php', NULL, $titleBarColorVendorPaymentList, NULL, 'f0cb', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentCompletedCreate')||checkUserAccessBoolean('VendorPaymentCompletedReverse')||checkUserAccessBoolean('VendorPaymentCompletedView')){
	$ui->addLabelValueLink('Payment completed', NULL, 'GET', $baseUrl.'vendorPaymentCompleted.php', NULL, $titleBarColorVendorPaymentCompleted, NULL, 'f153', $titleBarColorVendorPaymentCompleted);
}

echo $ui->getObjectAsJSONString();
?>