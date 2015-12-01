<?php
require('config.php');

checkUserAccess('AdministrationProperty');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$pdo = createPdo();
	
	$pdo->exec('START TRANSACTION');
	
	$readOnly = dbPrepareExecute($pdo, 'SELECT ReadOnly FROM Property WHERE Id=?', array($inputHidden['Id']));
	
	if ($readOnly[0]['ReadOnly'] == True){
		throw new Exception('This property is read only. No update was made.');
	}
	
	dbPrepareExecute($pdo, 'UPDATE Property SET Value=? WHERE Id=?', array($inputVisible['Value'], $inputHidden['Id']));
	
	auditTrailLog($pdo, 'Property', $inputHidden['Id'], 'UPDATE');
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Property changed';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>