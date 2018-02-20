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

checkUserAccess('GeneralLedgerJournalVoucherView');

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Number FROM GeneralLedgerAccountBooking ORDER BY Id DESC'); // We need to handle limits
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui = new UserInterface();

$ui->setTitle('View');

foreach ($results as $row){
	$ui->addLabelValueLink('Journal voucher #'.$row['Number'], NULL, 'GET',$baseUrl.'generalLedgerJournalVoucherViewDocument.php?Number='.$row['Number'], NULL, $titleBarColorGeneralLedgerJournalVoucher);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No documents exist yet');
}

echo $ui->getObjectAsJSONString();
?>
