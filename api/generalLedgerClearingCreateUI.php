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

checkUserAccess('GeneralLedgerClearingCreate');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT DISTINCT AccountNumber FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL ORDER BY AccountNumber ASC', array());

$ui = new UserInterface();

$ui->setTitle('Clearing');

foreach ($results as $row){
	$account = dbPrepareExecute($pdo, 'SELECT Type, Description FROM GeneralLedgerAccount WHERE AccountNumber=? ORDER BY Year DESC', array($row['AccountNumber']));
	
	if ($account[0]['Type'] == 'BS'){
		$results3 = dbPrepareExecute($pdo, 'SELECT COUNT(*) as OpenItemsCount FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL AND AccountNumber=?', array($row['AccountNumber']));
		
		$subAccounts = dbPrepareExecute($pdo, 'SELECT COUNT(*) as SubAccountNumberCount FROM GeneralLedgerAccountBookingRow WHERE ClearingDate IS NULL AND AccountNumber=? AND SubAccountNumber!=?', array($row['AccountNumber'], ''));
		
		if ($subAccounts[0]['SubAccountNumberCount'] == 0){
			$ui->addLabelValueLink($row['AccountNumber'].' '.$account[0]['Description'], $results3[0]['OpenItemsCount'].' items', 'GET',$baseUrl.'generalLedgerClearingCreateUI3.php?AccountNumber='.$row['AccountNumber'].'&SubAccountNumber='.'', NULL, $titleBarColorGeneralLedgerClearing);
		}else{
			$ui->addLabelValueLink($row['AccountNumber'].' '.$account[0]['Description'], $results3[0]['OpenItemsCount'].' items', 'GET',$baseUrl.'generalLedgerClearingCreateUI2.php?AccountNumber='.$row['AccountNumber'], NULL, $titleBarColorGeneralLedgerClearing);			
		}
	}
}

if (count($results) == 0){
	$ui->addLabelValueLink('No open items');
}

echo $ui->getObjectAsJSONString();
?>