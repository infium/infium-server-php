<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationVendorDatabase');

$ui = new UserInterface();

$ui->setTitle('Vendor');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationVendorDatabaseSearchProcess.php');
$ui->setButtonLabel('Search');
$ui->setTitleBarColorNewWindow($titleBarColorAdministrationVendorDatabase);

$ui->addField('Query',NULL,'Search query (optional)');
$ui->addLabelTrueFalse('OnlyActive','Only active');
$value['OnlyActive'] = True;

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>