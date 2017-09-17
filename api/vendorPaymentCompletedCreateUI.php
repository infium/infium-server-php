<?php
/*
 * Copyright 2012-2017 Infium AB
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

checkUserAccess('VendorPaymentCompletedCreate');

$pdo = createPdo();

$currencyResult = dbPrepareExecute($pdo, 'SELECT Value FROM Property WHERE Property = ?', array('Currency'));
$currency = $currencyResult[0]['Value'];

$ui = new UserInterface();

$ui->setTitle('Payment completed');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'vendorPaymentCompletedCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('Date',NULL,'Booking date');
$value['Date'] = date('Y-m-d');

$ui->addLabelHeader('Unpaid invoices');

$stmt = $pdo->prepare('SELECT Id, PreviousRowId, AmountRemaining FROM VendorPaymentListRow WHERE AmountRemaining != 0 ORDER BY DueDate ASC, Id ASC');
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($results as $row){

	$stmt2 = $pdo->prepare('SELECT Id, DueDate, InternalName, PaymentReference, VendorId, AmountGross FROM VendorInvoice WHERE Id=?');
	$stmt2->execute(array($row['PreviousRowId']));
	$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
	
	$ui->addLabelTrueFalse($row['Id'],"Vendor: ".$results2[0]['InternalName']."\nInvoice number: ".$results2[0]['PaymentReference']."\nAmount: ".decimalFormat($row['AmountRemaining']).' '.$currency."\nDueDate: ".$results2[0]['DueDate']);
	$value[$row['Id']] = False;
}

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>