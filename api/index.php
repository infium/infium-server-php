<?php
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