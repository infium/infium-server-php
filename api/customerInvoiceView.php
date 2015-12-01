<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('CustomerInvoiceView');

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Number, AmountGross FROM CustomerInvoice ORDER BY Id DESC'); // We need to handle limits
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui = new UserInterface();

$ui->setTitle('View');

foreach ($results as $row){
	$amount = decimalFormat($row['AmountGross']);
	
	$ui->addLabelValueLink('Invoice #'.$row['Number'], $amount, 'GET',$baseUrl.'customerInvoiceViewDocument.php?Number='.$row['Number'], NULL, $titleBarColorCustomerInvoice);	
}

if (count($results) == 0){
	$ui->addLabelValueLink('No documents exist yet', NULL, NULL, NULL, NULL, NULL);	
}

echo $ui->getObjectAsJSONString();
?>