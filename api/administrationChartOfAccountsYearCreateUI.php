<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationChartOfAccountsYearCreate');

$ui = new UserInterface();

$ui->setTitle('Years');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationChartOfAccountsYearCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('Year',NULL,'Year');

echo $ui->getObjectAsJSONString();
?>