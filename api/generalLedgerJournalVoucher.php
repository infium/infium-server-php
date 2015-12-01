<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Journal voucher');

if (checkUserAccessBoolean('GeneralLedgerJournalVoucherCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'generalLedgerJournalVoucherCreateUI.php', NULL, $titleBarColorGeneralLedgerJournalVoucher, NULL, 'f067', $titleBarColorGeneralLedgerJournalVoucher);
}

if (checkUserAccessBoolean('GeneralLedgerJournalVoucherView')){
	$ui->addLabelValueLink('View', NULL, 'GET', $baseUrl.'generalLedgerJournalVoucherView.php', NULL, $titleBarColorGeneralLedgerJournalVoucher, NULL, 'f06e', $titleBarColorGeneralLedgerJournalVoucher);
}

echo $ui->getObjectAsJSONString();
?>