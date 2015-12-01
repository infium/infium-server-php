<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportAuditTrail');

$ui = new UserInterface();

$ui->setTitle('Audit trail');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'reportAuditTrailProcess.php');
$ui->setButtonLabel('Run');
$ui->setTitleBarColorNewWindow($titleBarColorReportAuditTrail);

$ui->addSearchSelection('Type','Type',$baseUrl.'reportAuditTrailSearchType.php');
$ui->addField('Number',NULL,'Document number / Number / Username');

echo $ui->getObjectAsJSONString();
?>