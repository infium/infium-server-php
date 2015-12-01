<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Reports');

if (checkUserAccessBoolean('ReportProfitAndLoss')){
	$ui->addLabelValueLink('Profit and loss statement', NULL, 'GET', $baseUrl.'reportProfitAndLossUI.php', NULL, $titleBarColorReportProfitAndLoss, NULL, 'f022', $titleBarColorReportProfitAndLoss);
}

if (checkUserAccessBoolean('ReportBalanceSheet')){
	$ui->addLabelValueLink('Balance sheet', NULL, 'GET', $baseUrl.'reportBalanceSheetUI.php', NULL, $titleBarColorReportBalanceSheet, NULL, 'f0db', $titleBarColorReportBalanceSheet);
}

if (checkUserAccessBoolean('ReportGeneralLedger')){
	$ui->addLabelValueLink('General ledger', NULL, 'GET', $baseUrl.'reportGeneralLedgerUI.php', NULL, $titleBarColorReportGeneralLedger, NULL, 'f03a', $titleBarColorReportGeneralLedger);
}

if (checkUserAccessBoolean('ReportTax')){
	$ui->addLabelValueLink('Tax', NULL, 'GET', $baseUrl.'reportTax.php', NULL, $titleBarColorReportTax, NULL, 'f074', $titleBarColorReportTax);
}

if (checkUserAccessBoolean('ReportAuditTrail')){
	$ui->addLabelValueLink('Audit trail', NULL, 'GET', $baseUrl.'reportAuditTrailUI.php', NULL, $titleBarColorReportAuditTrail, NULL, 'f1b0', $titleBarColorReportAuditTrail);
}

echo $ui->getObjectAsJSONString();
?>