<?php
require('config.php');

checkUserAccess('AdministrationChartOfAccountsReportTemplateCreate');

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$newValueBS['Value'] = 'BS';
$newValueBS['Description'] = 'Balance sheet';

$newValuePL['Value'] = 'PL';
$newValuePL['Description'] = 'Profit and loss statement';

$dataToReturn['Data']['Result'][] = $newValueBS;
$dataToReturn['Data']['Result'][] = $newValuePL;

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>