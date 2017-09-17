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