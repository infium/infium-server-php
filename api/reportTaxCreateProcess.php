<?php
require('config.php');
require_once('classGeneralLedgerAccountBooking.php');

checkUserAccess('ReportTax');

try {

	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	
	validateDate($input['BookingDate']);
	validateDate($input['DateFrom']);
	validateDate($input['DateTo']);
		
	if ($input['DateFrom'] >= $input['DateTo']){
		throw new Exception('The dates does not add up. "From" needs to be earlier than "To".');
	}
	
	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');
	
	$documentNumber = nextDocumentNumber($pdo, 'TaxReport');
	
	$fields = dbPrepareExecute($pdo, 'SELECT Id, Field, Description FROM TaxField WHERE FromDate<=? AND ToDate>=? AND TaxRuleSet=? ORDER BY `Order` ASC', array($input['DateFrom'], $input['DateTo'], 'SE'));
	
	if (count($fields) == 0){
		throw new Exception('No tax fields could be found for the specified period. No report was created.');
	}
		
	$check = dbPrepareExecute($pdo, 'SELECT Number FROM TaxReport WHERE Active=? AND ((FromDate<=? AND ToDate>=?) OR (FromDate<=? AND ToDate>=?))', array(True, $input['DateFrom'], $input['DateFrom'], $input['DateTo'], $input['DateTo']));
	
	if (count($check) > 0){
		$t = '';
		foreach ($check as $r){
			$t .= ', '.$r['Number'];
		}
		throw new Exception('At least one tax report already exist somewhere in this tax period. The document number(s) is/are '.substr($t, 2).'. Nothing has been written to the database.');
	}
	
	dbPrepareExecute($pdo, 'INSERT INTO TaxReport (Number, BookingDate, FromDate, ToDate, Active, Reversal, Reversed) VALUES (?, ?, ?, ?, ?, ?, ?)', array($documentNumber, $input['BookingDate'], $input['DateFrom'], $input['DateTo'], True, False, False));
	
	$documentId = $pdo->lastInsertId();
	
	auditTrailLog($pdo, 'TaxReport', $pdo->lastInsertId(), 'INSERT');
		
	foreach ($fields as $row){
		$amountSum = 0.0;
		$calculationParts = dbPrepareExecute($pdo, 'SELECT TaxCode, MoveFromAccount, MoveFromAccountTaxCode, MoveToAccount, MoveToAccountTaxCode, ReversedSignInReport FROM TaxFieldCalculation WHERE FromDate<=? AND ToDate>=? AND TaxRuleSet=? AND Field=?', array($input['DateFrom'], $input['DateTo'], 'SE', $row['Field']));
		
		syslog(LOG_INFO, '$calculationParts = '.json_encode($calculationParts,JSON_PRETTY_PRINT));
		
		foreach ($calculationParts as $row2){
			$amount = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBookingRow WHERE TaxCode=? AND BookingDate>=? AND BookingDate<=?', array($row2['TaxCode'], $input['DateFrom'], $input['DateTo']));
			syslog(LOG_INFO, '$amount = '.json_encode($amount,JSON_PRETTY_PRINT));
			if (isset($amount[0]['Amount'])){
				
				
				if (($row2['MoveFromAccount'] != '')&&($row2['MoveFromAccountTaxCode'] != '')){
					if (isset($bookingRow[$row2['MoveFromAccount']][$row2['MoveFromAccountTaxCode']])){
						$bookingRow[$row2['MoveFromAccount']][$row2['MoveFromAccountTaxCode']] -= $amount[0]['Amount'];
					}else{
						$bookingRow[$row2['MoveFromAccount']][$row2['MoveFromAccountTaxCode']] = $amount[0]['Amount']*-1;
					}
				}
				
				if (($row2['MoveToAccount'] != '')&&($row2['MoveToAccountTaxCode'] != '')){
					if (isset($bookingRow[$row2['MoveToAccount']][$row2['MoveToAccountTaxCode']])){
						$bookingRow[$row2['MoveToAccount']][$row2['MoveToAccountTaxCode']] += $amount[0]['Amount'];
					}else{
						$bookingRow[$row2['MoveToAccount']][$row2['MoveToAccountTaxCode']] = $amount[0]['Amount'];
					}
				}
				
				if ($row2['ReversedSignInReport'] == True){
					$newAmount = $amount[0]['Amount'] * -1;
				}else{
					$newAmount = $amount[0]['Amount'];
				}
				$amountSum = $amountSum + $newAmount;
			}
		}

		syslog(LOG_INFO, '$amountSum = '.json_encode($amountSum,JSON_PRETTY_PRINT));
		
		
		dbPrepareExecute($pdo, 'INSERT INTO TaxReportRow (ParentId, Number, Field, Description, Amount) VALUES (?, ?, ?, ?, ?)', array($documentId, $documentNumber, $row['Field'], $row['Description'], $amountSum));
		
		auditTrailLog($pdo, 'TaxReportRow', $pdo->lastInsertId(), 'INSERT');
	}
	
	if (('2015-01-01' <= $input['DateFrom'])&&('2016-12-31' >= $input['DateTo'])){
		$customersProducts = dbPrepareExecute($pdo, 'SELECT DISTINCT TaxNumber FROM GeneralLedgerAccountBookingRow WHERE (TaxCode=\'SELL_EU_PRODUCT_25\' OR TaxCode=\'SELL_EU_PRODUCT_12\' OR TaxCode=\'SELL_EU_PRODUCT_6\' OR TaxCode=\'SELL_EU_PRODUCT_0\') AND BookingDate>=? AND BookingDate<=?', array($input['DateFrom'], $input['DateTo']));
		
		foreach ($customersProducts as $row){
			$amount = dbPrepareExecute($pdo, 'SELECT SUM(Amount) AS Amount FROM GeneralLedgerAccountBookingRow WHERE (TaxCode=\'SELL_EU_PRODUCT_25\' OR TaxCode=\'SELL_EU_PRODUCT_12\' OR TaxCode=\'SELL_EU_PRODUCT_6\' OR TaxCode=\'SELL_EU_PRODUCT_0\') AND BookingDate>=? AND BookingDate<=? AND TaxNumber=?', array($input['DateFrom'], $input['DateTo'], $row['TaxNumber']));
			
			dbPrepareExecute($pdo, 'INSERT INTO TaxReportRegionRow (ParentId, Number, Region, Type, ProductOrService, TaxNumber, Amount) VALUES (?, ?, ?, ?, ?, ?, ?)', array($documentId, $documentNumber, 'EU', 'Sell', 'Product', $row['TaxNumber'], $amount[0]['Amount']*-1));
			
			auditTrailLog($pdo, 'TaxReportRegionRow', $pdo->lastInsertId(), 'INSERT');
		}


		$customersServices = dbPrepareExecute($pdo, 'SELECT DISTINCT TaxNumber FROM GeneralLedgerAccountBookingRow WHERE (TaxCode=\'SELL_EU_SERVICE_25\' OR TaxCode=\'SELL_EU_SERVICE_12\' OR TaxCode=\'SELL_EU_SERVICE_6\' OR TaxCode=\'SELL_EU_SERVICE_0\') AND BookingDate>=? AND BookingDate<=?', array($input['DateFrom'], $input['DateTo']));
		
		foreach ($customersProducts as $row){
			$amount = dbPrepareExecute($pdo, 'SELECT SUM(Amount) AS Amount FROM GeneralLedgerAccountBookingRow WHERE (TaxCode=\'SELL_EU_SERVICE_25\' OR TaxCode=\'SELL_EU_SERVICE_12\' OR TaxCode=\'SELL_EU_SERVICE_6\' OR TaxCode=\'SELL_EU_SERVICE_0\') AND BookingDate>=? AND BookingDate<=? AND TaxNumber=?', array($input['DateFrom'], $input['DateTo'], $row['TaxNumber']));
			
			dbPrepareExecute($pdo, 'INSERT INTO TaxReportRegionRow (ParentId, Number, Region, Type, ProductOrService, TaxNumber, Amount) VALUES (?, ?, ?, ?, ?, ?, ?)', array($documentId, $documentNumber, 'EU', 'Sell', 'Service', $row['TaxNumber'], $amount[0]['Amount']*-1));
			
			auditTrailLog($pdo, 'TaxReportRegionRow', $pdo->lastInsertId(), 'INSERT');
		}
		
	}else{
		throw new Exception('Tax rules does not exist for calculation of sales breakdown.');
	}
	
	$booking = new GeneralLedgerAccountBooking();

	$booking->setDate($input['BookingDate']);
	$booking->setText('Tax report #' . $documentNumber);
	
	$bookingRowsExist = False;
	
	if (isset($bookingRow)){
		foreach ($bookingRow as $key1 => $value1){
			foreach ($value1 as $key2 => $value2){
				if ($bookingRow[$key1][$key2] > 0){
					$booking->addRowAdvanced($key1, '', '', '', $key2, $value2, 0, 'TaxReport', $documentNumber, '');
					$bookingRowsExist = True;
				}
				if ($bookingRow[$key1][$key2] < 0){
					$booking->addRowAdvanced($key1, '', '', '', $key2, 0, $value2*-1, 'TaxReport', $documentNumber, '');
					$bookingRowsExist = True;
				}
			}
		}
	}
	
	if ($bookingRowsExist == True){
		$booking->validateAndWriteToDatabase($pdo);
	}
	
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