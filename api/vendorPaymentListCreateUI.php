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

checkUserAccess('VendorPaymentListCreate');

$pdo = createPdo();

$currencyResult = dbPrepareExecute($pdo, 'SELECT Value FROM Property WHERE Property = ?', array('Currency'));
$currency = $currencyResult[0]['Value'];

$ui = new UserInterface();

$ui->setTitle('Payment list');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'vendorPaymentListCreateProcess.php');
$ui->setButtonLabel('Create');
$ui->setTitleBarColorNewWindow($titleBarColorVendorPaymentList);

$ui->addField('BookingDate',NULL,'Booking date');

$stmt = $pdo->prepare('SELECT Id, DueDate, InternalName, PaymentReference, VendorId, AmountGrossRemaining FROM VendorInvoice WHERE AmountGrossRemaining != 0 ORDER BY Number ASC');
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($results as $row){
	$ui->addLabelTrueFalse('Row'.$row['Id'], "Vendor: ".$row['InternalName']."\nInvoice number: ".$row['PaymentReference']."\nAmount: ".decimalFormat($row['AmountGrossRemaining']).' '.$currency."\nDueDate: ".$row['DueDate']);
	$value['Row'.$row['Id']] = False;
}

$value['BookingDate'] = date('Y-m-d');
$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>
