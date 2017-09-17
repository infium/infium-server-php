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

if (!(checkUserAccessBoolean('ReportProfitAndLoss')||checkUserAccessBoolean('ReportBalanceSheet'))){
	sendMessageToClient('User has no access to this');
	exit();
}

$pdo = createPdo();

$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

$ui = new UserInterface();

$ui->setTitle('Journal voucher #' . $_GET['Number']);

$header = dbPrepareExecute($pdo, 'SELECT BookingDate, DocumentType, DocumentTypeNumber FROM GeneralLedgerAccountBooking WHERE Number=?', array($_GET['Number']));

$ui->addLabelValueLink('Booking date', $header[0]['BookingDate']);

switch ($header[0]['DocumentType']) {
	case 'CustomerInvoice':
		$ui->addLabelValueLink('Origin', 'Customer invoice #'.$header[0]['DocumentTypeNumber'], 'GET', $baseUrl.'customerInvoiceViewDocument.php?Number='.$header[0]['DocumentTypeNumber'], NULL, $titleBarColorReportGeneralLedger);
		break;
	
	case 'CustomerPayment':
		$ui->addLabelValueLink('Origin', 'Customer payment #'.$header[0]['DocumentTypeNumber'], 'GET', $baseUrl.'customerPaymentViewDocument.php?Number='.$header[0]['DocumentTypeNumber'], NULL, $titleBarColorReportGeneralLedger);
		break;
	
	case 'VendorInvoice':
		$ui->addLabelValueLink('Origin', 'Vendor invoice #'.$header[0]['DocumentTypeNumber'], 'GET', $baseUrl.'vendorInvoiceViewDocument.php?Number='.$header[0]['DocumentTypeNumber'], NULL, $titleBarColorReportGeneralLedger);
		break;
	
	case 'VendorPaymentCompleted':
		$ui->addLabelValueLink('Origin', 'Vendor payment #'.$header[0]['DocumentTypeNumber'], 'GET', $baseUrl.'vendorPaymentCompletedViewDocument.php?Number='.$header[0]['DocumentTypeNumber'], NULL, $titleBarColorReportGeneralLedger);
		break;    
}

$rows = dbPrepareExecute($pdo, 'SELECT AccountNumber, Debit, Credit FROM GeneralLedgerAccountBookingRow WHERE Number=? ORDER BY Id', array($_GET['Number']));

$i = 0;

foreach ($rows as $row){
	
	$accountDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerAccount WHERE AccountNumber=? AND Year=? ORDER BY Year DESC', array($row['AccountNumber'], $_GET['Year']));
	$i++;
	$ui->addLabelHeader('Row '.$i);
	$ui->addLabelValueLink('Account', $row['AccountNumber'] . ' ' . $accountDescription[0]['Description']);
	$ui->addLabelValueLink('Debit', decimalFormat($row['Debit']));
	$ui->addLabelValueLink('Credit', decimalFormat($row['Credit']));
}

$pdo->exec('ROLLBACK');

echo $ui->getObjectAsJSONString();
?>