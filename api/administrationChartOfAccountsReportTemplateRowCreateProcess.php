<?php
require('config.php');

checkUserAccess('AdministrationChartOfAccountsReportTemplateChange');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$pdo = createPdo();
	
	$pdo->exec('START TRANSACTION');
	
	$reportTemplateData = dbPrepareExecute($pdo, 'SELECT Year FROM ReportTemplate WHERE Id=?', array($inputHidden['Id']));
	
	$yearOpen = dbPrepareExecute($pdo, 'SELECT Status FROM GeneralLedgerYear WHERE Year=?', array($reportTemplateData[0]['Year']));
	
	if ($yearOpen[0]['Status'] != 'Open'){
		throw new Exception('The year '.$reportTemplateData[0]['Year'].' is currently not open.');
	}
	
	if (!preg_match('/^[0-9]{1,2}$/', $inputVisible['Order'])){
		throw new Exception('Order needs to be an integer');
	}
	
	if (($inputVisible['Section'] != '')&&($inputVisible['Account'] != '')){
		throw new Exception('You have to choose either a section name or an account number, not both.');
	}

	if (($inputVisible['Section'] == '')&&($inputVisible['Account'] == '')){
		throw new Exception('You need to enter either a section or an account number.');
	}
	
	if ($inputVisible['Account'] != ''){
		$duplicate = dbPrepareExecute($pdo, 'SELECT COUNT(*) as Duplicate FROM ReportTemplateRow WHERE ParentId=? AND AccountNumber=?', array($inputHidden['Id'], $inputVisible['Account']));
		if ($duplicate[0]['Duplicate'] > 0){
			throw new Exception('The account has already been added');
		}
		
		$account = $inputVisible['Account'];
		$section = NULL;
	}
	
	if ($inputVisible['Section'] != ''){
		$account = NULL;
		$section = $inputVisible['Section'];
	}	
	
	dbPrepareExecute($pdo, 'INSERT INTO ReportTemplateRow (ParentId, ParentSection, `Order`, SectionDescription, AccountNumber) VALUES (?, ?, ?, ?, ?)', array($inputHidden['Id'], $inputHidden['ParentSection'], $inputVisible['Order'], $section, $account));
	
	auditTrailLog($pdo, 'ReportTemplateRow', $pdo->lastInsertId(), 'INSERT');
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Row created';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>