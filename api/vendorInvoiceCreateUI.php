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

checkUserAccess('VendorInvoiceCreate');

$ui = new UserInterface();

$ui->setTitle('Invoice');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'vendorInvoiceCreateUI2.php');
$ui->setButtonLabel('Next');
$ui->setTitleBarColorNewWindow($titleBarColorVendorInvoice);

$ui->addField('BookingDate',NULL,'Booking date');
$valueVisibleData['BookingDate'] = date('Y-m-d');
$ui->addField('InvoiceDate',NULL,'Invoice date');
$ui->addSearchSelection('Vendor','Vendor',$baseUrl.'vendorInvoiceCreateSearchVendor.php');

$ui->addField('VendorReference',NULL,'Vendor reference');
$ui->addTable('Row');
$ui->addSearchSelection('Account','Account',$baseUrl.'vendorInvoiceCreateSearchAccount.php', 'Row');
$ui->addSearchSelection('Tax','Tax',$baseUrl.'vendorInvoiceCreateSearchTax.php', 'Row');
$ui->addField('Amount','Row','Amount', 'Decimal');

$ui->setVisibleData($valueVisibleData);

echo $ui->getObjectAsJSONString();
?>