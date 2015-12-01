<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationCustomerDatabase');

$ui = new UserInterface();

$ui->setTitle('Customer');

if (checkUserAccessBoolean('AdministrationCustomerDatabase')){
	$ui->addLabelValueLink('Search', NULL, 'GET', $baseUrl.'administrationCustomerDatabaseSearchUI.php', NULL, $titleBarColorAdministrationCustomerDatabase);
}

if (checkUserAccessBoolean('AdministrationCustomerDatabase')){
	$ui->addLabelValueLink('Add', NULL, 'GET', $baseUrl.'administrationCustomerDatabaseAddUI.php', NULL, $titleBarColorAdministrationCustomerDatabase);
}

echo $ui->getObjectAsJSONString();
?>