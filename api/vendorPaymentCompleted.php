<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Payment completed');

if (checkUserAccessBoolean('VendorPaymentCompletedCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'vendorPaymentCompletedCreateUI.php', NULL, $titleBarColorVendorPaymentCompleted, NULL, 'f067', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentCompletedView')){
	$ui->addLabelValueLink('View', NULL, 'GET', $baseUrl.'vendorPaymentCompletedView.php', NULL, $titleBarColorVendorPaymentCompleted, NULL, 'f06e', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentCompletedReverse')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET', $baseUrl.'vendorPaymentCompletedReverseUI.php', NULL, $titleBarColorVendorPaymentCompleted, NULL, 'f0e2', $titleBarColorVendorPaymentList);
}

echo $ui->getObjectAsJSONString();
?>