<?php
require('functionValidations.php');
require('functionEmail.php');
require('functionDatabase.php');
require('functionExtendedLogging.php');
require('functionUserValidation.php');
require('functionMatchOpenItems.php');
require('functionAuditTrailLog.php');
require('functionNextDocumentNumber.php');
require('functionDecimalFormat.php');
require('functionSendMessageToClient.php');

$baseUrl = 'https://infium-eu.appspot.com/api/';
$emailFrom = 'noreply@infium-eu.appspotmail.com';
$extendedLogging = true;

$databaseDSN = 'mysql:unix_socket=/cloudsql/infium-eu:eu1';
$databaseUsername = 'root';
$databasePasswd = '';

$titleBarColorCustomer = '#59B750';
$titleBarColorCustomerInvoice = '#59B750';
$titleBarColorCustomerPayment = '#59B750';

$titleBarColorVendor = '#4169B9';
$titleBarColorVendorInvoice = '#4169B9';
$titleBarColorVendorPaymentList = '#4169B9';
$titleBarColorVendorPaymentCompleted = '#4169B9';

$titleBarColorGeneralLedger = '#ED462F';
$titleBarColorGeneralLedgerJournalVoucher = '#ED462F';
$titleBarColorGeneralLedgerClearing = '#ED462F';

$titleBarColorReport = '#F5A031';
$titleBarColorReportProfitAndLoss = '#F5A031';
$titleBarColorReportBalanceSheet = '#F5A031';
$titleBarColorReportGeneralLedger = '#F5A031';
$titleBarColorReportTax = '#F5A031';
$titleBarColorReportAuditTrail = '#F5A031';

$titleBarColorAdministration = '#E54E9A';
$titleBarColorAdministrationCustomerDatabase = '#E54E9A';
$titleBarColorAdministrationVendorDatabase = '#E54E9A';
$titleBarColorAdministrationArticleDatabase = '#E54E9A';
$titleBarColorAdministrationUserDatabase = '#E54E9A';
$titleBarColorAdministrationChartOfAccounts = '#E54E9A';
$titleBarColorAdministrationProperty = '#E54E9A';

if ($extendedLogging){
    createExtendedLog();
}
?>