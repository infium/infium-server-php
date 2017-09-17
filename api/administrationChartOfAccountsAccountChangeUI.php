<?php
/*
 * Copyright 2012-2017 Infium AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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