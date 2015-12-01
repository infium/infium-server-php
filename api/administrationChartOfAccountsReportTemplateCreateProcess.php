<?php
require('config.php');

checkUserAccess('AdministrationChartOfAccountsReportTemplateCreate');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();
	
	$pdo->exec('START TRANSACTION');
	
	if (!preg_match('/^[0-9]{4}$/', $inputVisible['Year'])){
		throw new Exception('The year needs to be in the format NNNN.');
	}
	
	$year = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfYears FROM GeneralLedgerYear WHERE Year=?', array($inputVisible['Year']));

	if ($year[0]['NumberOfYears'] != 1){
		throw new Exception('The year does not exist.');
	}
	
	$yearOpen = dbPrepareExecute($pdo, 'SELECT Status FROM GeneralLedgerYear WHERE Year=?', array($inputVisible['Year']));
	
	if ($yearOpen[0]['Status'] != 'Open'){
		throw new Exception('The year '.$inputVisible['Year'].' is currently not open.');
	}
	
	
	if (!(($inputVisible['Type'] == 'BS')||($inputVisible['Type'] == 'PL'))){
		throw new Exception('The report template needs to be for either the "Balance sheet" or the "Profit and loss statement".');
	}
	
	dbPrepareExecute($pdo, 'INSERT INTO ReportTemplate (Year, Type, Description) VALUES (?, ?, ?)', array($inputVisible['Year'], $inputVisible['Type'], $inputVisible['Description']));
	
	auditTrailLog($pdo, 'ReportTemplate', $pdo->lastInsertId(), 'INSERT');
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Report template created.';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>