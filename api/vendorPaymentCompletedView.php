<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('VendorPaymentCompletedView');

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Number, Amount FROM VendorPaymentCompleted ORDER BY Id DESC'); // We need to handle limits
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui = new UserInterface();

$ui->setTitle('View');

foreach ($results as $row){
	$amount = decimalFormat($row['Amount']);
	
	$ui->addLabelValueLink('Payment completed #'.$row['Number'], $amount, 'GET', $baseUrl.'vendorPaymentCompletedViewDocument.php?Number='.$row['Number'], NULL, $titleBarColorVendorPaymentCompleted);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No documents exist yet', NULL, NULL, NULL, NULL, NULL);	
}

echo $ui->getObjectAsJSONString();
?>