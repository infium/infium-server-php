<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Customer');

if (checkUserAccessBoolean('CustomerInvoiceCreate')||checkUserAccessBoolean('CustomerInvoiceEmail')||checkUserAccessBoolean('CustomerInvoiceReverse')||checkUserAccessBoolean('CustomerInvoiceView')){
	$ui->addLabelValueLink('Invoice', NULL, 'GET',$baseUrl.'customerInvoice.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f0f6', $titleBarColorCustomerInvoice);
}

if (checkUserAccessBoolean('CustomerPaymentCreate')||checkUserAccessBoolean('CustomerPaymentReverse')||checkUserAccessBoolean('CustomerPaymentView')){
	$ui->addLabelValueLink('Payment', NULL, 'GET',$baseUrl.'customerPayment.php', NULL, $titleBarColorCustomerPayment, NULL, 'f153', $titleBarColorCustomerPayment);
}

echo $ui->getObjectAsJSONString();
?>