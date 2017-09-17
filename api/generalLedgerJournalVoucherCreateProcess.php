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
require('classGeneralLedgerAccountBooking.php');

checkUserAccess('GeneralLedgerJournalVoucherCreate');

$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

try {

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');

	$booking = new GeneralLedgerAccountBooking();

	$booking->setDate($input['Date']);
	$booking->setText($input['Text']);

	foreach ($input['Row'] as $bookingRow){
		
		if (($bookingRow['Debit'] != '')||($bookingRow['Credit'] != '')||($bookingRow['Account'] != '')){
			if ($bookingRow['Debit'] == ''){
				$debit = 0;
			}else{
				$debit = $bookingRow['Debit'];
			}
	
			if ($bookingRow['Credit'] == ''){
				$credit = 0;
			}else{
				$credit = $bookingRow['Credit'];
			}
			
			if (substr($bookingRow['Account'], 0, 2) == 'C-'){
				
				$customer = dbPrepareExecute($pdo, 'SELECT Id FROM Customer WHERE Number=?', array(substr($bookingRow['Account'], 2)));
				$booking->addRowAdvanced('1510', $customer[0]['Id'], '', '', '', $debit, $credit, '', '', '');
				
			} elseif(substr($bookingRow['Account'], 0, 2) == 'V-'){
				
				$vendor = dbPrepareExecute($pdo, 'SELECT Id FROM Vendor WHERE Number=?', array(substr($bookingRow['Account'], 2)));
				$booking->addRowAdvanced('2441', $vendor[0]['Id'], '', '', '', $debit, $credit, '', '', '');
				
			}else{
				
				if ($bookingRow['Account'] == '1510'){
					throw new Exception('Booking directly on account 1510 is not allowed. You need to book on a customer account.');
				}
				
				if ($bookingRow['Account'] == '2441'){
					throw new Exception('Booking directly on account 2441 is not allowed. You need to book on a vendor account.');
				}
				
				$booking->addRowAdvanced($bookingRow['Account'], '', '', '', '', $debit, $credit, '', '', '');
				
			}
		}
	}


	$booking->validateAndWriteToDatabase($pdo);

	$stmt = $pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Reload';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Journal voucher created #' . $booking->getDocumentNumber() . ".";


} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
	
}
header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>