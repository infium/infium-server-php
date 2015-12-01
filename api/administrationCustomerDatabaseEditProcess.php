<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationCustomerDatabase');

try{
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');
	dbPrepareExecute($pdo, 'UPDATE Customer SET Active=?, InternalName=?, Email=?, EmailInvoice=?, TaxGroup=?, TaxNumber=?, BillToAddressLine1=?, BillToAddressLine2=?, BillToAddressLine3=?, BillToAddressLine4=?, BillToAddressCity=?, BillToAddressStateOrProvince=?, BillToAddressZipOrPostalCode=?, BillToAddressCountry=?, ShipToAddressLine1=?, ShipToAddressLine2=?, ShipToAddressLine3=?, ShipToAddressLine4=?, ShipToAddressCity=?, ShipToAddressStateOrProvince=?, ShipToAddressZipOrPostalCode=?, ShipToAddressCountry=? WHERE Id=?', array($inputVisible['Active'], $inputVisible['InternalName'], $inputVisible['Email'], $inputVisible['EmailInvoice'], $inputVisible['TaxGroup'], $inputVisible['TaxNumber'], $inputVisible['BillToAddressLine1'], $inputVisible['BillToAddressLine2'], $inputVisible['BillToAddressLine3'], $inputVisible['BillToAddressLine4'], $inputVisible['BillToAddressCity'], $inputVisible['BillToAddressStateOrProvince'], $inputVisible['BillToAddressZipOrPostalCode'], $inputVisible['BillToAddressCountry'], $inputVisible['ShipToAddressLine1'], $inputVisible['ShipToAddressLine2'], $inputVisible['ShipToAddressLine3'], $inputVisible['ShipToAddressLine4'], $inputVisible['ShipToAddressCity'], $inputVisible['ShipToAddressStateOrProvince'], $inputVisible['ShipToAddressZipOrPostalCode'], $inputVisible['ShipToAddressCountry'], $inputHidden['Id']));
	
	auditTrailLog($pdo, 'Customer', $inputHidden['Id'], 'UPDATE');
	
	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Customer updated';
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>