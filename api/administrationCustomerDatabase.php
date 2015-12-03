<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationCustomerDatabase');

$ui = new UserInterface();

$ui->setTitle('Customer');

$pdo = createPdo();

$resultsActive = dbPrepareExecute($pdo, "SELECT Id, Number, InternalName FROM Customer WHERE Active=? ORDER BY InternalName ASC", array(True));

if (count($resultsActive) > 0){
	$ui->addLabelHeader('Active');
}

foreach ($resultsActive as $row){
	$ui->addLabelValueLink($row['Number'].' '.$row['InternalName'], NULL, 'GET', $baseUrl.'administrationCustomerDatabaseEditUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationCustomerDatabase);
}

$resultsInactive = dbPrepareExecute($pdo, "SELECT Id, Number, InternalName FROM Customer WHERE Active=? ORDER BY InternalName ASC", array(False));

if (count($resultsInactive) > 0){
	$ui->addLabelHeader('Inactive');
}

foreach ($resultsInactive as $row){
	$ui->addLabelValueLink($row['Number'].' '.$row['InternalName'], NULL, 'GET', $baseUrl.'administrationCustomerDatabaseEditUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationCustomerDatabase);
}


$ui->addLabelValueLink('Create new...', NULL, 'GET', $baseUrl.'administrationCustomerDatabaseAddUI.php', NULL, $titleBarColorAdministrationCustomerDatabase);

echo $ui->getObjectAsJSONString();
?>