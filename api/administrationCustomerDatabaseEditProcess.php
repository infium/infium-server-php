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