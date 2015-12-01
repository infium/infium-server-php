<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationUserDatabasePasswordChange');

$ui = new UserInterface();

$ui->setTitle('Change password');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationUserDatabasePasswordChangeProcess.php');
$ui->setButtonLabel('Change');

$ui->addField('Password',NULL,'New password');

$hiddenData['Id'] = $_GET['Id'];
$ui->setHiddenData($hiddenData);

echo $ui->getObjectAsJSONString();
?>