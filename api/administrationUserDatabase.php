<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('User');

if (checkUserAccessBoolean('AdministrationUserDatabaseView')){
	$ui->addLabelValueLink('Change', NULL, 'GET', $baseUrl.'administrationUserDatabaseView.php', NULL, $titleBarColorAdministrationUserDatabase);
}

if (checkUserAccessBoolean('AdministrationUserDatabaseCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'administrationUserDatabaseCreateUI.php', NULL, $titleBarColorAdministrationUserDatabase);
}

echo $ui->getObjectAsJSONString();
?>