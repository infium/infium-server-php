<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationArticleDatabase');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT Active, Number, Description, TaxGroup FROM Article WHERE Id=?', array($_GET['Id']));

$taxGroupDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerTaxGroupArticleOrAccount WHERE TaxGroup=?', array($results[0]['TaxGroup']));

$ui = new UserInterface();

$ui->setTitle('Edit');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationArticleDatabaseEditProcess.php');
$ui->setButtonLabel('Change');

$ui->addField('Description',NULL,'Description');
$valueVisibleData['Description'] = $results[0]['Description'];

$ui->addSearchSelection('TaxGroup','Tax group',$baseUrl.'administrationArticleDatabaseAddTaxGroupSearchSelection.php');

$valueVisibleData['TaxGroup'] = $results[0]['TaxGroup'];
$valueVisibleDataDescription['TaxGroup'] = $taxGroupDescription[0]['Description'];

$ui->addLabelHeader('Other');

$ui->addLabelTrueFalse('Active','Active');

if ($results[0]['Active'] == 1){
	$valueVisibleData['Active'] = True;	
}else{
	$valueVisibleData['Active'] = False;
}

$valueHidden['Id'] = $_GET['Id'];

$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);
$ui->setHiddenData($valueHidden);

echo $ui->getObjectAsJSONString();
?>