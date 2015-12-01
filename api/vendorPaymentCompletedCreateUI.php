<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('VendorPaymentCompletedCreate');

$pdo = createPdo();

$currencyResult = dbPrepareExecute($pdo, 'SELECT Value FROM Property WHERE Property = ?', array('Currency'));
$currency = $currencyResult[0]['Value'];

$ui = new UserInterface();

$ui->setTitle('Payment completed');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'vendorPaymentCompletedCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('Date',NULL,'Booking date');
$value['Date'] = date('Y-m-d');

$ui->addLabelHeader('Unpaid invoices');

$stmt = $pdo->prepare('SELECT Id, PreviousRowId, AmountRemaining FROM VendorPaymentListRow WHERE AmountRemaining != 0 ORDER BY DueDate ASC, Id ASC');
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($results as $row){

	$stmt2 = $pdo->prepare('SELECT Id, DueDate, InternalName, PaymentReference, VendorId, AmountGross FROM VendorInvoice WHERE Id=?');
	$stmt2->execute(array($row['PreviousRowId']));
	$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
	
	$ui->addLabelTrueFalse($row['Id'],"Vendor: ".$results2[0]['InternalName']."\nInvoice number: ".$results2[0]['PaymentReference']."\nAmount: ".decimalFormat($row['AmountRemaining']).' '.$currency."\nDueDate: ".$results2[0]['DueDate']);
	$value[$row['Id']] = False;
}

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>