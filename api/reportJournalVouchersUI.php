<?php
/*
 * Copyright 2012-2018 Infium AB
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

checkUserAccess('GeneralLedgerJournalVoucherView');

$ui = new UserInterface();

$ui->setTitle('Journal vouchers');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'reportJournalVouchersProcess.php');
$ui->setButtonLabel('Run');
$ui->setTitleBarColorNewWindow($titleBarColorReportGeneralLedger);

$ui->addField('JournalVoucherFrom',NULL,'From');
$ui->addField('JournalVoucherTo',NULL,'To');

$pdo = createPdo();

$firstNumberResult = dbPrepareExecute($pdo, 'SELECT Number FROM GeneralLedgerAccountBooking ORDER BY Number ASC LIMIT 0, 1', array());
$lastNumberResult = dbPrepareExecute($pdo, 'SELECT Number FROM GeneralLedgerAccountBooking ORDER BY Number DESC LIMIT 0, 1', array());

$value['JournalVoucherFrom'] = $firstNumberResult[0]['Number'];
$value['JournalVoucherTo'] = $lastNumberResult[0]['Number'];

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>
