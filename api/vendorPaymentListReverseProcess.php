<?php
require('config.php');
require('classVendorPaymentList.php');

checkUserAccess('VendorPaymentListReverse');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();

	validateDate($inputVisible['BookingDate']);
	validateVendorPaymentListDocumentNumber($pdo, $inputVisible['DocumentNumber']);

	$pdo->exec('START TRANSACTION');
	
	$previousDoc = dbPrepareExecute($pdo, 'SELECT Id FROM VendorPaymentList WHERE Number=?', array($inputVisible['DocumentNumber']));
	
	$previousDocRows = dbPrepareExecute($pdo, 'SELECT Id, PreviousRowId, Amount FROM VendorPaymentListRow WHERE ParentId=?', array($previousDoc[0]['Id']));
	
	$vendorPaymentList = new VendorPaymentList();
	
	$vendorPaymentList->setBookingDate($inputVisible['BookingDate']);
	
	foreach ($previousDocRows as $row){
		$vendorPaymentList->addRow($row['PreviousRowId'], $row['Amount'] * -1, $row['Id']);
	}

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