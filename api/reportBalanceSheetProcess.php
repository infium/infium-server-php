<?php
/*
 * Copyright 2012-2018 Marcus Hammar
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
require('functionRenderReportBalanceSheet.php');

checkUserAccess('ReportBalanceSheet');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	validateDate($inputVisible['Date']);

	if ($inputVisible['Template'] == ''){
		$pdo = createPdo();

		$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

		$ui = new UserInterface();

		$ui->setTitle('Balance sheet '.$inputVisible['Date']);

		$AccountYear = substr($inputVisible['Date'],0,4);

		$stmt = $pdo->prepare("SELECT AccountNumber,Description FROM GeneralLedgerAccount WHERE Year=? AND Type='BS' ORDER BY AccountNumber ASC");
		$stmt->execute(array($AccountYear));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$SumResult = 0;

		foreach ($results as $row){

			$stmt2 = $pdo->prepare('SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND (BookingDate<=? OR BookingDate IS NULL)');
			$stmt2->execute(array($AccountYear, $row['AccountNumber'], $inputVisible['Date']));
			$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

			$SumResult = $SumResult + $results2[0]['Amount'];

			if ($results2[0]['Amount']){
				$ui->addLabelValueLink($row['AccountNumber'].' '.$row['Description'], decimalFormat($results2[0]['Amount']), 'GET', $baseUrl.'reportBalanceSheetRowProcess.php?AccountNumber='.$row['AccountNumber'].'&Date='.$inputVisible['Date'], NULL, $titleBarColorReportBalanceSheet);
			}
		}

		$SumAmount = number_format($SumResult*-1, 2, '.', ',');

		$ui->addLabelValueLink('Difference', decimalFormat($SumResult*-1));

		$pdo->exec('ROLLBACK');

		echo $ui->getObjectAsJSONString();
	}else{
		$output = renderReportBalanceSheet($inputVisible['Date'], $inputVisible['Template']);
		header('Content-type: text/html');
		header('Show-Print-Icon: true');
		echo $output;
	}

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'The following error occurred: ' . $e->getMessage();

	header('Content-type: application/json');
	echo json_encode($response,JSON_PRETTY_PRINT);
}
?>
