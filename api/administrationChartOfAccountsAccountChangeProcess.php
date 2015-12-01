<?php
require('config.php');

checkUserAccess('AdministrationChartOfAccountsAccountChange');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];
	
	$pdo = createPdo();
		
	$pdo->exec('START TRANSACTION');
	
	$accountYear = dbPrepareExecute($pdo, 'SELECT Year FROM GeneralLedgerAccount WHERE Id=?', array($inputHidden['Id']));
	
	$yearOpen = dbPrepareExecute($pdo, 'SELECT Status FROM GeneralLedgerYear WHERE Year=?', array($accountYear[0]['Year']));
	
	if ($yearOpen[0]['Status'] != 'Open'){
		throw new Exception('The year '.$accountYear[0]['Year'].' is currently not open.');
	}
	
	dbPrepareExecute($pdo, 'UPDATE GeneralLedgerAccount SET Description=?, Type=?, ShowInVendorInvoice=? WHERE Id=?', array($inputVisible['Description'], $inputVisible['Type'], $inputVisible['ShowInVendorInvoice'], $inputHidden['Id']));
	
	auditTrailLog($pdo, 'GeneralLedgerAccount', $inputHidden['Id'], 'UPDATE');
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Account changed';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>