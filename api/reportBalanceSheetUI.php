<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportBalanceSheet');

$ui = new UserInterface();

$ui->setTitle('Balance sheet');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'reportBalanceSheetProcess.php');
$ui->setButtonLabel('Run');
$ui->setTitleBarColorNewWindow($titleBarColorReportBalanceSheet);

$ui->addField('Date',NULL,'Date');
$valueVisibleData['Date'] = date('Y').'-12-31';

$ui->addSearchSelection('Template', 'Template', $baseUrl.'reportBalanceSheetSearchTemplate.php');
$valueVisibleData['Template'] = '';
$valueVisibleDataDescription['Template'] = 'None';

$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

echo $ui->getObjectAsJSONString();
?>