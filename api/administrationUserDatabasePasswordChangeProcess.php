<?php
require('config.php');

checkUserAccess('AdministrationUserDatabasePasswordChange');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	$inputHidden = json_decode(file_get_contents('php://input'), TRUE)['HiddenData'];

	$pdo = createPdo();
	
	$pdo->exec('START TRANSACTION');
	
	$passwordSalt = md5(mt_rand().mt_rand().mt_rand().mt_rand().mt_rand());
	$passwordEncrypted = md5($inputVisible['Password'].$passwordSalt);
	
	dbPrepareExecute($pdo, 'UPDATE User SET PasswordSalt=?, PasswordEncrypted=? WHERE Id=?', array($passwordSalt, $passwordEncrypted, $inputHidden['Id']));
	
	auditTrailLog($pdo, 'User', $inputHidden['Id'], 'UPDATE');
	
	$pdo->exec('COMMIT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Password changed';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>