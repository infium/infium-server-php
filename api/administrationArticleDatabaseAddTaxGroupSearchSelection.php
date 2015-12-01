<?php
require('config.php');

checkUserAccess('AdministrationArticleDatabase');

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$pdo = createPdo();

$years = dbPrepareExecute($pdo, 'SELECT TaxGroup, Description FROM GeneralLedgerTaxGroupArticleOrAccount WHERE Description LIKE ? ORDER BY Id ASC', array('%'.$input['SearchQuery'].'%'));

foreach ($years as $row){
	$newValue['Value'] = $row['TaxGroup'];
	$newValue['Description'] = $row['Description'];
	$dataToReturn['Data']['Result'][] = $newValue;
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>