<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Payment');

if (checkUserAccessBoolean('CustomerPaymentCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET',$baseUrl.'customerPaymentCreateUI.php', NULL, $titleBarColorCustomerPayment, NULL, 'f067', $titleBarColorCustomerPayment);
}

if (checkUserAccessBoolean('CustomerPaymentView')){
	$ui->addLabelValueLink('View', NULL, 'GET',$baseUrl.'customerPaymentView.php', NULL, $titleBarColorCustomerPayment, NULL, 'f06e', $titleBarColorCustomerPayment);
}

if (checkUserAccessBoolean('CustomerPaymentReverse')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET',$baseUrl.'customerPaymentReverseUI.php', NULL, $titleBarColorCustomerPayment, NULL, 'f0e2', $titleBarColorCustomerPayment);
}

echo $ui->getObjectAsJSONString();
?>