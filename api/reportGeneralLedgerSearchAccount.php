<?php
require('config.php');

if (!(checkUserAccessBoolean('ReportGeneralLedger')||checkUserAccessBoolean('GeneralLedgerJournalVoucherCreate'))){
	exit();
}

$input = json_decode(file_get_contents('php://input'), TRUE);

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT DISTINCT AccountNumber, Description FROM GeneralLedgerAccount WHERE (AccountNumber LIKE ? OR Description LIKE ?) ORDER BY AccountNumber ASC, Year DESC');
$stmt->execute(array('%'.$input['SearchQuery'].'%','%'.$input['SearchQuery'].'%'));
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>