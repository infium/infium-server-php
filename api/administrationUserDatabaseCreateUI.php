<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationUserDatabaseCreate');

$ui = new UserInterface();

$ui->setTitle('New user');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationUserDatabaseCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('Name',NULL,'Name');
$ui->addField('Email',NULL,'E-mail');
$ui->addField('Username',NULL,'Username prefix (before @'.$_SERVER['HTTP_X_CLIENT_LOGIN_COMPANY'].')');
$ui->addField('Password',NULL,'Password');

echo $ui->getObjectAsJSONString();
?>