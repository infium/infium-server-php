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

checkUserAccess('ReportProfitAndLoss');

try {
	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	validateDate($input['DateFrom']);
	validateDate($input['DateTo']);

	if ($input['DateTo'] < $input['DateFrom']){
		throw new Exception('The "From" date needs to be greater or equal to the "To" date.');
	}

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

	$ui = new UserInterface();

	$ui->setTitle('Profit and loss statement '.$input['DateFrom'].' - '.$input['DateTo']);

	$AccountYear = substr($input['DateFrom'],0,4);
	
	if ($input['Template'] == ''){
		$stmt = $pdo->prepare("SELECT AccountNumber, Description FROM GeneralLedgerAccount WHERE Year=? AND Type='PL' ORDER BY AccountNumber ASC");
		$stmt->execute(array($AccountYear));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$SumResult = 0;

		foreach ($results as $row){
	
			$stmt2 = $pdo->prepare('SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND BookingDate>=? AND BookingDate<=?');
			$stmt2->execute(array($AccountYear, $row['AccountNumber'], $input['DateFrom'], $input['DateTo']));
			$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);	
	
			$SumResult += $results2[0]['Amount'];
	
			if ($results2[0]['Amount']){
				$ui->addLabelValueLink($row['AccountNumber'].' '.$row['Description'], decimalFormat($results2[0]['Amount']*-1), 'GET', $baseUrl.'reportProfitAndLossRowProcess.php?AccountNumber='.$row['AccountNumber'].'&DateFrom='.$input['DateFrom'].'&DateTo='.$input['DateTo'], NULL, $titleBarColorReportProfitAndLoss);
			}
		}

		$ui->addLabelValueLink('Sum', decimalFormat($SumResult*-1));
		
	}else{
		$level = 0;
		$sumAmount = NULL;
		$sumRows = NULL;
		$pendingHeaders = NULL;
		$accountsInReport = array();
		$accountsInReportWithBalance = array();
		
		$sumAmount[0] = 0;
		$sumRows[0] = 0;
		
		function processSection($pdo, $ui, $parentId, $parentSection, $AccountYear, $dateFrom, $dateTo){
			global $level;
			global $sumAmount;
			global $sumRows;
			global $baseUrl;
			global $titleBarColorReportProfitAndLoss;
			global $pendingHeaders;
			global $accountsInReport;
			global $accountsInReportWithBalance;
			
			$itemsInSection = dbPrepareExecute($pdo, 'SELECT Id, SectionDescription, AccountNumber FROM ReportTemplateRow WHERE ParentId=? AND ParentSection=? ORDER BY \'Order\' ASC', array($parentId, $parentSection));
			foreach ($itemsInSection as $row){
				if ($row['SectionDescription'] != NULL){
					$level++;
					
					$pendingHeaders[$level] = $row['SectionDescription'];
					
					processSection($pdo, $ui, $parentId, $row['Id'], $AccountYear, $dateFrom, $dateTo);
					
					if (isset($sumRows[$level])&&($sumRows[$level]!=0)){
						$ui->addLabelValueLink('Sum '.$row['SectionDescription'], decimalFormat($sumAmount[$level]*-1), NULL, NULL, NULL, NULL, $level - 1);
					}
					$previousLevel = $level - 1;
					
					if (isset($sumAmount[$level])){
						if (isset($sumAmount[$previousLevel])){
							$sumAmount[$previousLevel] += $sumAmount[$level];
						}else{
							$sumAmount[$previousLevel] = $sumAmount[$level];
						}
					}
					$sumAmount[$level] = 0;
					
					if (isset($sumRows[$level])){
						if (isset($sumRows[$previousLevel])){
							$sumRows[$previousLevel] += $sumRows[$level];
						}else{
							$sumRows[$previousLevel] = $sumRows[$level];
						}
					}
					$sumRows[$level] = 0;
					$level--;
				}
				if ($row['AccountNumber'] != NULL){
					foreach ($accountsInReport as $acc){
						if ($acc == $row['AccountNumber']){
							throw new Exception('Account "'.$row['AccountNumber'].'" exist multiple times in the report template. Please ensure that the account is only present once in the report template.');
						}
					}
					$accountsInReport[] = $row['AccountNumber'];
					
					$accountDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND Type=\'PL\' AND AccountNumber=?', array($AccountYear, $row['AccountNumber']));
					$accountSumAmount = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND BookingDate>=? AND BookingDate<=?', array($AccountYear, $row['AccountNumber'], $dateFrom, $dateTo));					
					$accountSumRows = dbPrepareExecute($pdo, 'SELECT COUNT(*) as Rows FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND BookingDate>=? AND BookingDate<=?', array($AccountYear, $row['AccountNumber'], $dateFrom, $dateTo));
					
					if (isset($sumRows[$level])){
						$sumRows[$level] += $accountSumRows[0]['Rows'];
					}else{
						$sumRows[$level] = $accountSumRows[0]['Rows'];
					}
				
					if (isset($accountSumRows[0]['Rows'])&&($accountSumRows[0]['Rows'] > 0)){	
						for ($i = 0; $i <= 16; $i++) {
							if (isset($pendingHeaders[$i])){
								if ($pendingHeaders[$i] != ''){
									$ui->addLabelValueLink($pendingHeaders[$i], NULL, NULL, NULL, NULL, NULL, $i - 1);
									$pendingHeaders[$i] = '';
								}
							}
						}
						
						$ui->addLabelValueLink($row['AccountNumber'].' '.$accountDescription[0]['Description'], decimalFormat($accountSumAmount[0]['Amount']*-1), 'GET', $baseUrl.'reportProfitAndLossRowProcess.php?AccountNumber='.$row['AccountNumber'].'&DateFrom='.$dateFrom.'&DateTo='.$dateTo, NULL, $titleBarColorReportProfitAndLoss, $level);
						if (isset($sumAmount[$level])){
							$sumAmount[$level] += $accountSumAmount[0]['Amount'];
						}else{
							$sumAmount[$level] = $accountSumAmount[0]['Amount'];
						}
						$accountsInReportWithBalance[] = $row['AccountNumber'];
					}
				}
			}
		}
		
		$reportTemplateId = dbPrepareExecute($pdo, 'SELECT Id FROM ReportTemplate WHERE Year=? AND Type=\'PL\' AND Description=?', array($AccountYear, $input['Template']));

		if (count($reportTemplateId) != 1){
			throw new Exception('The template does not exist in the year.');
		}
		
		processSection($pdo, $ui, $reportTemplateId[0]['Id'], 0, $AccountYear, $input['DateFrom'], $input['DateTo']);
		
		$accountsWithBalanceInDatabase = dbPrepareExecute($pdo, 'SELECT DISTINCT AccountNumber FROM GeneralLedgerAccountBalance WHERE Year=? AND BookingDate>=? AND BookingDate<=?', array($AccountYear, $input['DateFrom'], $input['DateTo']));
		foreach ($accountsWithBalanceInDatabase as $accountInDatabase){
			$exist = False;
			foreach ($accountsInReportWithBalance as $accountInReport){
				if ($accountInDatabase['AccountNumber'] == $accountInReport){
					$exist = True;
				}
			}
			if ($exist == False){
				$accountMasterData = dbPrepareExecute($pdo, 'SELECT Type FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?', array($AccountYear, $accountInDatabase['AccountNumber']));
				if ($accountMasterData[0]['Type'] == 'PL'){
					throw new Exception('Account "'.$accountInDatabase['AccountNumber'].'" is missing in the report template.');
				}
			}
		}
	}

	$pdo->exec('ROLLBACK');

	echo $ui->getObjectAsJSONString();
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'The following error occurred: ' . $e->getMessage();
	
	header('Content-type: application/json');
	echo json_encode($response,JSON_PRETTY_PRINT);
}
?>