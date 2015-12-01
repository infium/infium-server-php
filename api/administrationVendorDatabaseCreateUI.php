<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationVendorDatabase');

$ui = new UserInterface();

$ui->setTitle('Add');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationVendorDatabaseCreateProcess.php');
$ui->setButtonLabel('Add');

$ui->addField('InternalName',NULL,'Internal name');
$ui->addField('BankAccount',NULL,'Bank account');
$ui->addField('Email',NULL,'E-mail address');

$ui->addSearchSelection('TaxGroup','Tax group',$baseUrl.'administrationVendorDatabaseCreateTaxGroupSearchSelection.php');

$ui->addLabelHeader('Bill from address');
$ui->addField('BillFromAddressLine1',NULL,'Line 1 (often customer name)');
$ui->addField('BillFromAddressLine2',NULL,'Line 2');
$ui->addField('BillFromAddressLine3',NULL,'Line 3');
$ui->addField('BillFromAddressLine4',NULL,'Line 4');
$ui->addField('BillFromAddressCity',NULL,'City');
$ui->addField('BillFromAddressStateOrProvince',NULL,'State or province');
$ui->addField('BillFromAddressZipOrPostalCode',NULL,'ZIP or postal code');
$ui->addField('BillFromAddressCountry',NULL,'Country');

$valueVisibleData['TaxGroup'] = 'SWEDEN';
$valueVisibleDataDescription['TaxGroup'] = 'Sverige';
$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

echo $ui->getObjectAsJSONString();
?>