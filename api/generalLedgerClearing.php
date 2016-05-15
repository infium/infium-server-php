<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Clearing');

if (checkUserAccessBoolean('GeneralLedgerClearingCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'generalLedgerClearingCreateUI.php', NULL, $titleBarColorGeneralLedgerClearing, NULL, 'f067', $titleBarColorGeneralLedgerClearing);
}

echo $ui->getObjectAsJSONString();
?>