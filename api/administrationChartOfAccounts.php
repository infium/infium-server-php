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

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Chart of accounts');

if (checkUserAccessBoolean('AdministrationChartOfAccountsYearChange')||checkUserAccessBoolean('AdministrationChartOfAccountsYearCreate')){
	$ui->addLabelValueLink('Years', NULL, 'GET', $baseUrl.'administrationChartOfAccountsYear.php', NULL, $titleBarColorAdministrationChartOfAccounts, NULL, 'f018', $titleBarColorAdministrationChartOfAccounts);
}

if (checkUserAccessBoolean('AdministrationChartOfAccountsAccountChange')||checkUserAccessBoolean('AdministrationChartOfAccountsAccountCreate')){
	$ui->addLabelValueLink('Accounts', NULL, 'GET', $baseUrl.'administrationChartOfAccountsAccountView.php', NULL, $titleBarColorAdministrationChartOfAccounts, NULL, 'f03a', $titleBarColorAdministrationChartOfAccounts);
}

if (checkUserAccessBoolean('AdministrationChartOfAccountsReportTemplateChange')||checkUserAccessBoolean('AdministrationChartOfAccountsReportTemplateCreate')){
	$ui->addLabelValueLink('Report templates', NULL, 'GET', $baseUrl.'administrationChartOfAccountsReportTemplate.php', NULL, $titleBarColorAdministrationChartOfAccounts, NULL, 'f0ce', $titleBarColorAdministrationChartOfAccounts);
}

if (checkUserAccessBoolean('AdministrationChartOfAccountsBalanceCarryForward')){
	$ui->addLabelValueLink('Balance carry forward', NULL, 'GET', $baseUrl.'administrationChartOfAccountsBalanceCarryForwardUI.php', NULL, $titleBarColorAdministrationChartOfAccounts, NULL, 'f0c1', $titleBarColorAdministrationChartOfAccounts);
}

echo $ui->getObjectAsJSONString();
?>