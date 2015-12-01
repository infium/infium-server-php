<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('VendorInvoiceView');

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Number, AmountGross FROM VendorInvoice ORDER BY Id DESC'); // We need to handle limits
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui = new UserInterface();

$ui->setTitle('View');

foreach ($results as $row){
	$amount = decimalFormat($row['AmountGross']);
	
	$ui->addLabelValueLink('Invoice #'.$row['Number'], $amount, 'GET',$baseUrl.'vendorInvoiceViewDocument.php?Number='.$row['Number'], NULL, $titleBarColorVendorInvoice);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No documents exist yet');
}

echo $ui->getObjectAsJSONString();
?>