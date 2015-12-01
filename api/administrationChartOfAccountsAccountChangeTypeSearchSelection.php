<?php
require('config.php');

checkUserAccess('AdministrationChartOfAccountsAccountChange');

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$newValue1['Value'] = 'PL';
$newValue1['Description'] = 'Profit and loss';

$newValue2['Value'] = 'BS';
$newValue2['Description'] = 'Balance sheet';

$dataToReturn['Data']['Result'][] = $newValue1;
$dataToReturn['Data']['Result'][] = $newValue2;

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>