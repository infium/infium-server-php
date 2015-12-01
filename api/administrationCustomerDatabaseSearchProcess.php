<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationCustomerDatabase');

$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

$ui = new UserInterface();

$ui->setTitle('Results');

$pdo = createPdo();

if ($input['OnlyActive'] == True){
	$results = dbPrepareExecute($pdo, "SELECT Id, Number, InternalName FROM Customer WHERE ((InternalName LIKE ?) AND Active=?) ORDER BY InternalName ASC", array('%'.$input['Query'].'%', True));
}else{
	$results = dbPrepareExecute($pdo, "SELECT Id, Number, InternalName FROM Customer WHERE InternalName LIKE ? ORDER BY InternalName ASC", array('%'.$input['Query'].'%'));
}

foreach ($results as $row){
	$ui->addLabelValueLink($row['Number'].' '.$row['InternalName'], NULL, 'GET', $baseUrl.'administrationCustomerDatabaseEditUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationCustomerDatabase);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No customers match the search');	
}

echo $ui->getObjectAsJSONString();
?>