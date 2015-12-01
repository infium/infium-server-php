<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('VendorPaymentListCreate');

$pdo = createPdo();

$currencyResult = dbPrepareExecute($pdo, 'SELECT Value FROM Property WHERE Property = ?', array('Currency'));
$currency = $currencyResult[0]['Value'];

$ui = new UserInterface();

$ui->setTitle('Payment list');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'vendorPaymentListCreateProcess.php');
$ui->setButtonLabel('Create');
$ui->setTitleBarColorNewWindow($titleBarColorVendorPaymentList);

$ui->addField('BookingDate',NULL,'Booking date');

$stmt = $pdo->prepare('SELECT Id, DueDate, InternalName, PaymentReference, VendorId, AmountGrossRemaining FROM VendorInvoice WHERE AmountGrossRemaining != 0 ORDER BY Number ASC');
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($results as $row){	
	$ui->addLabelTrueFalse('Row'.$row['Id'], "Vendor: ".$results[0]['InternalName']."\nInvoice number: ".$row['PaymentReference']."\nAmount: ".decimalFormat($row['AmountGrossRemaining']).' '.$currency."\nDueDate: ".$row['DueDate']);
	$value['Row'.$row['Id']] = False;
}

$value['BookingDate'] = date('Y-m-d');
$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>