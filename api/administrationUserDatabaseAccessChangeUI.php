<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationUserDatabaseAccessChange');

$pdo = createPdo();

$ui = new UserInterface();

$ui->setTitle('Access');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationUserDatabaseAccessChangeProcess.php');
$ui->setButtonLabel('Change');

$results = dbPrepareExecute($pdo, 'SELECT ResourceName FROM UserAccessAvailible WHERE Active=? ORDER BY ResourceName', array(True));

$results2 = dbPrepareExecute($pdo, 'SELECT Access FROM User WHERE Id=?', array($_GET['Id']));

$accessArray = json_decode($results2[0]['Access'], TRUE);

foreach($results as $row){
	$ui->addLabelTrueFalse($row['ResourceName'],$row['ResourceName']);
	$value[$row['ResourceName']] = False;
	foreach($accessArray as $access){
		if ($row['ResourceName'] == $access){
			$value[$row['ResourceName']] = True;
		}
	}
}

$ui->setVisibleData($value);

$hiddenData['Id'] = $_GET['Id'];
$ui->setHiddenData($hiddenData);

echo $ui->getObjectAsJSONString();
?>