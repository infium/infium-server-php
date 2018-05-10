<?php
/*
 * Copyright 2012-2017 Marcus Hammar
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
