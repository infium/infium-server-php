<?php
require('config.php');

checkUserAccess('AdministrationUserDatabaseAccessChange');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$pdo = createPdo();
	
	$pdo->exec('START TRANSACTION');
	
	$access = array();
	
	$results2 = dbPrepareExecute($pdo, 'SELECT ResourceName FROM UserAccessAvailible ORDER BY ResourceName', array());
		
	foreach($results2 as $row2){
		if (isset($inputVisible[$row2['ResourceName']])){
			if ($inputVisible[$row2['ResourceName']] == True){
				$access[] = $row2['ResourceName'];
			}
		}
	}
	
	dbPrepareExecute($pdo, 'UPDATE User SET Access=? WHERE Id=?', array(json_encode($access), $inputHidden['Id']));
	
	auditTrailLog($pdo, 'User', $inputHidden['Id'], 'UPDATE');
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Access changed';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>