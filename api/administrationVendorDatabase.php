<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationVendorDatabase');

$ui = new UserInterface();

$ui->setTitle('Vendor');

if (checkUserAccessBoolean('AdministrationVendorDatabase')){
	$ui->addLabelValueLink('Search', NULL, 'GET', $baseUrl.'administrationVendorDatabaseSearchUI.php', NULL, $titleBarColorAdministrationVendorDatabase);
}

if (checkUserAccessBoolean('AdministrationVendorDatabase')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'administrationVendorDatabaseCreateUI.php', NULL, $titleBarColorAdministrationVendorDatabase);
}

echo $ui->getObjectAsJSONString();
?>