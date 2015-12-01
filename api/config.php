<?php
use \google\appengine\api\mail\Message;
$now = time();

// We store the sending e-mail address for automatic documents in the variable $emailFrom.
$emailFrom = 'noreply@infium-eu.appspotmail.com';

$titleBarColorCustomer = '#59B750';
$titleBarColorCustomerInvoice = $titleBarColorCustomer;
$titleBarColorCustomerPayment = $titleBarColorCustomer;

$titleBarColorVendor = '#4169B9';
$titleBarColorVendorInvoice = $titleBarColorVendor;
$titleBarColorVendorPaymentList = $titleBarColorVendor;
$titleBarColorVendorPaymentCompleted = $titleBarColorVendor;

$titleBarColorGeneralLedger = '#ED462F';
$titleBarColorGeneralLedgerJournalVoucher = $titleBarColorGeneralLedger;

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

syslog(LOG_INFO, 'SERVER_NAME = ' . $_SERVER['SERVER_NAME']);	

if ($_SERVER['SERVER_NAME'] == 'localhost'){
	$baseUrl = 'http://localhost:8888/infium-eu/api/';	
}else{
	$baseUrl = 'https://infium-eu.appspot.com/api/';
}

if ($_SERVER['SERVER_NAME'] == 'sandbox.infium-eu.appspot.com'){
	$baseUrl = 'http://sandbox.infium-eu.appspot.com/api/';
}

function emailSend($from, $to, $subject, $textBody, $htmlBody, $attachmentName){	
	$pdo = createPdo();
	
	$userTokenData = dbPrepareExecute($pdo, 'SELECT UserId FROM UserToken WHERE Token=?', array($_SERVER['HTTP_X_CLIENT_LOGIN_TOKEN']));
	$userData = dbPrepareExecute($pdo, 'SELECT Email FROM User WHERE Id=?', array($userTokenData[0]['UserId']));
	
	$message = new Message();
	$message->setSender($from);
	$message->setReplyTo($userData[0]['Email']);
	$message->addTo($to);
	$message->addCc($userData[0]['Email']);
	$message->setSubject($subject);
	$message->setTextBody($textBody);
	$message->setHtmlBody($htmlBody);
	$message->addAttachment($attachmentName, $htmlBody);
	$message->send();
}

if (isset($_SERVER['HTTP_X_CLIENT_PLATFORM'])){
	syslog(LOG_INFO, 'X-Client-Platform = ' . $_SERVER['HTTP_X_CLIENT_PLATFORM']);	
}

if (isset($_SERVER['HTTP_X_CLIENT_PLATFORM_VERSION'])){
	syslog(LOG_INFO, 'X-Client-Platform-Version = ' . $_SERVER['HTTP_X_CLIENT_PLATFORM_VERSION']);
}

if (isset($_SERVER['HTTP_X_CLIENT_PLATFORM_DEVICE'])){
	syslog(LOG_INFO, 'X-Client-Platform-Device = ' . $_SERVER['HTTP_X_CLIENT_PLATFORM_DEVICE']);
}

if (isset($_SERVER['HTTP_X_CLIENT_PLATFORM_LANGUAGE'])){
	syslog(LOG_INFO, 'X-Client-Platform-Language = ' . $_SERVER['HTTP_X_CLIENT_PLATFORM_LANGUAGE']);
}

if (isset($_SERVER['HTTP_X_CLIENT_APP_VERSION'])){
	syslog(LOG_INFO, 'X-Client-App-Version = ' . $_SERVER['HTTP_X_CLIENT_APP_VERSION']);
}

function createPdo($db = NULL){
	
	if ($db != NULL){
		$dbName = $db;
	}else{
		$company = $_SERVER['HTTP_X_CLIENT_LOGIN_COMPANY'];
	
		if (!preg_match('/^[0-9]{6}$/', $company)){
			throw new Exception('The format of the company must be NNNNNN.');
		}
	
		$dbName = 'Company_'.$company;
	}
	
	if ($_SERVER['SERVER_NAME'] == 'localhost'){
		return new PDO('mysql:host=localhost;dbname='.$dbName.';port=8889;charset=utf8', 'root', 'root', array(PDO::ATTR_TIMEOUT => '10',PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}else{
		return new PDO('mysql:unix_socket=/cloudsql/infium-eu:eu1;dbname='.$dbName.';charset=utf8', 'root', '', array(PDO::ATTR_TIMEOUT => '10',PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}
}

function dbPrepareExecute($pdo, $prepare, $dataArray = array()){
	$stmt = $pdo->prepare($prepare);
	$stmt->execute($dataArray);
	try{
		return $stmt->fetchAll(PDO::FETCH_ASSOC);		
	}catch(Exception $e){
		return NULL;
	}
}

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
	
	if (!preg_match('/^[a-zA-Z]{1,32}$/', $table)){
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

function validateDate($date){
	if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)){
		throw new Exception('The date needs to be in the format YYYY-MM-DD.');
	}

	if (!checkdate(substr($date,5,2), substr($date,8,2), substr($date,0,4))){
		throw new Exception('The date '.$date.' does not exist.');
	}	
}

function validateCustomerNumber($pdo, $customerNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumCustomer FROM Customer WHERE Number=?', array($customerNumber));
	
	if ($results[0]['NumCustomer'] != 1){
		throw new Exception('A valid customer needs to be selected.');
	}
}

function validateVendorNumber($pdo, $vendorNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumVendor FROM Vendor WHERE Number=?', array($vendorNumber));
	
	if ($results[0]['NumVendor'] != 1){
		throw new Exception('A valid vendor needs to be selected.');
	}
}

function validateArticleNumber($pdo, $articleNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumArticle FROM Article WHERE Number=?', array($articleNumber));
	
	if ($results[0]['NumArticle'] != 1){
		throw new Exception('A valid article needs to be selected.');
	}
}

function validateAccountNumber($pdo, $year, $accountNumber){
	if (!preg_match('/^[0-9]{4}$/', $accountNumber)){
		throw new Exception('The account needs to be in the format NNNN.');
	}
	
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumAccount FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?', array($year, $accountNumber));
	
	if ($results[0]['NumAccount'] != 1){
		throw new Exception('A valid account that exist in the booking date year needs to be selected.');
	}
}

function validateNewAccountNumber($accountNumber){
	if (!preg_match('/^[0-9]{4}$/', $accountNumber)){
		throw new Exception('The account needs to be in the format NNNN.');
	}
}

function validateNumber($number){
	$number = str_replace (',', '.', $number);
	if (!preg_match('/^\-{0,1}[0-9]{1,16}\.{0,1}[0-9]{0,2}$/', $number)){
		throw new Exception('Numbers need to be in the format (-)NNNNN.NN');
	}
}

function validateUsername($username){
	if (!preg_match('/^[a-z0-9]{1,32}$/', $username)){
		throw new Exception('Usernames need to be between 1 and 32 characters and may only consist of lowercase "a" to "z" and/or "0" to "9"');
	}
}

function validateCustomerInvoiceDocumentNumber($pdo, $documentNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM CustomerInvoice WHERE Number=?', array($documentNumber));
	
	if ($results[0]['NumberOfDocuments'] != 1){
		throw new Exception('A valid document number needs to be entered.');
	}
}

function validateCustomerTransactionDocumentNumber($pdo, $documentNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM CustomerTransaction WHERE Number=?', array($documentNumber));
	
	if ($results[0]['NumberOfDocuments'] != 1){
		throw new Exception('A valid document number needs to be entered.');
	}
}

function validateCustomerPaymentDocumentNumber($pdo, $documentNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM CustomerPayment WHERE Number=?', array($documentNumber));
	
	if ($results[0]['NumberOfDocuments'] != 1){
		throw new Exception('A valid document number needs to be entered.');
	}
}

function validateVendorInvoiceDocumentNumber($pdo, $documentNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM VendorInvoice WHERE Number=?', array($documentNumber));
	
	if ($results[0]['NumberOfDocuments'] != 1){
		throw new Exception('A valid document number needs to be entered.');
	}
}

function validateVendorTransactionDocumentNumber($pdo, $documentNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM VendorTransaction WHERE Number=?', array($documentNumber));
	
	if ($results[0]['NumberOfDocuments'] != 1){
		throw new Exception('A valid document number needs to be entered.');
	}
}

function validateVendorPaymentListDocumentNumber($pdo, $documentNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM VendorPaymentList WHERE Number=?', array($documentNumber));
	
	if ($results[0]['NumberOfDocuments'] != 1){
		throw new Exception('A valid document number needs to be entered.');
	}
}

function validateVendorPaymentCompletedDocumentNumber($pdo, $documentNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM VendorPaymentCompleted WHERE Number=?', array($documentNumber));
	
	if ($results[0]['NumberOfDocuments'] != 1){
		throw new Exception('A valid document number needs to be entered.');
	}
}

function validateTaxReportDocumentNumber($pdo, $documentNumber){
	$results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM TaxReport WHERE Number=?', array($documentNumber));
	
	if ($results[0]['NumberOfDocuments'] != 1){
		throw new Exception('A valid document number needs to be entered.');
	}
}
?>