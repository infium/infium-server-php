<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('VendorPaymentCompletedReverse');

$ui = new UserInterface();

$ui->setTitle('Reverse');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'vendorPaymentCompletedReverseProcess.php');
$ui->setButtonLabel('Reverse');

$ui->addField('BookingDate',NULL,'Booking date (for reversal)');
$ui->addField('DocumentNumber',NULL,'Document number');

$valueVisibleData['BookingDate'] = date('Y-m-d');
$ui->setVisibleData($valueVisibleData);

echo $ui->getObjectAsJSONString();
?>