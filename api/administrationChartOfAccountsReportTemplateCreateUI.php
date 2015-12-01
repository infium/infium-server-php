<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationChartOfAccountsReportTemplateCreate');

$ui = new UserInterface();

$ui->setTitle('Report template');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationChartOfAccountsReportTemplateCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('Year',NULL,'Year');

$ui->addSearchSelection('Type','Type',$baseUrl.'administrationChartOfAccountsReportTemplateCreateTypeSearchSelection.php');

$valueVisibleData['Type'] = 'BS';
$valueVisibleDataDescription['Type'] = 'Balance sheet';

$ui->addField('Description',NULL,'Description');

$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

echo $ui->getObjectAsJSONString();
?>