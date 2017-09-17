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

checkUserAccess('AdministrationChartOfAccountsAccountChange');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];
	
	$pdo = createPdo();
		
	$pdo->exec('START TRANSACTION');
	
	$accountYear = dbPrepareExecute($pdo, 'SELECT Year FROM GeneralLedgerAccount WHERE Id=?', array($inputHidden['Id']));
	
	$yearOpen = dbPrepareExecute($pdo, 'SELECT Status FROM GeneralLedgerYear WHERE Year=?', array($accountYear[0]['Year']));
	
	if ($yearOpen[0]['Status'] != 'Open'){
		throw new Exception('The year '.$accountYear[0]['Year'].' is currently not open.');
	}
	
	dbPrepareExecute($pdo, 'UPDATE GeneralLedgerAccount SET Description=?, Type=?, ShowInVendorInvoice=? WHERE Id=?', array($inputVisible['Description'], $inputVisible['Type'], $inputVisible['ShowInVendorInvoice'], $inputHidden['Id']));
	
	auditTrailLog($pdo, 'GeneralLedgerAccount', $inputHidden['Id'], 'UPDATE');
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Account changed';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>