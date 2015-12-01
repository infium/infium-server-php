<?php
require('config.php');

checkUserAccess('AdministrationChartOfAccountsYearChange');

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$newValueOpen['Value'] = 'Open';
$newValueOpen['Description'] = 'Open';

$newValueClosed['Value'] = 'Closed';
$newValueClosed['Description'] = 'Closed';

$dataToReturn['Data']['Result'][] = $newValueOpen;	
$dataToReturn['Data']['Result'][] = $newValueClosed;	

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>