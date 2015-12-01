<?php
require('config.php');
require('classVendorPaymentCompleted.php');

checkUserAccess('VendorPaymentCompletedCreate');

try {
	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	validateDate($input['Date']);
	
	$vendorPaymentCompleted = new VendorPaymentCompleted();
	
	$vendorPaymentCompleted->setDate($input['Date']);
	
	$pdo = createPdo();
	
	$a = 0;
	foreach ($input as $field => $value){
		if (($field != 'Date')&&($value == True)){

			$stmt = $pdo->prepare('SELECT PreviousRowId, AmountRemaining FROM VendorPaymentListRow WHERE Id=?');
			$stmt->execute(array($field));
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						
			$vendorPaymentCompleted->addRow($field,$results[0]['AmountRemaining']);		
			
			$a++;
		}
	}
	
	if ($a == 0){
		throw new Exception('Document contains no rows.');
	}
	
	$pdo->exec('START TRANSACTION');
	$vendorPaymentCompleted->validateAndWriteToDatabase($pdo);
	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Reload';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Vendor payment completed #'.$vendorPaymentCompleted->getDocumentNumber();

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
	
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>