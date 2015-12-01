<?php
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