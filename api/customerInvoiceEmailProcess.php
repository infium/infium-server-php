<?php
require('config.php');
require('functionRenderCustomerInvoice.php');

checkUserAccess('CustomerInvoiceEmail');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];
	
	$pdo = createPdo();
	
	validateCustomerInvoiceDocumentNumber($pdo, $inputVisible['DocumentNumber']);
	
	$invoiceHTML = renderCustomerInvoice($inputVisible['DocumentNumber']);

	emailSend($emailFrom, $inputVisible['Email'], 'Invoice #'.$inputVisible['DocumentNumber'], 'This e-mail has been HTML formatted. Please use an e-mail client that supports HTML.', $invoiceHTML, 'Invoice_'.$inputVisible['DocumentNumber'].'.html');
	
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Invoice sent';
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>