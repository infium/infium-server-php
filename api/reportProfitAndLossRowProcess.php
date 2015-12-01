<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportProfitAndLoss');

$pdo = createPdo();

$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

$ui = new UserInterface();

$AccountYear = substr($_GET['DateFrom'],0,4);

$stmt = $pdo->prepare("SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?");
$stmt->execute(array($AccountYear, $_GET['AccountNumber']));
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui->setTitle($_GET['AccountNumber']. ' '.$results[0]['Description'].' '.$_GET['DateFrom'].' - '.$_GET['DateTo']);

$SumResult = 0;

$stmt2 = $pdo->prepare('SELECT Number, BookingDate, Text, Amount, DocumentType, DocumentTypeNumber FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? AND BookingDate>=? AND BookingDate<=? ORDER BY BookingDate, Id');
$stmt2->execute(array($_GET['AccountNumber'], $_GET['DateFrom'], $_GET['DateTo']));
$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);	

foreach ($results2 as $row){
	
	switch ($row['DocumentType']) {
		case 'CustomerInvoice':
			$url = $baseUrl.'customerInvoiceViewDocument.php?Number='.$row['DocumentTypeNumber'];
			break;
	
		case 'CustomerPayment':
			$url = $baseUrl.'customerPaymentViewDocument.php?Number='.$row['DocumentTypeNumber'];
			break;
	
		case 'VendorInvoice':
			$url = $baseUrl.'vendorInvoiceViewDocument.php?Number='.$row['DocumentTypeNumber'];
			break;
	
		case 'VendorPaymentCompleted':
			$url = $baseUrl.'vendorPaymentCompletedViewDocument.php?Number='.$row['DocumentTypeNumber'];
			break;
		
		default:
			$url = $baseUrl.'reportGeneralLedgerRowProcess.php?Year='.$AccountYear.'&Number='.$row['Number'];
	}
	
	$SumResult += $row['Amount'];
	$Amount = number_format($row['Amount']*-1, 2, '.', ',');
	$ui->addLabelValueLink($row['BookingDate'].": ".$row['Text'], $Amount, 'GET', $url, NULL, $titleBarColorReportBalanceSheet);	
}

$SumAmount = number_format($SumResult*-1, 2, '.', ',');

$ui->addLabelValueLink('Sum', $SumAmount);

$pdo->exec('ROLLBACK');

echo $ui->getObjectAsJSONString();
?>