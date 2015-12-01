<?php
require('config.php');
require('classGeneralLedgerAccountBooking.php');

checkUserAccess('GeneralLedgerJournalVoucherCreate');

$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

try {

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
			$booking->addRow($bookingRow['Account'], '', $debit, $credit);
		}
	}

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');

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