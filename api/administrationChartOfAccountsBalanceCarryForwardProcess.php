<?php
require('config.php');
require('classGeneralLedgerAccountBooking.php');

checkUserAccess('AdministrationChartOfAccountsBalanceCarryForward');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	
	$firstYear = substr($inputVisible['Period'],0,4);
	
	$secondYear = substr($inputVisible['Period'],5,4);
	
	$pdo = createPdo();
	
	$pdo->exec('START TRANSACTION');
	
	$accountsProfitAndLoss = dbPrepareExecute($pdo, 'SELECT AccountNumber FROM GeneralLedgerAccount WHERE Year=? AND Type=\'PL\'', array($firstYear));
	
	$amountProfitAndLoss = 0;
	foreach ($accountsProfitAndLoss as $row){
		$amountProfitAndLossAccount = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=?', array($firstYear, $row['AccountNumber']));
		$amountProfitAndLoss += $amountProfitAndLossAccount[0]['Amount'];
	}
	
	if ($amountProfitAndLoss != 0){
		$bookingFirstYear = new GeneralLedgerAccountBooking();		
		$bookingFirstYear->setDate($firstYear.'-12-31');
		$bookingFirstYear->setText('Balance carry forward');
		
		$bookingSecondYear = new GeneralLedgerAccountBooking();		
		$bookingSecondYear->setDate($secondYear.'-01-01');
		$bookingSecondYear->setText('Balance carry forward');
		
		if ($amountProfitAndLoss > 0){
			$bookingFirstYear->addRow('2099','', $amountProfitAndLoss, 0);
			$bookingFirstYear->addRow('8999','', 0, $amountProfitAndLoss);
			
			$bookingSecondYear->addRow('2099','', 0, $amountProfitAndLoss);
			$bookingSecondYear->addRow('2091','', $amountProfitAndLoss, 0);			
		}else{
			$bookingFirstYear->addRow('2099','', 0, $amountProfitAndLoss*-1);
			$bookingFirstYear->addRow('8999','', $amountProfitAndLoss*-1, 0);
			
			$bookingSecondYear->addRow('2099','', $amountProfitAndLoss*-1, 0);
			$bookingSecondYear->addRow('2091','', 0, $amountProfitAndLoss*-1);
		}
		
		$bookingFirstYear->validateAndWriteToDatabase($pdo);
		$bookingSecondYear->validateAndWriteToDatabase($pdo);
	}
	
	$accountsToDelete = dbPrepareExecute($pdo, 'SELECT Id FROM GeneralLedgerAccountBalance WHERE Year=? AND BookingDate IS NULL', array($secondYear));
	
	foreach ($accountsToDelete as $row){
		auditTrailLog($pdo, 'GeneralLedgerAccountBalance', $row['Id'], 'DELETE');
	}
	
	dbPrepareExecute($pdo, 'DELETE FROM GeneralLedgerAccountBalance WHERE Year=? AND BookingDate IS NULL', array($secondYear));
	
	$accounts = dbPrepareExecute($pdo, 'SELECT AccountNumber, SubAccountNumber FROM GeneralLedgerAccount WHERE Year=? AND Type=\'BS\'', array($firstYear));

	foreach ($accounts as $row){
		if ($row['SubAccountNumber'] == True){
			$subAccounts = dbPrepareExecute($pdo, 'SELECT DISTINCT SubAccountNumber FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=?', array($firstYear, $row['AccountNumber']));
			
			foreach ($subAccounts as $row2){
				$subAccountSum = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND SubAccountNumber=?', array($firstYear, $row['AccountNumber'], $row2['SubAccountNumber']));
				
				$accountBalance = $subAccountSum[0]['Amount']; 
				if ($accountBalance > 0){
					$debit = $accountBalance;
					$credit = 0;
					$amount = $accountBalance;
				}else{
					$debit = 0;
					$credit = $accountBalance*-1;
					$amount = $accountBalance;
				}
				if ($amount != 0){
					
					try {
						validateAccountNumber($pdo, $secondYear, $row['AccountNumber']);
					} catch (Exception $e) {
						throw new Exception('Account '.$row['AccountNumber'].' does not exist in year '.$secondYear.'. To transfer the balance it needs to be created.');
					}
					
					dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountBalance (Year, AccountNumber, SubAccountNumber, ProfitCenter, Debit, Credit, Amount) VALUES (?, ?, ?, ?, ?, ?, ?)', array($secondYear, $row['AccountNumber'], $row2['SubAccountNumber'], '', $debit, $credit, $amount));
					
					auditTrailLog($pdo, 'GeneralLedgerAccountBalance', $pdo->lastInsertId(), 'INSERT');
				}else{
					
					$openItems = dbPrepareExecute($pdo, 'SELECT COUNT(*) as OpenItems FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? AND SubAccountNumber=? AND BookingDate<=? AND (ClearingDate IS NULL OR ClearingDate>?)', array($row['AccountNumber'], $row2['SubAccountNumber'], $firstYear.'-12-31', $firstYear.'-12-31'));
					
					if ($openItems[0]['OpenItems'] > 0){
						dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountBalance (Year, AccountNumber, SubAccountNumber, ProfitCenter, Debit, Credit, Amount) VALUES (?, ?, ?, ?, ?, ?, ?)', array($secondYear, $row['AccountNumber'], $row2['SubAccountNumber'], '', 0, 0, 0));
					
						auditTrailLog($pdo, 'GeneralLedgerAccountBalance', $pdo->lastInsertId(), 'INSERT');
					}
					
				}
			}
		}else{
			$subAccountSum = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=?', array($firstYear, $row['AccountNumber']));
			
			$accountBalance = $subAccountSum[0]['Amount']; 
			if ($accountBalance > 0){
				$debit = $accountBalance;
				$credit = 0;
				$amount = $accountBalance;
			}else{
				$debit = 0;
				$credit = $accountBalance*-1;
				$amount = $accountBalance;
			}
			if ($amount != 0){
				
				try {
					validateAccountNumber($pdo, $secondYear, $row['AccountNumber']);
				} catch (Exception $e) {
					throw new Exception('Account '.$row['AccountNumber'].' does not exist in year '.$secondYear.'. To transfer the balance it needs to be created.');
				}
				
				dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountBalance (Year, AccountNumber, SubAccountNumber, ProfitCenter, Debit, Credit, Amount) VALUES (?, ?, ?, ?, ?, ?, ?)', array($secondYear, $row['AccountNumber'], '', '', $debit, $credit, $amount));
				
				auditTrailLog($pdo, 'GeneralLedgerAccountBalance', $pdo->lastInsertId(), 'INSERT');	
			}else{
				$openItems = dbPrepareExecute($pdo, 'SELECT COUNT(*) as OpenItems FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? AND BookingDate<=? AND (ClearingDate IS NULL OR ClearingDate>?)', array($row['AccountNumber'], $firstYear.'-12-31', $firstYear.'-12-31'));
				
				if ($openItems[0]['OpenItems'] > 0){
					dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountBalance (Year, AccountNumber, SubAccountNumber, ProfitCenter, Debit, Credit, Amount) VALUES (?, ?, ?, ?, ?, ?, ?)', array($secondYear, $row['AccountNumber'], '', '', 0, 0, 0));
				
					auditTrailLog($pdo, 'GeneralLedgerAccountBalance', $pdo->lastInsertId(), 'INSERT');
				}
				
			}
		}
	}
	
	$balanceSheetOpeningBalanceSum = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=?', array($secondYear));
	
	if ($balanceSheetOpeningBalanceSum[0]['Amount'] != 0){
		throw new Exception('The balance sheet of year '.$firstYear.' needs to have a sum of zero at year end. A common reason for the difference is that the profit and loss statement result has not yet been booked in the balance sheet. Currently the sum is '.decimalFormat($balanceSheetOpeningBalanceSum[0]['Amount']));
	}
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Balance transferred from year '.$firstYear.' to year '.$secondYear;

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>