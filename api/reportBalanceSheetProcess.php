<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportBalanceSheet');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	
	validateDate($inputVisible['Date']);

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

	$ui = new UserInterface();

	$ui->setTitle('Balance sheet '.$inputVisible['Date']);

	$AccountYear = substr($inputVisible['Date'],0,4);
	
	if ($inputVisible['Template'] == ''){
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
		
	}else{
		$level = 0;
		$sumAmount = NULL;
		$sumRows = NULL;
		$pendingHeaders = NULL;
		$accountsInReport = array();
		$accountsInReportWithBalance = array();
		
		$sumAmount[0] = 0;
		$sumRows[0] = 0;
		
		function processSection($pdo, $ui, $parentId, $parentSection, $AccountYear, $date){
			global $level;
			global $sumAmount;
			global $sumRows;
			global $baseUrl;
			global $titleBarColorReportBalanceSheet;
			global $pendingHeaders;
			global $accountsInReport;
			global $accountsInReportWithBalance;
		
			$itemsInSection = dbPrepareExecute($pdo, 'SELECT Id, SectionDescription, AccountNumber FROM ReportTemplateRow WHERE ParentId=? AND ParentSection=? ORDER BY \'Order\' ASC', array($parentId, $parentSection));
			foreach ($itemsInSection as $row){
				if ($row['SectionDescription'] != NULL){
					$level++;
					
					$pendingHeaders[$level] = $row['SectionDescription'];
					
					processSection($pdo, $ui, $parentId, $row['Id'], $AccountYear, $date);
					
					if (isset($sumRows[$level])&&($sumRows[$level]!=0)){
						$ui->addLabelValueLink('Sum '.$row['SectionDescription'], decimalFormat($sumAmount[$level]), NULL, NULL, NULL, NULL, $level - 1);
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
					
					$accountDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND Type=\'BS\' AND AccountNumber=?', array($AccountYear, $row['AccountNumber']));
					$accountSumAmount = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND (BookingDate<=? OR BookingDate IS NULL)', array($AccountYear, $row['AccountNumber'], $date));
					$accountSumRows = dbPrepareExecute($pdo, 'SELECT COUNT(*) as Rows FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND (BookingDate<=? OR BookingDate IS NULL)', array($AccountYear, $row['AccountNumber'], $date));
					
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
						
						$ui->addLabelValueLink($row['AccountNumber'].' '.$accountDescription[0]['Description'], decimalFormat($accountSumAmount[0]['Amount']), 'GET', $baseUrl.'reportBalanceSheetRowProcess.php?AccountNumber='.$row['AccountNumber'].'&Date='.$date, NULL, $titleBarColorReportBalanceSheet, $level);
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
		
		$reportTemplateId = dbPrepareExecute($pdo, 'SELECT Id FROM ReportTemplate WHERE Year=? AND Type=\'BS\' AND Description=?', array($AccountYear, $inputVisible['Template']));

		if (count($reportTemplateId) != 1){
			throw new Exception('The template does not exist in the year.');
		}
		
		processSection($pdo, $ui, $reportTemplateId[0]['Id'], 0, $AccountYear, $inputVisible['Date']);
		
		
		$accountsWithBalanceInDatabase = dbPrepareExecute($pdo, 'SELECT DISTINCT AccountNumber FROM GeneralLedgerAccountBalance WHERE Year=? AND (BookingDate<=? OR BookingDate IS NULL)', array($AccountYear, $inputVisible['Date']));
		foreach ($accountsWithBalanceInDatabase as $accountInDatabase){
			$exist = False;
			foreach ($accountsInReportWithBalance as $accountInReport){
				if ($accountInDatabase['AccountNumber'] == $accountInReport){
					$exist = True;
				}
			}
			if ($exist == False){
				$accountMasterData = dbPrepareExecute($pdo, 'SELECT Type FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?', array($AccountYear, $accountInDatabase['AccountNumber']));
				if ($accountMasterData[0]['Type'] == 'BS'){
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