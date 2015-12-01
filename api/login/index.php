<?php
require('../config.php');

$input = json_decode(file_get_contents('php://input'), TRUE);

$pdo = createPdo();

$pdo->exec('START TRANSACTION');

$passwordSalt = dbPrepareExecute($pdo, 'SELECT PasswordSalt FROM User WHERE Username=?', array($input['Username']));

$passwordEncrypted = md5($input['Password'].$passwordSalt[0]['PasswordSalt']);

$stmt = $pdo->prepare('SELECT COUNT(*) as MatchingUsers FROM User WHERE Username=? AND PasswordEncrypted=?');
$stmt->execute(array($input['Username'],$passwordEncrypted));
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

log($results[0]['MatchingUsers']);

if ($results[0]['MatchingUsers'] == 1){
	
	$token = md5(mt_rand().mt_rand().mt_rand().mt_rand().mt_rand());
	
	$stmt2 = $pdo->prepare('SELECT Id FROM User WHERE Username=?');
	$stmt2->execute(array($input['Username']));
	$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
	
	
	
	$stmt3 = $pdo->prepare('INSERT INTO UserToken (UserId, Token, ClientPlatform, ClientPlatformVersion, ClientPlatformDevice, ClientAppVersion) VALUES (?, ?, ?, ?, ?, ?)');
	$stmt3->execute(array($results2[0]['Id'],$token, $_SERVER['HTTP_X_CLIENT_PLATFORM'], $_SERVER['HTTP_X_CLIENT_PLATFORM_VERSION'], $_SERVER['HTTP_X_CLIENT_PLATFORM_DEVICE'], $_SERVER['HTTP_X_CLIENT_APP_VERSION']));
	
	$response['Response'] = 'LoginToken';
	$response['Data']['Token'] = $token;
}else{
	$response['Response'] = 'LoginToken';
}

$pdo->exec('COMMIT');

header('Content-type: application/json');

echo json_encode($response,JSON_PRETTY_PRINT);
?>