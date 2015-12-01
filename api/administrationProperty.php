<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationProperty');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT Id, Property FROM Property ORDER BY Property ASC', array());

$ui = new UserInterface();

$ui->setTitle('Properties');

foreach ($results as $row){
	$ui->addLabelValueLink($row['Property'], NULL, 'GET', $baseUrl.'administrationPropertyChangeUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationProperty);
}

echo $ui->getObjectAsJSONString();
?>