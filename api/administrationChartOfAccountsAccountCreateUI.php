<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationChartOfAccountsAccountCreate');

$ui = new UserInterface();

$ui->setTitle('Account');

$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationChartOfAccountsAccountCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addLabelValueLink('Year: '.$_GET['Year']);

$valueHiddenData['Year'] = $_GET['Year'];

$ui->addField('Number', NULL, 'Number');

$ui->addField('Description', NULL, 'Description');

$ui->addSearchSelection('Type','Type',$baseUrl.'administrationChartOfAccountsAccountChangeTypeSearchSelection.php');
$valueVisibleData['Type'] = '';
$valueVisibleDataDescription['Type'] = '';

$ui->addLabelTrueFalse('ShowInVendorInvoice','Show in vendor invoice');
$valueVisibleData['ShowInVendorInvoice'] = False;

$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);
$ui->setHiddenData($valueHiddenData);

echo $ui->getObjectAsJSONString();
?>