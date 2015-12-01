<?php
require('config.php');
require('classUserInterface.php');
require('classCustomerCreate.php');

checkUserAccess('AdministrationCustomerDatabase');

try{
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');
	
	$customerCreate = new CustomerCreate();
	$customerCreate->setInternalName($inputVisible['InternalName']);
	$customerCreate->setEmail($inputVisible['Email']);
	$customerCreate->setEmailInvoice($inputVisible['EmailInvoice']);
	$customerCreate->setTaxGroup($inputVisible['TaxGroup']);
	$customerCreate->setTaxNumber($inputVisible['TaxNumber']);
	$customerCreate->setPaymentTerms('30DAYS');
	$customerCreate->setBillToAddressLine1($inputVisible['BillToAddressLine1']);
	$customerCreate->setBillToAddressLine2($inputVisible['BillToAddressLine2']);
	$customerCreate->setBillToAddressLine3($inputVisible['BillToAddressLine3']);
	$customerCreate->setBillToAddressLine4($inputVisible['BillToAddressLine4']);
	$customerCreate->setBillToAddressCity($inputVisible['BillToAddressCity']);
	$customerCreate->setBillToAddressStateOrProvince($inputVisible['BillToAddressStateOrProvince']);
	$customerCreate->setBillToAddressZipOrPostalCode($inputVisible['BillToAddressZipOrPostalCode']);
	$customerCreate->setBillToAddressCountry($inputVisible['BillToAddressCountry']);
	$customerCreate->setShipToAddressLine1($inputVisible['ShipToAddressLine1']);
	$customerCreate->setShipToAddressLine2($inputVisible['ShipToAddressLine2']);
	$customerCreate->setShipToAddressLine3($inputVisible['ShipToAddressLine3']);
	$customerCreate->setShipToAddressLine4($inputVisible['ShipToAddressLine4']);
	$customerCreate->setShipToAddressCity($inputVisible['ShipToAddressCity']);
	$customerCreate->setShipToAddressStateOrProvince($inputVisible['ShipToAddressStateOrProvince']);
	$customerCreate->setShipToAddressZipOrPostalCode($inputVisible['ShipToAddressZipOrPostalCode']);
	$customerCreate->setShipToAddressCountry($inputVisible['ShipToAddressCountry']);
	$customerCreate->create($pdo);
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Reload';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Customer created #'.$customerCreate->getCustomerNumber();
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>