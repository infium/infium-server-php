<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationArticleDatabase');

$ui = new UserInterface();

$ui->setTitle('Add');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationArticleDatabaseAddProcess.php');
$ui->setButtonLabel('Add');

$ui->addField('Number',NULL,'Article number');
$ui->addField('Description',NULL,'Description');

$ui->addSearchSelection('TaxGroup','Tax group',$baseUrl.'administrationArticleDatabaseAddTaxGroupSearchSelection.php');

$valueVisibleData['TaxGroup'] = 'PRODUCT_25';
$valueVisibleDataDescription['TaxGroup'] = 'Produkt - 25% moms normalt';
$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

echo $ui->getObjectAsJSONString();
?>