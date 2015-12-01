<?php
require('config.php');
require('classCustomerPayment.php');

checkUserAccess('CustomerPaymentReverse');

try {

	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	
	$pdo = createPdo();
	
	validateDate($input['BookingDate']);
	validateCustomerPaymentDocumentNumber($pdo, $input['DocumentNumber']);
	
	$pdo->exec('START TRANSACTION');
	
	$previousDoc = dbPrepareExecute($pdo, 'SELECT Id, BookingDate, PartnerDate, AccountNumber, Amount FROM CustomerPayment WHERE Number=?', array($input['DocumentNumber']));
	
	$previousDocRows = dbPrepareExecute($pdo, 'SELECT PaymentReference, Amount FROM CustomerPaymentRow WHERE ParentId=?', array($previousDoc[0]['Id']));
		
	$customerPayment = new CustomerPayment();

	$customerPayment->setDateCreatedOurSide($input['BookingDate']);
	$customerPayment->setDateCreatedPartnerSide($input['BookingDate']);
	$customerPayment->setAccount($previousDoc[0]['AccountNumber']);

	foreach ($previousDocRows as $customerPaymentRow){
		$customerPayment->addRow($customerPaymentRow['Amount']*-1,$customerPaymentRow['PaymentReference']);		
	}
	
	$customerPayment->validateAndWriteToDatabase($pdo);
	$pdo->exec('COMMIT');
		
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Reload';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Customer payment created #'.$customerPayment->getDocumentNumber();

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>