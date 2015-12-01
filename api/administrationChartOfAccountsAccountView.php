<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationChartOfAccountsAccountChange');

$ui = new UserInterface();

$ui->setTitle('Select year');

$pdo = createPdo();

$years = dbPrepareExecute($pdo, 'SELECT Year FROM GeneralLedgerYear ORDER BY Year DESC', array());

foreach ($years as $row){
	$ui->addLabelValueLink($row['Year'], NULL, 'GET', $baseUrl.'administrationChartOfAccountsAccountView2.php?Year='.$row['Year'], NULL, $titleBarColorAdministrationChartOfAccounts);
}

echo $ui->getObjectAsJSONString();
?>