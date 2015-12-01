<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportProfitAndLoss');

$ui = new UserInterface();

$ui->setTitle('Profit and loss statement');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'reportProfitAndLossProcess.php');
$ui->setButtonLabel('Run');
$ui->setTitleBarColorNewWindow($titleBarColorReportProfitAndLoss);

$ui->addField('DateFrom',NULL,'From');
$ui->addField('DateTo',NULL,'To');

$value['DateFrom'] = date('Y').'-01-01';
$value['DateTo'] = date('Y').'-12-31';

$ui->addSearchSelection('Template', 'Template', $baseUrl.'reportProfitAndLossSearchTemplate.php');
$valueVisibleData['Template'] = '';
$valueVisibleDataDescription['Template'] = 'None';

$ui->setVisibleData($value);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

echo $ui->getObjectAsJSONString();
?>