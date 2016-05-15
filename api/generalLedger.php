<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('General ledger');

if (checkUserAccessBoolean('GeneralLedgerJournalVoucherCreate')||checkUserAccessBoolean('GeneralLedgerJournalVoucherView')){
	$ui->addLabelValueLink('Journal voucher', NULL, 'GET', $baseUrl.'generalLedgerJournalVoucher.php', NULL, $titleBarColorGeneralLedgerJournalVoucher, NULL, 'f24e', $titleBarColorGeneralLedgerJournalVoucher);
}

if (checkUserAccessBoolean('GeneralLedgerClearingCreate')||checkUserAccessBoolean('GeneralLedgerClearingView')){
	$ui->addLabelValueLink('Clearing', NULL, 'GET', $baseUrl.'generalLedgerClearing.php', NULL, $titleBarColorGeneralLedgerClearing, NULL, 'f046', $titleBarColorGeneralLedgerClearing);
}

echo $ui->getObjectAsJSONString();
?>