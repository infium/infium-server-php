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

checkUserAccess('GeneralLedgerClearingCreate');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT DISTINCT SubAccountNumber FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL AND AccountNumber=? ORDER BY AccountNumber ASC', array($_GET['AccountNumber']));

$ui = new UserInterface();

$ui->setTitle('Clearing');

foreach ($results as $row){
	$results3 = dbPrepareExecute($pdo, 'SELECT COUNT(*) as OpenItemsCount FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL AND AccountNumber=? AND SubAccountNumber=?', array($_GET['AccountNumber'], $row['SubAccountNumber']));

	if ($row['SubAccountNumber'] == ''){
		$subAccountText = 'Not assigned';
	}

	if ($_GET['AccountNumber'] == '1510' && $row['SubAccountNumber'] != ''){
		$customer = dbPrepareExecute($pdo, 'SELECT Number, InternalName FROM Customer WHERE Id=?', array($row['SubAccountNumber']));
		$subAccountText = 'C-' . $customer[0]['Number'] . ' ' . $customer[0]['InternalName'];
	}

	if ($_GET['AccountNumber'] == '2441' && $row['SubAccountNumber'] != ''){
		$vendor = dbPrepareExecute($pdo, 'SELECT Number, InternalName FROM Vendor WHERE Id=?', array($row['SubAccountNumber']));
		$subAccountText = 'V-' . $vendor[0]['Number'] . ' ' . $vendor[0]['InternalName'];
	}

	$ui->addLabelValueLink($subAccountText, $results3[0]['OpenItemsCount'].' items', 'GET',$baseUrl.'generalLedgerClearingCreateUI3.php?AccountNumber='.$_GET['AccountNumber'].'&SubAccountNumber='.$row['SubAccountNumber'], NULL, $titleBarColorGeneralLedgerClearing);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No open items');
}

echo $ui->getObjectAsJSONString();
?>
