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

checkUserAccess('ReportBalanceSheet');

$pdo = createPdo();

$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

$ui = new UserInterface();

$AccountYear = substr($_GET['Date'],0,4);

$stmt = $pdo->prepare("SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?");
$stmt->execute(array($AccountYear, $_GET['AccountNumber']));
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui->setTitle($_GET['AccountNumber'].' '.$results[0]['Description'].' '.$_GET['Date']);

$SumResult = 0;

$stmt2 = $pdo->prepare('SELECT Number, BookingDate, Text, Amount, DocumentType, DocumentTypeNumber FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? AND BookingDate<=? AND BookingDate<=? AND (ClearingDate IS NULL OR ClearingDate > ?) ORDER BY BookingDate, Id');
$stmt2->execute(array($_GET['AccountNumber'], $_GET['Date'], $_GET['Date'], $_GET['Date']));
$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($results2 as $row){

	switch ($row['DocumentType']) {
		case 'CustomerInvoice':
			$url = $baseUrl.'customerInvoiceViewDocument.php?Number='.$row['DocumentTypeNumber'];
			break;

		case 'CustomerPayment':
			$url = $baseUrl.'customerPaymentViewDocument.php?Number='.$row['DocumentTypeNumber'];
			break;

		case 'VendorInvoice':
			$url = $baseUrl.'vendorInvoiceViewDocument.php?Number='.$row['DocumentTypeNumber'];
			break;

		case 'VendorPaymentCompleted':
			$url = $baseUrl.'vendorPaymentCompletedViewDocument.php?Number='.$row['DocumentTypeNumber'];
			break;

		default:
			$url = $baseUrl.'reportGeneralLedgerRowProcess.php?Year='.$AccountYear.'&Number='.$row['Number'];
	}

	$SumResult += $row['Amount'];
	$Amount = number_format($row['Amount'], 2, '.', ',');
	$ui->addLabelValueLink($row['BookingDate'].": ".$row['Text'], $Amount, 'GET', $url, NULL, $titleBarColorReportBalanceSheet);
}

$SumAmount = number_format($SumResult, 2, '.', ',');

$ui->addLabelValueLink('Sum', $SumAmount);

$pdo->exec('ROLLBACK');

echo $ui->getObjectAsJSONString();
?>
