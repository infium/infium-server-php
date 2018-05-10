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

checkUserAccess('AdministrationChartOfAccountsYearChange');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');

	if (($inputVisible['Status'] != 'Open')&&($inputVisible['Status'] != 'Closed')){
		throw new Exception('The year must be either open or closed.');
	}

	if ($inputVisible['Status'] == 'Closed'){
		$year = dbPrepareExecute($pdo, 'SELECT Year FROM GeneralLedgerYear WHERE Id=?', array($inputHidden['Id']));

		if ($inputVisible['Status'] == 'Closed'){
			$previousYears = dbPrepareExecute($pdo, 'SELECT Year, Status FROM GeneralLedgerYear WHERE Year<?', array($year[0]['Year']));
			foreach ($previousYears as $row) {
				if ($row['Status'] != 'Closed'){
					throw new Exception('All previous years must be closed for this year to be closed. Currently year '.$row['Year'].' is open.');
				}
			}
		}

		if ($inputVisible['Status'] == 'Open'){
			$upcomingYears = dbPrepareExecute($pdo, 'SELECT Year, Status FROM GeneralLedgerYear WHERE Year>?', array($year[0]['Year']));
			foreach ($upcomingYears as $row) {
				if ($row['Status'] != 'Open'){
					throw new Exception('All upcoming years must be open for this year to be opened. Currently year '.$row['Year'].' is closed.');
				}
			}
		}

		$accountsProfitAndLoss = dbPrepareExecute($pdo, 'SELECT AccountNumber FROM GeneralLedgerAccount WHERE Year=? AND Type=\'PL\'', array($year[0]['Year']));

		$amountProfitAndLoss = 0.0;
		foreach ($accountsProfitAndLoss as $row){
			$amountProfitAndLossAccount = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=?', array($year[0]['Year'], $row['AccountNumber']));
			$amountProfitAndLoss += $amountProfitAndLossAccount[0]['Amount'];
		}

		if (!(bccomp($amountProfitAndLoss, 0.0, 4) === 0)){
			throw new Exception('The balance in the profit and loss statement must be zero. Please run the balance carry forward procedure before you close the year.');
		}

		$accountsBalanceSheet = dbPrepareExecute($pdo, 'SELECT AccountNumber FROM GeneralLedgerAccount WHERE Year=? AND Type=\'BS\'', array($year[0]['Year']));

		$amountBalanceSheet = 0;
		foreach ($accountsBalanceSheet as $row){
			$amountBalanceSheetAccount = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=?', array($year[0]['Year'], $row['AccountNumber']));
			$amountBalanceSheet += $amountBalanceSheetAccount[0]['Amount'];
		}

		if (!(bccomp($amountBalanceSheet, 0.0, 4) === 0)){
			throw new Exception('The balance in the balance sheet must be zero. Please run the balance carry forward procedure before you close the year.');
		}

		$yearBefore = $year[0]['Year'] - 1;
		$accountsBalanceSheet = dbPrepareExecute($pdo, 'SELECT AccountNumber FROM GeneralLedgerAccount WHERE Year=? AND Type=\'BS\'', array($yearBefore));

		foreach ($accountsBalanceSheet as $row1){
			$accountsAndSubAccountsBalanceSheet = dbPrepareExecute($pdo, 'SELECT DISTINCT AccountNumber, SubAccountNumber FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=?', array($yearBefore, $row1['AccountNumber']));

			foreach ($accountsAndSubAccountsBalanceSheet as $row){
				$amountBalanceSheetAccountAndSubAccountFirstYear = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND SubAccountNumber=?', array($yearBefore, $row['AccountNumber'], $row['SubAccountNumber']));
				$amountBalanceSheetAccountAndSubAccountSecondYear = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND SubAccountNumber=? AND BookingDate IS NULL', array($year[0]['Year'], $row['AccountNumber'], $row['SubAccountNumber']));
				$difference = $amountBalanceSheetAccountAndSubAccountSecondYear[0]['Amount'] - $amountBalanceSheetAccountAndSubAccountFirstYear[0]['Amount'];
				if ($difference > 0.001){
					throw new Exception('The outgoing balance of account '.$row['AccountNumber'].' in '.$yearBefore.' is not equal to the opening balance of the same account in '.$year[0]['Year'].'. The difference is ' . $difference . '. Please run the balance carry forward procedure before you close the year.');
				}
			}
		}
	}

	dbPrepareExecute($pdo, 'UPDATE GeneralLedgerYear SET Status=? WHERE Id=?', array($inputVisible['Status'], $inputHidden['Id']));

	auditTrailLog($pdo, 'GeneralLedgerYear', $inputHidden['Id'], 'UPDATE');

	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Year changed';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>
