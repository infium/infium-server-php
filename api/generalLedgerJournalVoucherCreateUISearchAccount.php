<?php
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