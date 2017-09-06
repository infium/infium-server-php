<?php
require('functionValidations.php');
require('functionEmail.php');
require('functionDatabase.php');
require('functionExtendedLogging.php');
require('functionUserValidation.php');

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

function auditTrailLog($pdo, $table, $tableId, $operation){
	global $now;
	global $testing;
	
	if (!preg_match('/^[a-zA-Z]{1,64}$/', $table)){
		throw new Exception('The table must consist of the letters A-Z or a-z. No other characters.');
	}
	
	if (($operation == 'INSERT')||($operation == 'UPDATE')){
		$data = dbPrepareExecute($pdo, 'SELECT * FROM '.$table.' WHERE Id=?', array($tableId));
		
		foreach($data[0] as $key => $value){
			if (($key == 'PasswordSalt')||($key == 'PasswordEncrypted')){
				$data2[$key] = '*';
			}else{
				$data2[$key] = $value;
			}
		}
		
		$dataJSON = json_encode($data2);
	}else{
		$dataJSON = NULL;
	}
	
	if (isset($testing) && $testing == True){
		$user = 0;
	}else{
		$user = getUser($pdo);
	}
	
	dbPrepareExecute($pdo, "INSERT INTO AuditTrail (`Table`, `TableId`, `Operation`, `Data`, `Time`, `User`, `IP`) VALUES (?, ?, ?, ?, ?, ?, ?)", array($table, $tableId, $operation, $dataJSON, date("Y-m-d H:i:s", $now), $user, $_SERVER['REMOTE_ADDR']));
}

function sendMessageToClient($message){
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = $message;
	
	header('Content-type: application/json');
	echo json_encode($response,JSON_PRETTY_PRINT);
}

function matchOpenItems ($pdo, $date, $account, $subAccount, $reference){
	$sum = 0;
	$results = dbPrepareExecute($pdo, 'SELECT Id, Amount FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? AND SubAccountNumber=? AND ClearingReference=? AND ClearingDate IS NULL', array($account, $subAccount, $reference));
	
	foreach ($results as $row){
		$sum += $row['Amount'];
	}
	
	if (($sum == 0)&&(count($results) > 0)){
		
		$clearingNumber = nextDocumentNumber($pdo, 'GeneralLedgerAccountClearing');
		
		dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountClearing (Number, BookingDate) VALUES (?, ?)', array($clearingNumber, $date));
		$clearingNumberId = $pdo->lastInsertId();
		auditTrailLog($pdo, 'GeneralLedgerAccountClearing', $clearingNumberId, 'INSERT');
		
		foreach ($results as $row){
			dbPrepareExecute($pdo, 'UPDATE GeneralLedgerAccountBookingRow SET ClearingDate=?, ClearingNumber=? WHERE Id=?', array($date, $clearingNumber, $row['Id']));
			auditTrailLog($pdo, 'GeneralLedgerAccountBookingRow', $row['Id'], 'UPDATE');
			
			dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountClearingRow (ParentId, Number, BookingRowId) VALUES (?, ?, ?)', array($clearingNumberId, $clearingNumber, $row['Id']));
			auditTrailLog($pdo, 'GeneralLedgerAccountClearingRow', $pdo->lastInsertId(), 'INSERT');
		}
	}
}

if ($extendedLogging){
    createExtendedLog();
}
?>