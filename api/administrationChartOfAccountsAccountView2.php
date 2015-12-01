<?php
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