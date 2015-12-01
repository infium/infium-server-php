<?php
require('config.php');

checkUserAccess('VendorInvoiceCreate');

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$pdo = createPdo();

$results2 = dbPrepareExecute($pdo, 'SELECT TaxGroup, Description FROM GeneralLedgerTaxGroupArticleOrAccount WHERE Description LIKE ? ORDER BY `Order` ASC', array('%'.$input['SearchQuery'].'%'));

foreach ($results2 as $row){
	if (($row['TaxGroup'] == 'PRODUCT_0')||($row['TaxGroup'] == 'SERVICE_0')){
		$newValue['Value'] = 'EXCLUDED_'.$row['TaxGroup'];
		$newValue['Description'] = $row['Description'];
		$dataToReturn['Data']['Result'][] = $newValue;		
	}else{
		$newValue['Value'] = 'INCLUDED_'.$row['TaxGroup'];
		$newValue['Description'] = $row['Description'].' - Included';
		$dataToReturn['Data']['Result'][] = $newValue;
		
		$newValue['Value'] = 'EXCLUDED_'.$row['TaxGroup'];
		$newValue['Description'] = $row['Description'].' - Excluded';
		$dataToReturn['Data']['Result'][] = $newValue;
	}
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>