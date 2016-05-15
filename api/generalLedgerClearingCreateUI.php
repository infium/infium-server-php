<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('GeneralLedgerClearingCreate');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT DISTINCT AccountNumber FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL ORDER BY AccountNumber ASC', array());

$ui = new UserInterface();

$ui->setTitle('Clearing');

foreach ($results as $row){
	$account = dbPrepareExecute($pdo, 'SELECT Type, Description FROM GeneralLedgerAccount WHERE AccountNumber=? ORDER BY Year DESC', array($row['AccountNumber']));
	
	if ($account[0]['Type'] == 'BS'){
		$results3 = dbPrepareExecute($pdo, 'SELECT COUNT(*) as OpenItemsCount FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL AND AccountNumber=?', array($row['AccountNumber']));
		
		$subAccounts = dbPrepareExecute($pdo, 'SELECT COUNT(*) as SubAccountNumberCount FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL AND AccountNumber=? AND SubAccountNumber!=?', array($row['AccountNumber'], ''));
		
		if ($subAccounts[0]['SubAccountNumberCount'] == 0){
			$ui->addLabelValueLink($row['AccountNumber'].' '.$account[0]['Description'], $results3[0]['OpenItemsCount'].' items', 'GET',$baseUrl.'generalLedgerClearingCreateUI3.php?AccountNumber='.$row['AccountNumber'].'&SubAccountNumber='.'', NULL, $titleBarColorGeneralLedgerClearing);
		}else{
			$ui->addLabelValueLink($row['AccountNumber'].' '.$account[0]['Description'], $results3[0]['OpenItemsCount'].' items', 'GET',$baseUrl.'generalLedgerClearingCreateUI2.php?AccountNumber='.$row['AccountNumber'], NULL, $titleBarColorGeneralLedgerClearing);			
		}
	}
}

if (count($results) == 0){
	$ui->addLabelValueLink('No open items');
}

echo $ui->getObjectAsJSONString();
?>