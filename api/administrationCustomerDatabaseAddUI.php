<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationCustomerDatabase');

$ui = new UserInterface();

$ui->setTitle('Add');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationCustomerDatabaseAddProcess.php');
$ui->setButtonLabel('Add');

$ui->addField('InternalName',NULL,'Internal name');
$ui->addField('Email',NULL,'E-mail address');

$ui->addSearchSelection('TaxGroup','Tax group',$baseUrl.'administrationCustomerDatabaseAddTaxGroupSearchSelection.php');
$ui->addField('TaxNumber',NULL,'VAT number');

$ui->addLabelHeader('Automatic e-mail');
$ui->addLabelTrueFalse('EmailInvoice','Invoice');

$ui->addLabelHeader('Bill to address');
$ui->addField('BillToAddressLine1',NULL,'Line 1 (often customer name)');
$ui->addField('BillToAddressLine2',NULL,'Line 2');
$ui->addField('BillToAddressLine3',NULL,'Line 3');
$ui->addField('BillToAddressLine4',NULL,'Line 4');
$ui->addField('BillToAddressCity',NULL,'City');
$ui->addField('BillToAddressStateOrProvince',NULL,'State or province');
$ui->addField('BillToAddressZipOrPostalCode',NULL,'ZIP or postal code');
$ui->addField('BillToAddressCountry',NULL,'Country');

$ui->addLabelHeader('Ship to address');
$ui->addField('ShipToAddressLine1',NULL,'Line 1 (often customer name)');
$ui->addField('ShipToAddressLine2',NULL,'Line 2');
$ui->addField('ShipToAddressLine3',NULL,'Line 3');
$ui->addField('ShipToAddressLine4',NULL,'Line 4');
$ui->addField('ShipToAddressCity',NULL,'City');
$ui->addField('ShipToAddressStateOrProvince',NULL,'State or province');
$ui->addField('ShipToAddressZipOrPostalCode',NULL,'ZIP or postal code');
$ui->addField('ShipToAddressCountry',NULL,'Country');

$valueVisibleData['TaxGroup'] = 'SWEDEN';
$valueVisibleDataDescription['TaxGroup'] = 'Sweden';
$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

echo $ui->getObjectAsJSONString();
?>