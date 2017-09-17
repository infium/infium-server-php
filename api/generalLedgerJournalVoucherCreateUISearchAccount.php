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

if (!checkUserAccessBoolean('GeneralLedgerJournalVoucherCreate')){
	exit();
}

$input = json_decode(file_get_contents('php://input'), TRUE);

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT DISTINCT AccountNumber, Description FROM GeneralLedgerAccount WHERE (AccountNumber LIKE ? OR Description LIKE ?) ORDER BY AccountNumber ASC, Year DESC');
$stmt->execute(array('%'.$input['SearchQuery'].'%', '%'.$input['SearchQuery'].'%'));
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$customers = dbPrepareExecute($pdo, 'SELECT Number, InternalName FROM Customer WHERE Active=? AND (Number LIKE ? OR InternalName LIKE ?) ORDER BY Number', array(True, '%'.$input['SearchQuery']. '%','%'.$input['SearchQuery'].'%'));

$vendors = dbPrepareExecute($pdo, 'SELECT Number, InternalName FROM Vendor WHERE Active=? AND (Number LIKE ? OR InternalName LIKE ?) ORDER BY Number', array(True, '%'.$input['SearchQuery']. '%','%'.$input['SearchQuery'].'%'));

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$lastAccount = '';

foreach ($results as $row){
	if ($row['AccountNumber'] != $lastAccount){
		$newValue['Value'] = $row['AccountNumber'];
		$newValue['Description'] = $row['AccountNumber'] . ' ' . $row['Description'];
		
		$dataToReturn['Data']['Result'][] = $newValue;
	}
	$lastAccount = $row['AccountNumber'];
}

foreach ($customers as $row){
	$newValue['Value'] = 'C-'.$row['Number'];
	$newValue['Description'] = 'C-' . $row['Number'] . ' ' . $row['InternalName'];
	
	$dataToReturn['Data']['Result'][] = $newValue;
}

foreach ($vendors as $row){
	$newValue['Value'] = 'V-'.$row['Number'];
	$newValue['Description'] = 'V-' . $row['Number'] . ' ' . $row['InternalName'];
	
	$dataToReturn['Data']['Result'][] = $newValue;
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>