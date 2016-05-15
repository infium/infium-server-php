<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('GeneralLedgerClearingCreate');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT DISTINCT SubAccountNumber FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL AND AccountNumber=? ORDER BY AccountNumber ASC', array($_GET['AccountNumber']));

$ui = new UserInterface();

$ui->setTitle('Clearing');

foreach ($results as $row){
	$results3 = dbPrepareExecute($pdo, 'SELECT COUNT(*) as OpenItemsCount FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL AND AccountNumber=? AND SubAccountNumber=?', array($_GET['AccountNumber'], $row['SubAccountNumber']));
	
	if ($row['SubAccountNumber'] == ''){
		$subAccountText = 'Not assigned';
	}
	
	if ($_GET['AccountNumber'] == '1510' && $row['SubAccountNumber'] != ''){
		$customer = dbPrepareExecute($pdo, 'SELECT Number, InternalName FROM Customer WHERE Id=?', array($row['SubAccountNumber']));
		$subAccountText = 'C-' . $customer[0]['Number'] . ' ' . $customer[0]['InternalName'];
	}
	
	if ($_GET['AccountNumber'] == '2441' && $row['SubAccountNumber'] != ''){
		$vendor = dbPrepareExecute($pdo, 'SELECT Number, InternalName FROM Vendor WHERE Id=?', array($row['SubAccountNumber']));
		$subAccountText = 'V-' . $vendor[0]['Number'] . ' ' . $vendor[0]['InternalName'];
	}
	
	$ui->addLabelValueLink($subAccountText, $results3[0]['OpenItemsCount'].' items', 'GET',$baseUrl.'generalLedgerClearingCreateUI3.php?AccountNumber='.$_GET['AccountNumber'].'&SubAccountNumber='.$row['SubAccountNumber'], NULL, $titleBarColorGeneralLedgerClearing);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No open items');
}

echo $ui->getObjectAsJSONString();
?>