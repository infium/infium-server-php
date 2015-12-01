<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('General ledger');

if (checkUserAccessBoolean('GeneralLedgerJournalVoucherCreate')||checkUserAccessBoolean('GeneralLedgerJournalVoucherView')){
	$ui->addLabelValueLink('Journal voucher', NULL, 'GET', $baseUrl.'generalLedgerJournalVoucher.php', NULL, $titleBarColorGeneralLedgerJournalVoucher, NULL, 'f24e', $titleBarColorGeneralLedgerJournalVoucher);
}

echo $ui->getObjectAsJSONString();
?>