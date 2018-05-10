<?php
/*
 * Copyright 2012-2017 Marcus Hammar
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
