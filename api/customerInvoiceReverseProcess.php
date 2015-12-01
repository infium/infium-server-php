<?php
require('config.php');
require('classCustomerInvoice.php');

checkUserAccess('CustomerInvoiceReverse');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];	

	$pdo = createPdo();

	validateDate($inputVisible['BookingDate']);
	validateCustomerInvoiceDocumentNumber($pdo, $inputVisible['DocumentNumber']);
	
	$pdo->exec("START TRANSACTION;");
	
	$header = dbPrepareExecute($pdo, 'SELECT Id, CustomerId, CustomerReference, TaxNumber FROM CustomerInvoice WHERE Number=?', array($inputVisible['DocumentNumber']));
	$rows = dbPrepareExecute($pdo, 'SELECT ArticleId, Quantity, Price FROM CustomerInvoiceRow WHERE ParentId=?', array($header[0]['Id']));
	$customerData = dbPrepareExecute($pdo, 'SELECT Number FROM Customer WHERE Id=?', array($header[0]['CustomerId']));
	
	$customerInvoice = new CustomerInvoice();
	
	$customerInvoice->setBookingDate($inputVisible['BookingDate']);
	$customerInvoice->setCustomerNumber($customerData[0]['Number']);
	$customerInvoice->setCustomerReference($header[0]['CustomerReference']);
	$customerInvoice->setTaxNumber($header[0]['TaxNumber']);
	
	foreach ($rows as $row){
		$articleData = dbPrepareExecute($pdo, 'SELECT Number FROM Article WHERE Id=?', array($row['ArticleId']));
		$customerInvoice->addRow($pdo, $articleData[0]['Number'], $row['Quantity']*-1, $row['Price']);
	}
	
	$customerInvoice->validateAndWriteToDatabase($pdo);
	
	$pdo->exec("COMMIT;");
	
	$customerInvoice->sendMail($pdo);
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Invoice created #'.$customerInvoice->getDocumentNumber();
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>