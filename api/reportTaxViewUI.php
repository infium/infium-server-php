<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportTax');

$ui = new UserInterface();

$ui->setTitle('View');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'reportTaxViewProcess.php');
$ui->setButtonLabel('Search');
$ui->setTitleBarColorNewWindow($titleBarColorReportTax);

$ui->addLabelTrueFalse('Active','Active');
$value['Active'] = True;

$ui->addLabelTrueFalse('Reversal','Reversal');
$value['Reversal'] = True;

$ui->addLabelTrueFalse('Reversed','Reversed');
$value['Reversed'] = True;

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>