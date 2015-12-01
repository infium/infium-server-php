<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('GeneralLedgerJournalVoucherCreate');

$ui = new UserInterface();

$ui->setTitle('Journal voucher');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'generalLedgerJournalVoucherCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('Date',NULL,'Booking date');
$ui->addField('Text',NULL,'Text');

$ui->addTable('Row');

$ui->addSearchSelection('Account','Account',$baseUrl.'reportGeneralLedgerSearchAccount.php', 'Row');
$ui->addField('Debit','Row','Debit');
$ui->addField('Credit','Row','Credit');

$value['Date'] = date("Y-m-d");

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>