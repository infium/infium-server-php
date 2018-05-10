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
