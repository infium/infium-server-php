<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('CustomerPaymentView');

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Number, Amount FROM CustomerPayment ORDER BY Id DESC'); // We need to handle limits
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui = new UserInterface();

$ui->setTitle('View');

foreach ($results as $row){
	$amount = decimalFormat($row['Amount']);
	
	$ui->addLabelValueLink('Payment #'.$row['Number'], $amount, 'GET',$baseUrl.'customerPaymentViewDocument.php?Number='.$row['Number'], NULL, $titleBarColorCustomerPayment);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No documents exist yet');	
}

echo $ui->getObjectAsJSONString();
?>