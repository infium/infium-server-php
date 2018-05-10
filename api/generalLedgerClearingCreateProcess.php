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
require('classGeneralLedgerClearing.php');

checkUserAccess('GeneralLedgerClearingCreate');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$generalLedgerClearing = new GeneralLedgerClearing();

	$generalLedgerClearing->setDate($inputVisible['ClearingDate']);
	$generalLedgerClearing->setAccountNumber($inputHidden['AccountNumber']);
	$generalLedgerClearing->setSubAccountNumber($inputHidden['SubAccountNumber']);

	$pdo = createPdo();

	foreach ($inputVisible as $rowId => $rowClearBoolean){
		if ((substr($rowId, 0, 3)=='Row') && ($rowClearBoolean == True)){
			$generalLedgerClearing->addRow($pdo, substr($rowId, 3));
		}
	}

	$pdo->exec('START TRANSACTION');
	$generalLedgerClearing->validateAndWriteToDatabase($pdo);
	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	if ($inputHidden['SubAccountNumber'] != ''){
		$response['Data'][0]['Action'] = 'Pop';
		$response['Data'][1]['Action'] = 'Pop';
		$response['Data'][2]['Action'] = 'Reload';
		$response['Data'][3]['Action'] = 'MessageFlash';
		$response['Data'][3]['Message'] = 'Clearing done #'.$generalLedgerClearing->getDocumentNumber();
	}else{
		$response['Data'][0]['Action'] = 'Pop';
		$response['Data'][1]['Action'] = 'Reload';
		$response['Data'][2]['Action'] = 'MessageFlash';
		$response['Data'][2]['Message'] = 'Clearing done #'.$generalLedgerClearing->getDocumentNumber();
	}
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>
