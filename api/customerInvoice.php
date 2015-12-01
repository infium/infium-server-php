<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Invoice');

if (checkUserAccessBoolean('CustomerInvoiceCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'customerInvoiceCreateUI.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f067', $titleBarColorCustomerInvoice);
}

if (checkUserAccessBoolean('CustomerInvoiceView')){
	$ui->addLabelValueLink('View', NULL, 'GET', $baseUrl.'customerInvoiceView.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f06e', $titleBarColorCustomerInvoice);
}

if (checkUserAccessBoolean('CustomerInvoiceEmail')){
	$ui->addLabelValueLink('E-mail', NULL, 'GET', $baseUrl.'customerInvoiceEmailUI.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f1d8', $titleBarColorCustomerInvoice);
}

if (checkUserAccessBoolean('CustomerInvoiceReverse')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET', $baseUrl.'customerInvoiceReverseUI.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f0e2', $titleBarColorCustomerInvoice);
}

echo $ui->getObjectAsJSONString();
?>