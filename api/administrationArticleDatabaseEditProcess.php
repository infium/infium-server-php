<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationArticleDatabase');

try{
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');
	dbPrepareExecute($pdo, 'UPDATE Article SET Active=?, Description=?, TaxGroup=? WHERE Id=?', array($inputVisible['Active'], $inputVisible['Description'], $inputVisible['TaxGroup'], $inputHidden['Id']));
	
	auditTrailLog($pdo, 'Article', $inputHidden['Id'], 'UPDATE');
	
	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Article updated';
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>