<?php
/*
 * Copyright 2012-2017 Marcus Hammar
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationVendorDatabase');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT Active, InternalName, BankAccount, Email, TaxGroup, BillFromAddressLine1, BillFromAddressLine2, BillFromAddressLine3, BillFromAddressLine4, BillFromAddressCity, BillFromAddressStateOrProvince, BillFromAddressZipOrPostalCode, BillFromAddressCountry FROM Vendor WHERE Id=?', array($_GET['Id']));

$taxGroupDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerTaxGroupCustomerOrVendor WHERE TaxGroup=?', array($results[0]['TaxGroup']));

$ui = new UserInterface();

$ui->setTitle('Edit');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationVendorDatabaseEditProcess.php');
$ui->setButtonLabel('Change');

$ui->addField('InternalName',NULL,'Internal name');
$valueVisibleData['InternalName'] = $results[0]['InternalName'];

$ui->addField('BankAccount',NULL,'Bank account');
$valueVisibleData['BankAccount'] = $results[0]['BankAccount'];

$ui->addField('Email',NULL,'E-mail address');
$valueVisibleData['Email'] = $results[0]['Email'];

$ui->addSearchSelection('TaxGroup','Tax group',$baseUrl.'administrationVendorDatabaseCreateTaxGroupSearchSelection.php');

$valueVisibleData['TaxGroup'] = $results[0]['TaxGroup'];
$valueVisibleDataDescription['TaxGroup'] = $taxGroupDescription[0]['Description'];

$ui->addLabelHeader('Bill from address');

$ui->addField('BillFromAddressLine1',NULL,'Line 1 (often customer name)');
$valueVisibleData['BillFromAddressLine1'] = $results[0]['BillFromAddressLine1'];

$ui->addField('BillFromAddressLine2',NULL,'Line 2');
$valueVisibleData['BillFromAddressLine2'] = $results[0]['BillFromAddressLine2'];

$ui->addField('BillFromAddressLine3',NULL,'Line 3');
$valueVisibleData['BillFromAddressLine3'] = $results[0]['BillFromAddressLine3'];

$ui->addField('BillFromAddressLine4',NULL,'Line 4');
$valueVisibleData['BillFromAddressLine4'] = $results[0]['BillFromAddressLine4'];

$ui->addField('BillFromAddressCity',NULL,'City');
$valueVisibleData['BillFromAddressCity'] = $results[0]['BillFromAddressCity'];

$ui->addField('BillFromAddressStateOrProvince',NULL,'State or province');
$valueVisibleData['BillFromAddressStateOrProvince'] = $results[0]['BillFromAddressStateOrProvince'];

$ui->addField('BillFromAddressZipOrPostalCode',NULL,'ZIP or postal code');
$valueVisibleData['BillFromAddressZipOrPostalCode'] = $results[0]['BillFromAddressZipOrPostalCode'];

$ui->addField('BillFromAddressCountry',NULL,'Country');
$valueVisibleData['BillFromAddressCountry'] = $results[0]['BillFromAddressCountry'];

$ui->addLabelHeader('');

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
