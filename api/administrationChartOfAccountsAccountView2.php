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

$ui->setTitle('Accounts in '.$_GET['Year']);

$pdo = createPdo();

$accounts = dbPrepareExecute($pdo, 'SELECT Id, AccountNumber, Description FROM GeneralLedgerAccount WHERE Year=? ORDER BY AccountNumber ASC', array($_GET['Year']));

foreach ($accounts as $row){
	$ui->addLabelValueLink($row['AccountNumber'].' '.$row['Description'], NULL, 'GET', $baseUrl.'administrationChartOfAccountsAccountChangeUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationChartOfAccounts);
}

if (checkUserAccessBoolean('AdministrationChartOfAccountsAccountCreate')){
	$ui->addLabelValueLink('Create new...', NULL, 'GET', $baseUrl.'administrationChartOfAccountsAccountCreateUI.php?Year='.$_GET['Year'], NULL, $titleBarColorAdministrationChartOfAccounts);
}

echo $ui->getObjectAsJSONString();
?>
