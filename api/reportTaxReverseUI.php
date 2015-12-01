<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportTax');

$ui = new UserInterface();

$ui->setTitle('Reverse');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'reportTaxReverseProcess.php');
$ui->setButtonLabel('Reverse');

$ui->addField('BookingDate',NULL,'Booking date (for reversal)');
$ui->addField('Number',NULL,'Document number');

$valueVisibleData['BookingDate'] = date('Y-m-d');
$ui->setVisibleData($valueVisibleData);

echo $ui->getObjectAsJSONString();
?>