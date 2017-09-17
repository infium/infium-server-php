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