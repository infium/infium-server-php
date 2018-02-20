<?php
/*
 * Copyright 2012-2017 Infium AB
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
require('classUserInterface.php');

checkUserAccess('AdministrationUserDatabaseView');

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Name, Username FROM User WHERE Id = ?');
$stmt->execute(array($_GET['Id']));
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui = new UserInterface();

$ui->setTitle('User');

$ui->addLabelValueLink('Name: '.$results[0]['Name']);
$ui->addLabelValueLink('Username: '.$results[0]['Username'].'@'.$_SERVER['HTTP_X_CLIENT_LOGIN_COMPANY']);

if (checkUserAccessBoolean('AdministrationUserDatabaseAccessChange')){
	$ui->addLabelValueLink('Change access', NULL, 'GET', $baseUrl.'administrationUserDatabaseAccessChangeUI.php?Id='.$_GET['Id'], NULL, $titleBarColorAdministrationUserDatabase);
}

if (checkUserAccessBoolean('AdministrationUserDatabasePasswordChange')){
	$ui->addLabelValueLink('Change password', NULL, 'GET', $baseUrl.'administrationUserDatabasePasswordChangeUI.php?Id='.$_GET['Id'], NULL, $titleBarColorAdministrationUserDatabase);
}

echo $ui->getObjectAsJSONString();
?>
