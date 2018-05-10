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

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Administration');

if (checkUserAccessBoolean('AdministrationCustomerDatabase')){
	$ui->addLabelValueLink('Customer database', NULL, 'GET', $baseUrl.'administrationCustomerDatabase.php', NULL, $titleBarColorAdministrationCustomerDatabase, NULL, 'f0c0', $titleBarColorAdministrationCustomerDatabase);
}

if (checkUserAccessBoolean('AdministrationVendorDatabase')){
	$ui->addLabelValueLink('Vendor database', NULL, 'GET', $baseUrl.'administrationVendorDatabase.php', NULL, $titleBarColorAdministrationVendorDatabase, NULL, 'f0d1', $titleBarColorAdministrationVendorDatabase);
}

if (checkUserAccessBoolean('AdministrationArticleDatabase')){
	$ui->addLabelValueLink('Article database', NULL, 'GET', $baseUrl.'administrationArticleDatabase.php', NULL, $titleBarColorAdministrationArticleDatabase, NULL, 'f219', $titleBarColorAdministrationArticleDatabase);
}

if (checkUserAccessBoolean('AdministrationUserDatabaseAccessChange')||checkUserAccessBoolean('AdministrationUserDatabaseCreate')||checkUserAccessBoolean('AdministrationUserDatabasePasswordChange')||checkUserAccessBoolean('AdministrationUserDatabaseView')){
	$ui->addLabelValueLink('User database', NULL, 'GET', $baseUrl.'administrationUserDatabase.php', NULL, $titleBarColorAdministrationUserDatabase, NULL, 'f084', $titleBarColorAdministrationUserDatabase);
}

if (checkUserAccessBoolean('AdministrationChartOfAccountsAccountChange')||checkUserAccessBoolean('AdministrationChartOfAccountsAccountCreate')||checkUserAccessBoolean('AdministrationChartOfAccountsBalanceCarryForward')||checkUserAccessBoolean('AdministrationChartOfAccountsReportTemplateChange')||checkUserAccessBoolean('AdministrationChartOfAccountsReportTemplateCreate')||checkUserAccessBoolean('AdministrationChartOfAccountsYearChange')||checkUserAccessBoolean('AdministrationChartOfAccountsYearCreate')){
	$ui->addLabelValueLink('Chart of accounts', NULL, 'GET', $baseUrl.'administrationChartOfAccounts.php', NULL, $titleBarColorAdministrationChartOfAccounts, NULL, 'f03a', $titleBarColorAdministrationChartOfAccounts);
}

if (checkUserAccessBoolean('AdministrationProperty')){
	$ui->addLabelValueLink('Properties', NULL, 'GET', $baseUrl.'administrationProperty.php', NULL, $titleBarColorAdministrationProperty, NULL, 'f02b', $titleBarColorAdministrationProperty);
}

echo $ui->getObjectAsJSONString();
?>
