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

checkUserAccess('ReportTax');

$ui = new UserInterface();

$ui->setTitle('Tax');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'reportTaxCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('BookingDate',NULL,'Booking date');

$ui->addLabelHeader('Tax period');

$ui->addField('DateFrom',NULL,'From');
$ui->addField('DateTo',NULL,'To');

$lastDayOfLastMonth = mktime(0,0,0, date('m', time()), 1, date('Y', time())) - (3600 * 24);

$valueVisibleData['BookingDate'] = date('Y-m-d', $lastDayOfLastMonth);
$valueVisibleData['DateFrom'] = date('Y-m', $lastDayOfLastMonth).'-01';
$valueVisibleData['DateTo'] = date('Y-m-d', $lastDayOfLastMonth);

$ui->setVisibleData($valueVisibleData);

echo $ui->getObjectAsJSONString();
?>
