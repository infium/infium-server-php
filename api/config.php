<?php
require('functionValidations.php');
require('functionEmail.php');
require('functionDatabase.php');
require('functionExtendedLogging.php');
require('functionUserValidation.php');
require('functionMatchOpenItems.php');
require('functionAuditTrailLog.php');
$now = time();

$baseUrl = 'https://infium-eu.appspot.com/api/';
$emailFrom = 'noreply@infium-eu.appspotmail.com';
$extendedLogging = true;

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

function nextDocumentNumber ($pdo, $documentType){
	dbPrepareExecute($pdo, 'UPDATE Number SET LastNumber=LastNumber+1 WHERE Type=?', array($documentType));
	$results = dbPrepareExecute($pdo, 'SELECT Id, Prefix, LastNumber FROM Number WHERE Type=?', array($documentType));
	
	auditTrailLog($pdo, 'Number', $results[0]['Id'], 'UPDATE');
	
	return $results[0]['Prefix'].$results[0]['LastNumber'];
}


function decimalFormat($value){
	
	$valueFormatted = number_format($value,2,'.',',');
	
	if ($valueFormatted == '-0.00'){
		return '0.00';
	}else{
		return $valueFormatted;
	}
}

function sendMessageToClient($message){
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = $message;
	
	header('Content-type: application/json');
	echo json_encode($response,JSON_PRETTY_PRINT);
}

if ($extendedLogging){
    createExtendedLog();
}
?>