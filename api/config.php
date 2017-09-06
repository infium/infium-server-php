<?php
require('functionValidations.php');
require('functionEmail.php');
require('functionDatabase.php');
require('functionExtendedLogging.php');

$now = time();

$baseUrl = 'https://infium-eu.appspot.com/api/';
// We store the sending e-mail address for automatic documents in the variable $emailFrom.
$emailFrom = 'noreply@infium-eu.appspotmail.com';
$extendedLogging = true;

$titleBarColorCustomer = '#59B750';
$titleBarColorCustomerInvoice = $titleBarColorCustomer;
$titleBarColorCustomerPayment = $titleBarColorCustomer;

$titleBarColorVendor = '#4169B9';
$titleBarColorVendorInvoice = $titleBarColorVendor;
$titleBarColorVendorPaymentList = $titleBarColorVendor;
$titleBarColorVendorPaymentCompleted = $titleBarColorVendor;

$titleBarColorGeneralLedger = '#ED462F';
$titleBarColorGeneralLedgerJournalVoucher = $titleBarColorGeneralLedger;
$titleBarColorGeneralLedgerClearing = $titleBarColorGeneralLedger;

$titleBarColorReport = '#F5A031';
$titleBarColorReportProfitAndLoss = $titleBarColorReport;
$titleBarColorReportBalanceSheet = $titleBarColorReport;
$titleBarColorReportGeneralLedger = $titleBarColorReport;
$titleBarColorReportTax = $titleBarColorReport;
$titleBarColorReportAuditTrail = $titleBarColorReport;

$titleBarColorAdministration = '#E54E9A';
$titleBarColorAdministrationCustomerDatabase = $titleBarColorAdministration;
$titleBarColorAdministrationVendorDatabase = $titleBarColorAdministration;
$titleBarColorAdministrationArticleDatabase = $titleBarColorAdministration;
$titleBarColorAdministrationUserDatabase = $titleBarColorAdministration;
$titleBarColorAdministrationChartOfAccounts = $titleBarColorAdministration;
$titleBarColorAdministrationProperty = $titleBarColorAdministration;

function nextAuditId ($pdo, $documentType){
	return nextDocumentNumber($pdo, $documentType);
}

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

function getUser($pdo){
	$results = dbPrepareExecute($pdo, 'SELECT UserId FROM UserToken WHERE Token=?', array($_SERVER['HTTP_X_CLIENT_LOGIN_TOKEN']));
		
	if (count($results) != 1){
		throw new Exception('User token cannot be found');
	}
	
	return $results[0]['UserId'];		
}

function getUserName($pdo, $userId){
	if ($userId == 0){
		return 'Initial system setup';
	}
	
	$results = dbPrepareExecute($pdo, 'SELECT Name FROM User WHERE Id=?', array($userId));
	
	if (count($results) != 1){
		throw new Exception('User cannot be found');
	}
	
	return $results[0]['Name'];		
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

function checkUserAccess($resourceName = NULL, $throwException = False){
	$pdo = createPdo();
	
	$stmt = $pdo->prepare('SELECT COUNT(*) as MatchingUsers FROM UserToken WHERE Token=?');
	$stmt->execute(array($_SERVER['HTTP_X_CLIENT_LOGIN_TOKEN']));
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($results[0]['MatchingUsers'] != 1){
		
		if ($throwException == True){
			throw new Exception('User token cannot be found');			
		}else{
			$response['Response'] = 'LocalActions';
			$response['Data'][0]['Action'] = 'Logout';
			$response['Data'][1]['Action'] = 'MessageFlash';
			$response['Data'][1]['Message'] = 'User is not logged in';
		
			header('Content-type: application/json');
			echo json_encode($response,JSON_PRETTY_PRINT);
		
			exit();
		}
	}
	
	if ($resourceName != NULL){
		$stmt2 = $pdo->prepare('SELECT UserId FROM UserToken WHERE Token=?');
		$stmt2->execute(array($_SERVER['HTTP_X_CLIENT_LOGIN_TOKEN']));
		$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
		
		$results3 = dbPrepareExecute($pdo, 'SELECT Access FROM User WHERE Id=?', array($results2[0]['UserId']));
		
		$accessArray = json_decode($results3[0]['Access'], TRUE);
		
		$accessGranted = False;
		
		foreach($accessArray as $access){
			if ($resourceName == $access){
				$accessGranted = True;
			}
		}
		
		if ($accessGranted == False){
			
			if ($throwException == True){
				throw new Exception('User has no access to this');
			}else{
				$response['Response'] = 'LocalActions';
				$response['Data'][0]['Action'] = 'MessageFlash';
				$response['Data'][0]['Message'] = 'User has no access to this';
				
				header('Content-type: application/json');
				echo json_encode($response,JSON_PRETTY_PRINT);
				
				exit();
			}
		}
	}
}

function checkUserAccessBoolean($resourceName = NULL) {
	try {
		checkUserAccess($resourceName, True);
	} catch (Exception $e) {
		return false;
	}
	return true;
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