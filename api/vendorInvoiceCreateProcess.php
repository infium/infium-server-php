<?php
require('config.php');
require('classVendorInvoice.php');

checkUserAccess('VendorInvoiceCreate');

try {
	$input = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');
	
	$vendorInvoice = new VendorInvoice();
	$vendorInvoice->setBookingDate($input['BookingDate']);
	$vendorInvoice->setDate($input['InvoiceDate']);
	$vendorInvoice->setPaymentReference($input['VendorReference']);
	$vendorInvoice->setVendorNumber($input['Vendor']);
	$vendorInvoice->setBankAccount($input['BankAccount']);
	
	foreach ($input['Row'] as $row){
		$vendorInvoice->addRow($row['Account'], $row['Tax'], $row['Amount']);
	}
	
	$vendorInvoice->validateAndWriteToDatabase($pdo);
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Vendor invoice created #'.$vendorInvoice->getDocumentNumber();

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>