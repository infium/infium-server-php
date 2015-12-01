<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationChartOfAccountsAccountChange');

$ui = new UserInterface();

$pdo = createPdo();

$account = dbPrepareExecute($pdo, 'SELECT Year, AccountNumber, Description, Type, ShowInVendorInvoice FROM GeneralLedgerAccount WHERE Id=?', array($_GET['Id']));

$ui->setTitle('Account');

if (checkUserAccessBoolean('AdministrationChartOfAccountsAccountChange')){
	$ui->setWindow('Same');
	$ui->setMethod('POST');
	$ui->setUrl($baseUrl.'administrationChartOfAccountsAccountChangeProcess.php');
	$ui->setButtonLabel('Change');
}

$ui->addLabelValueLink('Year: '.$account[0]['Year']);
$ui->addLabelValueLink('Number: '.$account[0]['AccountNumber']);

$ui->addField('Description',NULL,'Description');
$valueVisibleData['Description'] = $account[0]['Description'];

$ui->addSearchSelection('Type','Type',$baseUrl.'administrationChartOfAccountsAccountChangeTypeSearchSelection.php');
$valueVisibleData['Type'] = $account[0]['Type'];
$valueVisibleDataDescription['Type'] = '';
if ($account[0]['Type'] == 'PL'){
	$valueVisibleDataDescription['Type'] = 'Profit and loss';
}
if ($account[0]['Type'] == 'BS'){
	$valueVisibleDataDescription['Type'] = 'Balance sheet';
}

$ui->addLabelTrueFalse('ShowInVendorInvoice','Show in vendor invoice');

if ($account[0]['ShowInVendorInvoice'] == 1){
	$valueVisibleData['ShowInVendorInvoice'] = True;	
}else{
	$valueVisibleData['ShowInVendorInvoice'] = False;
}

$valueHiddenData['Id'] = $_GET['Id'];

$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);
$ui->setHiddenData($valueHiddenData);

echo $ui->getObjectAsJSONString();
?>