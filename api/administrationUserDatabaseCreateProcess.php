<?php
/*
 * Copyright 2012-2017 Marcus Hammar
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
	$response['Data'][0]['Action'] = 'Pop';
    $response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'User "'.$input['Name'].'" created';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>
