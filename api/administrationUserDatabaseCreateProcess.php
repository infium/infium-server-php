<?php
require('config.php');

checkUserAccess('AdministrationUserDatabaseCreate');

try {
	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	
	validateUsername($input['Username']);

	$pdo = createPdo();
	
	$passwordSalt = md5(mt_rand().mt_rand().mt_rand().mt_rand().mt_rand());
	$passwordEncrypted = md5($input['Password'].$passwordSalt);
	
	$stmt = $pdo->prepare('INSERT INTO User (Name, Username, Email, PasswordSalt, PasswordEncrypted, Access) VALUES (?, ?, ?, ?, ?, ?)');
	$stmt->execute(array($input['Name'],$input['Username'], $input['Email'], $passwordSalt, $passwordEncrypted, '[]'));
	
	auditTrailLog($pdo, 'User', $pdo->lastInsertId(), 'INSERT');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Reload';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'User "'.$input['Name'].'" created';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>