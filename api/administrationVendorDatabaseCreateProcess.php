<?php
require('config.php');
require('classUserInterface.php');
require('classVendorCreate.php');

checkUserAccess('AdministrationVendorDatabase');

try{
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();
	
	$pdo->exec('START TRANSACTION');
	
	$vendorCreate = new VendorCreate();
	$vendorCreate->setInternalName($inputVisible['InternalName']);
	$vendorCreate->setBankAccount($inputVisible['BankAccount']);
	$vendorCreate->setEmail($inputVisible['Email']);
	$vendorCreate->setTaxGroup($inputVisible['TaxGroup']);
	$vendorCreate->setPaymentTerms('30DAYS');
	$vendorCreate->setBillFromAddressLine1($inputVisible['BillFromAddressLine1']);
	$vendorCreate->setBillFromAddressLine2($inputVisible['BillFromAddressLine2']);
	$vendorCreate->setBillFromAddressLine3($inputVisible['BillFromAddressLine3']);
	$vendorCreate->setBillFromAddressLine4($inputVisible['BillFromAddressLine4']);
	$vendorCreate->setBillFromAddressCity($inputVisible['BillFromAddressCity']);
	$vendorCreate->setBillFromAddressStateOrProvince($inputVisible['BillFromAddressStateOrProvince']);
	$vendorCreate->setBillFromAddressZipOrPostalCode($inputVisible['BillFromAddressZipOrPostalCode']);
	$vendorCreate->setBillFromAddressCountry($inputVisible['BillFromAddressCountry']);
	$vendorCreate->create($pdo);
	
	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Reload';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Vendor created #'.$vendorCreate->getVendorNumber();
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>