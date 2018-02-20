<?php
/*
 * Copyright 2012-2017 Infium AB
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
