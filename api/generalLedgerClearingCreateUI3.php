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

checkUserAccess('GeneralLedgerClearingCreate');

$pdo = createPdo();

$ui = new UserInterface();

$ui->setTitle('Clearing');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'generalLedgerClearingCreateProcess.php');
$ui->setButtonLabel('Create');
$ui->setTitleBarColorNewWindow($titleBarColorGeneralLedgerClearing);

$ui->addField('ClearingDate',NULL,'Clearing date');

$bookingRows = dbPrepareExecute($pdo, 'SELECT Id, BookingDate, Text, Amount FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL AND AccountNumber=? AND SubAccountNumber=? ORDER BY BookingDate ASC, Id ASC', array($_GET['AccountNumber'], $_GET['SubAccountNumber']));

foreach($bookingRows as $row){	
	$ui->addLabelTrueFalse('Row'.$row['Id'], 'Date: '.$row['BookingDate']."\nText: ".$row['Text']."\nAmount: ".decimalFormat($row['Amount']));
	$value['Row'.$row['Id']] = False;
}

$value['ClearingDate'] = date('Y-m-d');
$ui->setVisibleData($value);

$hiddenData['AccountNumber'] = $_GET['AccountNumber'];
$hiddenData['SubAccountNumber'] = $_GET['SubAccountNumber'];
$ui->setHiddenData($hiddenData);

echo $ui->getObjectAsJSONString();
?>