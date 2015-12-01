<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Years');

if (checkUserAccessBoolean('AdministrationChartOfAccountsYearChange')){
	$pdo = createPdo();
	$years = dbPrepareExecute($pdo, 'SELECT Id, Year, Status FROM GeneralLedgerYear ORDER BY Year DESC', array());

	foreach ($years as $row){
		$ui->addLabelValueLink($row['Year'].' - '.$row['Status'], NULL, 'GET', $baseUrl.'administrationChartOfAccountsYearChangeUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationChartOfAccounts);
	}
}

if (checkUserAccessBoolean('AdministrationChartOfAccountsYearCreate')){
	$ui->addLabelValueLink('Create new...', NULL, 'GET', $baseUrl.'administrationChartOfAccountsYearCreateUI.php', NULL, $titleBarColorAdministrationChartOfAccounts);
}

echo $ui->getObjectAsJSONString();
?>