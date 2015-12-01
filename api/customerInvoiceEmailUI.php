<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('CustomerInvoiceEmail');

$ui = new UserInterface();

$ui->setTitle('E-mail');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'customerInvoiceEmailProcess.php');
$ui->setButtonLabel('Send');
$ui->setTitleBarColorNewWindow($titleBarColorCustomerInvoice);

$ui->addField('DocumentNumber',NULL,'Document number');
$ui->addField('Email',NULL,'E-mail');

echo $ui->getObjectAsJSONString();
?>