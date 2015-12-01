<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationProperty');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT Id, Property, Value, ReadOnly FROM Property WHERE Id = ?', array($_GET['Id']));

$ui = new UserInterface();

$ui->setTitle($results[0]['Property']);

if ($results[0]['ReadOnly'] == False){
	$ui->setWindow('Same');
	$ui->setMethod('POST');
	$ui->setUrl($baseUrl.'administrationPropertyChangeProcess.php');
	$ui->setButtonLabel('Change');	
}

$ui->addField('Value',NULL,'Value');

$value['Value'] = $results[0]['Value'];
$ui->setVisibleData($value);

$hiddenData['Id'] = $_GET['Id'];
$ui->setHiddenData($hiddenData);

echo $ui->getObjectAsJSONString();
?>