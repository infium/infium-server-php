<?php
require('config.php');

checkUserAccess('AdministrationChartOfAccountsBalanceCarryForward');

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$pdo = createPdo();

$years = dbPrepareExecute($pdo, 'SELECT Year FROM GeneralLedgerYear ORDER BY Year ASC', array());

for ($i = 0; $i < count($years) - 1; $i++) {
	$newValue['Value'] = $years[$i]['Year'].'-'.$years[$i+1]['Year'];
	$newValue['Description'] = 'Copy from '.$years[$i]['Year'].' to '.$years[$i+1]['Year'];
	$dataToReturn['Data']['Result'][] = $newValue;
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>