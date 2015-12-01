<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportGeneralLedger');

$ui = new UserInterface();

$ui->setTitle('General ledger');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'reportGeneralLedgerProcess.php');
$ui->setButtonLabel('Run');
$ui->setTitleBarColorNewWindow($titleBarColorReportGeneralLedger);

$ui->addSearchSelection('Account','Account',$baseUrl.'reportGeneralLedgerSearchAccount.php');

$ui->addField('DateFrom',NULL,'From');
$ui->addField('DateTo',NULL,'To');

$value['DateFrom'] = date('Y').'-01-01';
$value['DateTo'] = date('Y').'-12-31';

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>