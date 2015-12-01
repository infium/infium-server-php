<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportTax');

$ui = new UserInterface();

$ui->setTitle('Tax');

if (checkUserAccessBoolean('ReportTax')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'reportTaxCreateUI.php', NULL, $titleBarColorReportTax, NULL, 'f067', $titleBarColorReportTax);
}

if (checkUserAccessBoolean('ReportTax')){
	$ui->addLabelValueLink('View', NULL, 'GET', $baseUrl.'reportTaxViewUI.php', NULL, $titleBarColorReportTax, NULL, 'f06e', $titleBarColorReportTax);
}

if (checkUserAccessBoolean('ReportTax')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET', $baseUrl.'reportTaxReverseUI.php', NULL, $titleBarColorReportTax, NULL, 'f0e2', $titleBarColorReportTax);
}

echo $ui->getObjectAsJSONString();
?>