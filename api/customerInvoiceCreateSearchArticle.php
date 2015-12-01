<?php
require('config.php');

checkUserAccess('CustomerInvoiceCreate');

$input = json_decode(file_get_contents('php://input'), TRUE);

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Number,Description FROM Article WHERE ((Description LIKE ? OR Number LIKE ?) AND Active=?) ORDER BY Description ASC');
$stmt->execute(array('%'.$input['SearchQuery'].'%','%'.$input['SearchQuery'].'%', True));
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

foreach ($results as $row){
	$newValue['Value'] = $row['Number'];
	$newValue['Description'] = $row['Number'] . ' ' . $row['Description'];

	$dataToReturn['Data']['Result'][] = $newValue;	
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>