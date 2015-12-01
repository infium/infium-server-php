<?php
require('config.php');

checkUserAccess('AdministrationChartOfAccountsAccountCreate');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];
	
	validateNewAccountNumber($inputVisible['Number']);
	
	if ($inputVisible['Type'] == ''){
		throw new Exception('Account type must be selected');
	}
	
	$pdo = createPdo();
	
	$pdo->exec('START TRANSACTION');
	
	$yearOpen = dbPrepareExecute($pdo, 'SELECT Status FROM GeneralLedgerYear WHERE Year=?', array($inputHidden['Year']));
	
	if ($yearOpen[0]['Status'] != 'Open'){
		throw new Exception('The year '.$inputHidden['Year'].' is currently not open.');
	}
	
	dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccount (Year, AccountNumber, Description, Type, SubAccountNumber, ShowInVendorInvoice) VALUES (?, ?, ?, ?, ?, ?)', array($inputHidden['Year'], $inputVisible['Number'], $inputVisible['Description'], $inputVisible['Type'], False, $inputVisible['ShowInVendorInvoice']));
	
	auditTrailLog($pdo, 'GeneralLedgerAccount', $pdo->lastInsertId(), 'INSERT');
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Account created';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>