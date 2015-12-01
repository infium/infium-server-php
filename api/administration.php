<?php
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