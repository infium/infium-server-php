<?php
require('config.php');
require('classVendorPaymentList.php');

checkUserAccess('VendorPaymentListCreate');

try {
	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	
	validateDate($input['BookingDate']);
	
	$vendorPaymentList = new VendorPaymentList();
	
	$vendorPaymentList->setBookingDate($input['BookingDate']);
	
	$a = 0;
	foreach ($input as $invoiceId => $invoiceCreateBoolean){
		if ((substr($invoiceId, 0, 3)=='Row') && ($invoiceCreateBoolean == True)){
			$vendorPaymentList->addRow(substr($invoiceId, 3));
			$a++;
		}
	}
	
	if ($a == 0){
		throw new Exception('Document contains no rows.');
	}

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');
	$vendorPaymentList->validateAndWriteToDatabase($pdo);
	$pdo->exec('COMMIT');	
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Payment list created #'.$vendorPaymentList->getDocumentNumber();
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>