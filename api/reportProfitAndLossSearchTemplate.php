<?php
require('config.php');

checkUserAccess('ReportProfitAndLoss');

$input = json_decode(file_get_contents('php://input'), TRUE);

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT DISTINCT Description FROM ReportTemplate WHERE Description LIKE ? AND Type=\'PL\' ORDER BY Description ASC', array('%'.$input['SearchQuery'].'%'));

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$newValue['Value'] = '';
$newValue['Description'] = 'None';

$dataToReturn['Data']['Result'][] = $newValue;

foreach ($results as $row){
	$newValue['Value'] = $row['Description'];
	$newValue['Description'] = $row['Description'];
	
	$dataToReturn['Data']['Result'][] = $newValue;	
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>