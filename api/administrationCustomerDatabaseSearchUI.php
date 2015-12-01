<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationCustomerDatabase');

$ui = new UserInterface();

$ui->setTitle('Customer');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationCustomerDatabaseSearchProcess.php');
$ui->setButtonLabel('Search');
$ui->setTitleBarColorNewWindow($titleBarColorAdministrationCustomerDatabase);

$ui->addField('Query',NULL,'Search query (optional)');
$ui->addLabelTrueFalse('OnlyActive','Only active');
$value['OnlyActive'] = True;

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>