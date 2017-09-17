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

checkUserAccess('GeneralLedgerJournalVoucherCreate');

$ui = new UserInterface();

$ui->setTitle('Journal voucher');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'generalLedgerJournalVoucherCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('Date',NULL,'Booking date');
$ui->addField('Text',NULL,'Text');

$ui->addTable('Row');

$ui->addSearchSelection('Account','Account',$baseUrl.'generalLedgerJournalVoucherCreateUISearchAccount.php', 'Row');
$ui->addField('Debit','Row','Debit');
$ui->addField('Credit','Row','Credit');

$value['Date'] = date("Y-m-d");

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>