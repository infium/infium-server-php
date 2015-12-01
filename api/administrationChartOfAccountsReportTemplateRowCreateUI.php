<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationChartOfAccountsReportTemplateChange');

$ui = new UserInterface();

$ui->setTitle('New row');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationChartOfAccountsReportTemplateRowCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('Order',NULL,'Order');

$ui->addField('Section',NULL,'Section');

$ui->addSearchSelection('Account','Account',$baseUrl.'administrationChartOfAccountsReportTemplateRowCreateAccountSearchSelection.php?Id='.$_GET['Id']);

$valueVisibleData['Account'] = '';
$valueVisibleDataDescription['Account'] = 'Using section instead';

$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

$valueHiddenData['Id'] = $_GET['Id'];
$valueHiddenData['ParentSection'] = $_GET['ParentSection'];

$ui->setHiddenData($valueHiddenData);

echo $ui->getObjectAsJSONString();
?>