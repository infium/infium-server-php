<?php
require('config.php');

checkUserAccess('VendorInvoiceCreate');

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$pdo = createPdo();

$results2 = dbPrepareExecute($pdo, 'SELECT AccountNumber, Description FROM GeneralLedgerAccount WHERE ((Description LIKE ? OR AccountNumber LIKE ?) AND Year=? AND ShowInVendorInvoice=?) ORDER BY AccountNumber ASC', array('%'.$input['SearchQuery'].'%','%'.$input['SearchQuery'].'%', date('Y'), True));

foreach ($results2 as $row){
	$newValue['Value'] = $row['AccountNumber'];
	$newValue['Description'] = $row['AccountNumber'] . ' ' . $row['Description'];

	$dataToReturn['Data']['Result'][] = $newValue;
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>