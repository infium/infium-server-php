<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('VendorInvoiceCreate');

$ui = new UserInterface();

$ui->setTitle('Invoice');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'vendorInvoiceCreateUI2.php');
$ui->setButtonLabel('Next');
$ui->setTitleBarColorNewWindow($titleBarColorVendorInvoice);

$ui->addField('BookingDate',NULL,'Booking date');
$valueVisibleData['BookingDate'] = date('Y-m-d');
$ui->addField('InvoiceDate',NULL,'Invoice date');
$ui->addSearchSelection('Vendor','Vendor',$baseUrl.'vendorInvoiceCreateSearchVendor.php');

$ui->addField('VendorReference',NULL,'Vendor reference');
$ui->addTable('Row');
$ui->addSearchSelection('Account','Account',$baseUrl.'vendorInvoiceCreateSearchAccount.php', 'Row');
$ui->addSearchSelection('Tax','Tax',$baseUrl.'vendorInvoiceCreateSearchTax.php', 'Row');
$ui->addField('Amount','Row','Amount', 'Decimal');

$ui->setVisibleData($valueVisibleData);

echo $ui->getObjectAsJSONString();
?>