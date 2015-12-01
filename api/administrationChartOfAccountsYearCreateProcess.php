<?php
require('config.php');

checkUserAccess('AdministrationChartOfAccountsYearCreate');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();
	
	$pdo->exec('START TRANSACTION');
	
	if (!preg_match('/^[0-9]{4}$/', $inputVisible['Year'])){
		throw new Exception('The year needs to be in the format NNNN.');
	}
	
	$yearPlusOne = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfYears FROM GeneralLedgerYear WHERE Year=?', array($inputVisible['Year']+1));
	$yearMinusOne = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfYears FROM GeneralLedgerYear WHERE Year=?', array($inputVisible['Year']-1));
	$currentYear = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfYears FROM GeneralLedgerYear WHERE Year=?', array($inputVisible['Year']));
	$upcomingOpenYears = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfYears FROM GeneralLedgerYear WHERE Status=\'Closed\' AND Year>?', array($inputVisible['Year']));

	if ($currentYear[0]['NumberOfYears'] == 1){
		throw new Exception('The year already exist.');
	}
	
	if (!(($yearPlusOne[0]['NumberOfYears'] == 1)||($yearMinusOne[0]['NumberOfYears'] == 1))){
		throw new Exception('The year needs to be an increment of one or decrement of one to a year that already exist in the system. If the latest year is '.date("Y").' you cannot create '.(date("Y")+2).' until '.(date("Y")+1).' has been created.');
	}
	
	if ($upcomingOpenYears[0]['NumberOfYears'] > 0){
		throw new Exception('If you add a year in the past, all subsequent years needs to be open.');
	}
	
	if ($yearMinusOne[0]['NumberOfYears'] == 1){
		$copyFromYear = $inputVisible['Year'] - 1;
	}else{
		$copyFromYear = $inputVisible['Year'] + 1;
	}
	
	dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerYear (Year, Status) VALUES (?, ?)', array($inputVisible['Year'], 'Open'));
		
	auditTrailLog($pdo, 'GeneralLedgerYear', $pdo->lastInsertId(), 'INSERT');
	
	$results = dbPrepareExecute($pdo, 'SELECT AccountNumber, Description, Type, SubAccountNumber, ShowInVendorInvoice FROM GeneralLedgerAccount WHERE Year=?', array($copyFromYear));
	foreach ($results as $row){
		dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccount (Year, AccountNumber, Description, Type, SubAccountNumber, ShowInVendorInvoice) VALUES (?, ?, ?, ?, ?, ?)', array($inputVisible['Year'], $row['AccountNumber'], $row['Description'], $row['Type'], $row['SubAccountNumber'], $row['ShowInVendorInvoice']));
		auditTrailLog($pdo, 'GeneralLedgerAccount', $pdo->lastInsertId(), 'INSERT');
	}

	function processSection($pdo, $parentIdOld, $parentIdNew, $parentSectionOld, $parentSectionNew){
		$itemsInSection = dbPrepareExecute($pdo, 'SELECT Id, `Order`, SectionDescription, AccountNumber FROM ReportTemplateRow WHERE ParentId=? AND ParentSection=? ORDER BY \'Order\' ASC', array($parentIdOld, $parentSectionOld));
		foreach ($itemsInSection as $row){
			
			dbPrepareExecute($pdo, 'INSERT INTO ReportTemplateRow (ParentId, ParentSection, `Order`, SectionDescription, AccountNumber) VALUES (?, ?, ?, ?, ?)', array($parentIdNew, $parentSectionNew, $row['Order'], $row['SectionDescription'], $row['AccountNumber']));
			
			$parentSectionNewId = $pdo->lastInsertId();
			
			auditTrailLog($pdo, 'ReportTemplateRow', $parentSectionNewId, 'INSERT');
			
			if ($row['SectionDescription'] != NULL){
				processSection($pdo, $parentIdOld, $parentIdNew, $row['Id'], $parentSectionNewId);
			}
		}
	}
	
	$results = dbPrepareExecute($pdo, 'SELECT Id, Type, Code, Description FROM ReportTemplate WHERE Year=?', array($copyFromYear));
	foreach ($results as $row){
		dbPrepareExecute($pdo, 'INSERT INTO ReportTemplate (Year, Type, Code, Description) VALUES (?, ?, ?, ?)', array($inputVisible['Year'], $row['Type'], $row['Code'], $row['Description']));
		
		$parentIdNew = $pdo->lastInsertId();
		auditTrailLog($pdo, 'ReportTemplate', $parentIdNew, 'INSERT');
		
		processSection($pdo, $row['Id'], $parentIdNew, 0, 0);
	}
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Year '.$inputVisible['Year'].' created.';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>