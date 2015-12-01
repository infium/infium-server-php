<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationCustomerDatabase');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT Active, InternalName, Email, EmailInvoice, TaxGroup, TaxNumber, BillToAddressLine1, BillToAddressLine2, BillToAddressLine3, BillToAddressLine4, BillToAddressCity, BillToAddressStateOrProvince, BillToAddressZipOrPostalCode, BillToAddressCountry, ShipToAddressLine1, ShipToAddressLine2, ShipToAddressLine3, ShipToAddressLine4, ShipToAddressCity, ShipToAddressStateOrProvince, ShipToAddressZipOrPostalCode, ShipToAddressCountry FROM Customer WHERE Id=?', array($_GET['Id']));

$taxGroupDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerTaxGroupCustomerOrVendor WHERE TaxGroup=?', array($results[0]['TaxGroup']));

$ui = new UserInterface();

$ui->setTitle('Edit');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationCustomerDatabaseEditProcess.php');
$ui->setButtonLabel('Change');

$ui->addField('InternalName',NULL,'Internal name');
$valueVisibleData['InternalName'] = $results[0]['InternalName'];

$ui->addField('Email',NULL,'E-mail address');
$valueVisibleData['Email'] = $results[0]['Email'];

$ui->addSearchSelection('TaxGroup','Tax group',$baseUrl.'administrationCustomerDatabaseAddTaxGroupSearchSelection.php');

$valueVisibleData['TaxGroup'] = $results[0]['TaxGroup'];
$valueVisibleDataDescription['TaxGroup'] = $taxGroupDescription[0]['Description'];

$ui->addField('TaxNumber',NULL,'VAT number');
$valueVisibleData['TaxNumber'] = $results[0]['TaxNumber'];

$ui->addLabelHeader('Automatic e-mail');

$ui->addLabelTrueFalse('EmailInvoice','Invoice');
if ($results[0]['EmailInvoice'] == 1){
	$valueVisibleData['EmailInvoice'] = True;	
}else{
	$valueVisibleData['EmailInvoice'] = False;
}

$ui->addLabelHeader('Bill to address');

$ui->addField('BillToAddressLine1',NULL,'Line 1 (often customer name)');
$valueVisibleData['BillToAddressLine1'] = $results[0]['BillToAddressLine1'];

$ui->addField('BillToAddressLine2',NULL,'Line 2');
$valueVisibleData['BillToAddressLine2'] = $results[0]['BillToAddressLine2'];

$ui->addField('BillToAddressLine3',NULL,'Line 3');
$valueVisibleData['BillToAddressLine3'] = $results[0]['BillToAddressLine3'];

$ui->addField('BillToAddressLine4',NULL,'Line 4');
$valueVisibleData['BillToAddressLine4'] = $results[0]['BillToAddressLine4'];

$ui->addField('BillToAddressCity',NULL,'City');
$valueVisibleData['BillToAddressCity'] = $results[0]['BillToAddressCity'];

$ui->addField('BillToAddressStateOrProvince',NULL,'State or province');
$valueVisibleData['BillToAddressStateOrProvince'] = $results[0]['BillToAddressStateOrProvince'];

$ui->addField('BillToAddressZipOrPostalCode',NULL,'ZIP or postal code');
$valueVisibleData['BillToAddressZipOrPostalCode'] = $results[0]['BillToAddressZipOrPostalCode'];

$ui->addField('BillToAddressCountry',NULL,'Country');
$valueVisibleData['BillToAddressCountry'] = $results[0]['BillToAddressCountry'];

$ui->addLabelHeader('Ship to address');

$ui->addField('ShipToAddressLine1',NULL,'Line 1 (often customer name)');
$valueVisibleData['ShipToAddressLine1'] = $results[0]['ShipToAddressLine1'];

$ui->addField('ShipToAddressLine2',NULL,'Line 2');
$valueVisibleData['ShipToAddressLine2'] = $results[0]['ShipToAddressLine2'];

$ui->addField('ShipToAddressLine3',NULL,'Line 3');
$valueVisibleData['ShipToAddressLine3'] = $results[0]['ShipToAddressLine3'];

$ui->addField('ShipToAddressLine4',NULL,'Line 4');
$valueVisibleData['ShipToAddressLine4'] = $results[0]['ShipToAddressLine4'];

$ui->addField('ShipToAddressCity',NULL,'City');
$valueVisibleData['ShipToAddressCity'] = $results[0]['ShipToAddressCity'];

$ui->addField('ShipToAddressStateOrProvince',NULL,'State or province');
$valueVisibleData['ShipToAddressStateOrProvince'] = $results[0]['ShipToAddressStateOrProvince'];

$ui->addField('ShipToAddressZipOrPostalCode',NULL,'ZIP or postal code');
$valueVisibleData['ShipToAddressZipOrPostalCode'] = $results[0]['ShipToAddressZipOrPostalCode'];

$ui->addField('ShipToAddressCountry',NULL,'Country');
$valueVisibleData['ShipToAddressCountry'] = $results[0]['ShipToAddressCountry'];

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