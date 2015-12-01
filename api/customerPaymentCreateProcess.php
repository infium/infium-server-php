<?php
require('config.php');
require('classCustomerPayment.php');

checkUserAccess('CustomerPaymentCreate');

try {
	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();
	
	validateDate($input['Date']);
	
	$a = 0;
	foreach ($input['Row'] as $row){
		if (($row['PaymentReference'] != '')||($row['Amount'] != '')){
			if ($row['PaymentReference'] == ''){
				throw new Exception('A payment reference needs to be supplied.');
			}			
			validateNumber($row['Amount']);
			$a++;
		}
	}
	
	if ($a == 0){
		throw new Exception('Document contains no rows.');
	}

	$customerPayment = new CustomerPayment();

	$customerPayment->setDateCreatedOurSide($input['Date']);
	$customerPayment->setDateCreatedPartnerSide($input['Date']);
	$customerPayment->setAccount('1930');

	foreach ($input['Row'] as $customerPaymentRow){
		if ($customerPaymentRow['Amount'] != ''){
			$customerPayment->addRow($customerPaymentRow['Amount'],$customerPaymentRow['PaymentReference']);		
		}
	}
	
	$pdo->exec('START TRANSACTION');
	$customerPayment->validateAndWriteToDatabase($pdo);
	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Customer payment created #'.$customerPayment->getDocumentNumber();

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
	
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>