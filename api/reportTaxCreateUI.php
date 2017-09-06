<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportTax');

$ui = new UserInterface();

$ui->setTitle('Tax');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'reportTaxCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('BookingDate',NULL,'Booking date');

$ui->addLabelHeader('Tax period');

$ui->addField('DateFrom',NULL,'From');
$ui->addField('DateTo',NULL,'To');

$lastDayOfLastMonth = mktime(0,0,0, date('m', time()), 1, date('Y', time())) - (3600 * 24);

$valueVisibleData['BookingDate'] = date('Y-m-d', $lastDayOfLastMonth);
$valueVisibleData['DateFrom'] = date('Y-m', $lastDayOfLastMonth).'-01';
$valueVisibleData['DateTo'] = date('Y-m-d', $lastDayOfLastMonth);

$ui->setVisibleData($valueVisibleData);

echo $ui->getObjectAsJSONString();
?>