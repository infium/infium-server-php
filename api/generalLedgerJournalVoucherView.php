<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('GeneralLedgerJournalVoucherView');

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Number FROM GeneralLedgerAccountBooking ORDER BY Id DESC'); // We need to handle limits
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui = new UserInterface();

$ui->setTitle('View');

foreach ($results as $row){
	$ui->addLabelValueLink('Journal voucher #'.$row['Number'], NULL, 'GET',$baseUrl.'generalLedgerJournalVoucherViewDocument.php?Number='.$row['Number'], NULL, $titleBarColorGeneralLedgerJournalVoucher);	
}

if (count($results) == 0){
	$ui->addLabelValueLink('No documents exist yet');	
}

echo $ui->getObjectAsJSONString();
?>