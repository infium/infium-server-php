<?php
require('config.php');
require('classVendorPaymentCompleted.php');

checkUserAccess('VendorPaymentCompletedReverse');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();
	
	validateDate($inputVisible['BookingDate']);
	validateVendorPaymentCompletedDocumentNumber($pdo, $inputVisible['DocumentNumber']);
	
	$pdo->exec('START TRANSACTION');

	$previousDoc = dbPrepareExecute($pdo, 'SELECT Id, PartnerDate FROM VendorPaymentCompleted WHERE Number=?', array($inputVisible['DocumentNumber']));
	
	$previousDocRows = dbPrepareExecute($pdo, 'SELECT Id, PreviousRowId, Amount FROM VendorPaymentCompletedRow WHERE ParentId=?', array($previousDoc[0]['Id']));
	
	$vendorPaymentCompleted = new VendorPaymentCompleted();

	$vendorPaymentCompleted->setDate($inputVisible['BookingDate']);
	
	foreach ($previousDocRows as $row){
		$vendorPaymentCompleted->addRow($row['PreviousRowId'], $row['Amount'] * -1);
	}
	
	$vendorPaymentCompleted->validateAndWriteToDatabase($pdo);
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Reload';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Vendor payment completed #'.$vendorPaymentCompleted->getDocumentNumber();

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>