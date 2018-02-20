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
require_once('classGeneralLedgerAccountBooking.php');

checkUserAccess('ReportTax');

try {

	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();

	validateDate($input['BookingDate']);
	validateTaxReportDocumentNumber($pdo, $input['Number']);

	$pdo->exec('START TRANSACTION');

	$documentNumber = nextDocumentNumber($pdo, 'TaxReport');

	$check = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumTaxReports FROM TaxReport WHERE Active=? AND Number=?', array(True, $input['Number']));

	if ($check[0]['NumTaxReports'] != 1){
		throw new Exception('No active tax report with number '.$input['Number'].' could be found.');
	}

	$previousTaxReport = dbPrepareExecute($pdo, 'SELECT Id, FromDate, ToDate FROM TaxReport WHERE Number=?', array($input['Number']));

	dbPrepareExecute($pdo, 'INSERT INTO TaxReport (Number, BookingDate, FromDate, ToDate, Active, Reversal, Reversed) VALUES (?, ?, ?, ?, ?, ?, ?)', array($documentNumber, $input['BookingDate'], $previousTaxReport[0]['FromDate'], $previousTaxReport[0]['ToDate'], False, True, False));

	$documentId = $pdo->lastInsertId();

	auditTrailLog($pdo, 'TaxReport', $pdo->lastInsertId(), 'INSERT');

	$previousTaxReportRow = dbPrepareExecute($pdo, 'SELECT Field, Description, Amount FROM TaxReportRow WHERE ParentId=?', array($previousTaxReport[0]['Id']));

	foreach ($previousTaxReportRow as $row){
		dbPrepareExecute($pdo, 'INSERT INTO TaxReportRow (ParentId, Number, Field, Description, Amount) VALUES (?, ?, ?, ?, ?)', array($documentId, $documentNumber, $row['Field'], $row['Description'], $row['Amount']*-1));
		auditTrailLog($pdo, 'TaxReportRow', $pdo->lastInsertId(), 'INSERT');
	}

	$previousTaxReportRegionRow = dbPrepareExecute($pdo, 'SELECT Region, Type, ProductOrService, TaxNumber, Amount FROM TaxReportRegionRow WHERE ParentId=?', array($previousTaxReport[0]['Id']));

	foreach ($previousTaxReportRegionRow as $row){
		dbPrepareExecute($pdo, 'INSERT INTO TaxReportRegionRow (ParentId, Number, Region, Type, ProductOrService, TaxNumber, Amount) VALUES (?, ?, ?, ?, ?, ?, ?)', array($documentId, $documentNumber, $row['Region'], $row['Type'], $row['ProductOrService'], $row['TaxNumber'], $row['Amount']*-1));
		auditTrailLog($pdo, 'TaxReportRegionRow', $pdo->lastInsertId(), 'INSERT');
	}


	$generalLedgerRow = dbPrepareExecute($pdo, 'SELECT AccountNumber, Amount FROM GeneralLedgerAccountBookingRow WHERE DocumentType=? AND DocumentTypeNumber=?', array('TaxReport', $input['Number']));


	$booking = new GeneralLedgerAccountBooking();

	$booking->setDate($input['BookingDate']);
	$booking->setText('Tax report # ' . $documentNumber);

	$bookingRowsExist = False;

	if (count($generalLedgerRow) > 0){
		foreach ($generalLedgerRow as $row){
			$amount = $row['Amount']*-1;
			if ($amount > 0){
				$booking->addRowAdvanced($row['AccountNumber'], '', '', '', 'VAT_REPORTED_REVERSED', $amount, 0, 'TaxReport', $documentNumber, '');
				$bookingRowsExist = True;
			}
			if ($amount < 0){
				$booking->addRowAdvanced($row['AccountNumber'], '', '', '', 'VAT_REPORTED_REVERSED', 0, $amount*-1, 'TaxReport', $documentNumber, '');
				$bookingRowsExist = True;
			}
		}
	}

	if ($bookingRowsExist == True){
		$booking->validateAndWriteToDatabase($pdo);
	}

	dbPrepareExecute($pdo, 'UPDATE TaxReport SET Active=?, Reversed=? WHERE Number=?', array(False, True, $input['Number']));

	auditTrailLog($pdo, 'TaxReport', $previousTaxReport[0]['Id'], 'UPDATE');

	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Tax report created #'.$documentNumber;

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>
