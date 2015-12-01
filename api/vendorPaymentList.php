<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Payment list');

if (checkUserAccessBoolean('VendorPaymentListCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'vendorPaymentListCreateUI.php', NULL, $titleBarColorVendorPaymentList, NULL, 'f067', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentListView')){
	$ui->addLabelValueLink('View', NULL, 'GET', $baseUrl.'vendorPaymentListView.php', NULL, $titleBarColorVendorPaymentList, NULL, 'f06e', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentListReverse')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET', $baseUrl.'vendorPaymentListReverseUI.php', NULL, $titleBarColorVendorPaymentList, NULL, 'f0e2', $titleBarColorVendorPaymentList);
}

echo $ui->getObjectAsJSONString();
?>