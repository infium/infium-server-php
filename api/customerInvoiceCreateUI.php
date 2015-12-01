<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('CustomerInvoiceCreate');

$ui = new UserInterface();

$ui->setTitle('Invoice');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'customerInvoiceCreateUI2.php');
$ui->setButtonLabel('Next');
$ui->setTitleBarColorNewWindow($titleBarColorCustomerInvoice);

$ui->addField('BookingDate',NULL,'Booking date');
$ui->addSearchSelection('Customer','Customer',$baseUrl.'customerInvoiceCreateSearchCustomer.php');
$ui->addField('CustomerReference',NULL,'Customer reference');
$ui->addTable('Row');
$ui->addSearchSelection('ArticleID','Article',$baseUrl.'customerInvoiceCreateSearchArticle.php', 'Row');
$ui->addField('Quantity','Row','Quantity', 'Decimal');
$ui->addField('Price','Row','Price each', 'Decimal');

$valueVisibleData['BookingDate'] = date('Y-m-d');
$ui->setVisibleData($valueVisibleData);

echo $ui->getObjectAsJSONString();
?>