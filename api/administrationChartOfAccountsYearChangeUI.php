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

checkUserAccess('AdministrationChartOfAccountsYearChange');

$ui = new UserInterface();

$pdo = createPdo();

$year = dbPrepareExecute($pdo, 'SELECT Id, Year, Status FROM GeneralLedgerYear WHERE Id=?', array($_GET['Id']));

$ui->setTitle($year[0]['Year']);
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationChartOfAccountsYearChangeProcess.php');
$ui->setButtonLabel('Change');

$ui->addSearchSelection('Status','Locking status',$baseUrl.'administrationChartOfAccountsYearChangeStatusSearchSelection.php');

$valueVisibleData['Status'] = $year[0]['Status'];
$valueVisibleDataDescription['Status'] = $year[0]['Status'];
$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

$valueHiddenData['Id'] = $year[0]['Id'];
$ui->setHiddenData($valueHiddenData);

echo $ui->getObjectAsJSONString();
?>