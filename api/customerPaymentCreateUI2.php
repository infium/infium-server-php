<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('CustomerPaymentCreate');

$ui = new UserInterface();

$ui->setTitle('Payment');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'customerPaymentCreateProcess.php');
$ui->setButtonLabel('Create');
$ui->setTitleBarColorNewWindow($titleBarColorCustomerPayment);

$ui->addField('Date',NULL,'Booking date');
$ui->addTable('Row');
$ui->addField('PaymentReference','Row','Payment reference');
$ui->addField('Amount','Row','Amount', 'Decimal');

$visibleData['Date'] = date('Y-m-d');

if (isset(json_decode(file_get_contents('php://input'), TRUE)['HiddenData'])){
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];
}else{
	$inputHidden = NULL;
}

$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

foreach ($inputVisible as $key => $value){
	if ($value == True){
		$index = substr($key, 5);
		
		$newRow['PaymentReference'] = $inputHidden['Payment'][$index]['ClearingReference'];
		$newRow['Amount'] = $inputHidden['Payment'][$index]['Amount'];
		
		$visibleData['Row'][] = $newRow;
	}
}

$ui->setVisibleData($visibleData);

echo $ui->getObjectAsJSONString();
?>