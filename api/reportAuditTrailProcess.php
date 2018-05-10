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
require('functionRenderReportAuditTrail.php');

checkUserAccess('ReportAuditTrail');

try {

	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$type = $input['Type'];
	$number = $input['Number'];

	if (($type == 'CustomerInvoice')||($type == 'CustomerPayment')||($type == 'VendorInvoice')||($type == 'VendorPaymentList')||($type == 'VendorPaymentCompleted')||($type == 'GeneralLedgerAccountBooking')||($type == 'GeneralLedgerAccountClearing')||($type == 'Customer')||($type == 'Vendor')||($type == 'Article')||($type == 'TaxReport')||($type == 'User')){
		$pdo = createPdo();

		if ($type == 'User'){
			$id = dbPrepareExecute($pdo, 'SELECT Id FROM '.$type.' WHERE Username=?', array($number));
		}else{
			$id = dbPrepareExecute($pdo, 'SELECT Id FROM '.$type.' WHERE Number=?', array($number));
		}

		$num = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfEntries FROM `AuditTrail` WHERE `Table`=? AND `TableId`=?', array($type, $id[0]['Id']));

		if ((count($num) == 0)||($num[0]['NumberOfEntries'] == 0)){
			throw new Exception('No entries were found. Check document number.');
		}
	}else{
		throw new Exception('No type was selected.');
	}

	header('Content-type: text/html');
	header('Show-Print-Icon: true');

	echo renderReportAuditTrail($type, $number);

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'The following error occurred: ' . $e->getMessage();

	header('Content-type: application/json');
	echo json_encode($response,JSON_PRETTY_PRINT);
}
?>
