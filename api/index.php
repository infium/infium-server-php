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

$pdo = createPdo();

$companyName = dbPrepareExecute($pdo, 'SELECT Value FROM Property WHERE Property = ?', array('CompanyName'));

if ($companyName[0]['Value'] != ''){
	$ui->setTitle($companyName[0]['Value']);
}else{
	$ui->setTitle('Menu');
}

$noAccess = True;
if (checkUserAccessBoolean('CustomerInvoiceCreate')||checkUserAccessBoolean('CustomerInvoiceEmail')||checkUserAccessBoolean('CustomerInvoiceReverse')||checkUserAccessBoolean('CustomerInvoiceView')||checkUserAccessBoolean('CustomerPaymentCreate')||checkUserAccessBoolean('CustomerPaymentReverse')||checkUserAccessBoolean('CustomerPaymentView')){
	$ui->addLabelValueLink('Customer', NULL, 'GET', $baseUrl.'customer.php', NULL, $titleBarColorCustomer, NULL, 'f0c0', $titleBarColorCustomer);
	$noAccess = False;
}

if (checkUserAccessBoolean('VendorInvoiceCreate')||checkUserAccessBoolean('VendorInvoiceReverse')||checkUserAccessBoolean('VendorInvoiceView')||checkUserAccessBoolean('VendorPaymentCompletedCreate')||checkUserAccessBoolean('VendorPaymentCompletedReverse')||checkUserAccessBoolean('VendorPaymentCompletedView')||checkUserAccessBoolean('VendorPaymentListCreate')||checkUserAccessBoolean('VendorPaymentListReverse')||checkUserAccessBoolean('VendorPaymentListView')){
	$ui->addLabelValueLink('Vendor', NULL, 'GET', $baseUrl.'vendor.php', NULL, $titleBarColorVendor, NULL, 'f0d1', $titleBarColorVendor);
	$noAccess = False;
}

if (checkUserAccessBoolean('GeneralLedgerJournalVoucherCreate')||checkUserAccessBoolean('GeneralLedgerJournalVoucherView')){
	$ui->addLabelValueLink('General ledger', NULL, 'GET', $baseUrl.'generalLedger.php', NULL, $titleBarColorGeneralLedger, NULL, 'f03a', $titleBarColorGeneralLedger);
	$noAccess = False;
}

if (checkUserAccessBoolean('ReportAuditTrail')||checkUserAccessBoolean('ReportBalanceSheet')||checkUserAccessBoolean('ReportGeneralLedger')||checkUserAccessBoolean('ReportProfitAndLoss')||checkUserAccessBoolean('ReportTax')){
	$ui->addLabelValueLink('Reports', NULL, 'GET', $baseUrl.'report.php', NULL, $titleBarColorReport, NULL, 'f080', $titleBarColorReport);
	$noAccess = False;
}

if (checkUserAccessBoolean('AdministrationArticleDatabase')||checkUserAccessBoolean('AdministrationChartOfAccountsAccountChange')||checkUserAccessBoolean('AdministrationChartOfAccountsAccountCreate')||checkUserAccessBoolean('AdministrationChartOfAccountsBalanceCarryForward')||checkUserAccessBoolean('AdministrationChartOfAccountsReportTemplateChange')||checkUserAccessBoolean('AdministrationChartOfAccountsReportTemplateCreate')||checkUserAccessBoolean('AdministrationChartOfAccountsYearChange')||checkUserAccessBoolean('AdministrationChartOfAccountsYearCreate')||checkUserAccessBoolean('AdministrationCustomerDatabase')||checkUserAccessBoolean('AdministrationProperty')||checkUserAccessBoolean('AdministrationUserDatabaseAccessChange')||checkUserAccessBoolean('AdministrationUserDatabaseCreate')||checkUserAccessBoolean('AdministrationUserDatabasePasswordChange')||checkUserAccessBoolean('AdministrationUserDatabaseView')||checkUserAccessBoolean('AdministrationVendorDatabase')){
	$ui->addLabelValueLink('Administration', NULL, 'GET', $baseUrl.'administration.php', NULL, $titleBarColorAdministration, NULL, 'f013', $titleBarColorAdministration);
	$noAccess = False;
}

if ($noAccess == True){
	$ui->addLabelValueLink('You have no access');
}

echo $ui->getObjectAsJSONString();
?>
