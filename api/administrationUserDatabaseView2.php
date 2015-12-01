<?php
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