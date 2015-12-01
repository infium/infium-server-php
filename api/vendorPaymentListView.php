<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('VendorPaymentListView');

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Number, Amount FROM VendorPaymentList ORDER BY Id DESC'); // We need to handle limits
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui = new UserInterface();

$ui->setTitle('View');

foreach ($results as $row){
	$amount = decimalFormat($row['Amount']);
	
	$ui->addLabelValueLink('Payment list #'.$row['Number'], $amount, 'GET', $baseUrl.'vendorPaymentListViewDocument.php?Number='.$row['Number'], NULL, $titleBarColorVendorPaymentList);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No documents exist yet');	
}

echo $ui->getObjectAsJSONString();
?>