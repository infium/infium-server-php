<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationVendorDatabase');

$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

$ui = new UserInterface();

$ui->setTitle('Results');

$pdo = createPdo();

if ($input['OnlyActive'] == True){
	$results = dbPrepareExecute($pdo, "SELECT Id, Number, InternalName FROM Vendor WHERE ((InternalName LIKE ?) AND Active=?) ORDER BY InternalName ASC", array('%'.$input['Query'].'%', True));
}else{
	$results = dbPrepareExecute($pdo, "SELECT Id, Number, InternalName FROM Vendor WHERE InternalName LIKE ? ORDER BY InternalName ASC", array('%'.$input['Query'].'%'));
}

foreach ($results as $row){
	$ui->addLabelValueLink($row['Number'].' '.$row['InternalName'], NULL, 'GET', $baseUrl.'administrationVendorDatabaseEditUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationVendorDatabase);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No vendors match the search');	
}

echo $ui->getObjectAsJSONString();
?>