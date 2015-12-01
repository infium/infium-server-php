<?php
require('config.php');
require('classVendorInvoiceReverse.php');

checkUserAccess('VendorInvoiceReverse');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];	
	
	$pdo = createPdo();
	
	validateDate($inputVisible['BookingDate']);
	validateVendorInvoiceDocumentNumber($pdo, $inputVisible['DocumentNumber']);
	
	$pdo->exec("START TRANSACTION;");
	
	$vendorInvoiceReverse = new VendorInvoiceReverse();
	
	$newDocumentNumberInvoice = $vendorInvoiceReverse->reverse($pdo, $inputVisible['BookingDate'], $inputVisible['DocumentNumber']);
	
	$pdo->exec("COMMIT;");
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Invoice created #'.$newDocumentNumberInvoice;
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>