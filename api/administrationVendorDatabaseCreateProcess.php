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
	$response['Data'][0]['Action'] = 'Pop';
    $response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Vendor created #'.$vendorCreate->getVendorNumber();
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>
