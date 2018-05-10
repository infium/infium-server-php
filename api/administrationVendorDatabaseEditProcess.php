<?php
/*
 * Copyright 2012-2017 Marcus Hammar
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

checkUserAccess('AdministrationVendorDatabase');

try{
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');
	dbPrepareExecute($pdo, 'UPDATE Vendor SET Active=?, InternalName=?, BankAccount=?, Email=?, TaxGroup=?, BillFromAddressLine1=?, BillFromAddressLine2=?, BillFromAddressLine3=?, BillFromAddressLine4=?, BillFromAddressCity=?, BillFromAddressStateOrProvince=?, BillFromAddressZipOrPostalCode=?, BillFromAddressCountry=? WHERE Id=?', array($inputVisible['Active'], $inputVisible['InternalName'], $inputVisible['BankAccount'], $inputVisible['Email'], $inputVisible['TaxGroup'], $inputVisible['BillFromAddressLine1'], $inputVisible['BillFromAddressLine2'], $inputVisible['BillFromAddressLine3'], $inputVisible['BillFromAddressLine4'], $inputVisible['BillFromAddressCity'], $inputVisible['BillFromAddressStateOrProvince'], $inputVisible['BillFromAddressZipOrPostalCode'], $inputVisible['BillFromAddressCountry'], $inputHidden['Id']));

	auditTrailLog($pdo, 'Vendor', $inputHidden['Id'], 'UPDATE');

	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Vendor updated';
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>
