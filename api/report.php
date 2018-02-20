<?php
/*
 * Copyright 2012-2017 Infium AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
