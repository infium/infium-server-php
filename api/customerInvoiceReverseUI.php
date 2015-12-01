<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('CustomerInvoiceReverse');

$ui = new UserInterface();

$ui->setTitle('Reverse');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'customerInvoiceReverseProcess.php');
$ui->setButtonLabel('Reverse');
$ui->setTitleBarColorNewWindow($titleBarColorCustomerInvoice);

$ui->addField('BookingDate',NULL,'Booking date (for reversal)');
$ui->addField('DocumentNumber',NULL,'Document number');

$valueVisibleData['BookingDate'] = date('Y-m-d');
$ui->setVisibleData($valueVisibleData);

echo $ui->getObjectAsJSONString();
?>