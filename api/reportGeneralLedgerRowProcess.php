<?php
require('config.php');
require('classUserInterface.php');

if (!(checkUserAccessBoolean('ReportProfitAndLoss')||checkUserAccessBoolean('ReportBalanceSheet'))){
	sendMessageToClient('User has no access to this');
	exit();
}

$pdo = createPdo();

$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

$ui = new UserInterface();

$ui->setTitle('Journal voucher #' . $_GET['Number']);

$header = dbPrepareExecute($pdo, 'SELECT BookingDate, DocumentType, DocumentTypeNumber FROM GeneralLedgerAccountBooking WHERE Number=? AND Year=?', array($_GET['Number'], $_GET['Year']));

$ui->addLabelValueLink('Booking date', $header[0]['BookingDate']);

switch ($header[0]['DocumentType']) {
	case 'CustomerInvoice':
		$ui->addLabelValueLink('Origin', 'Customer invoice #'.$header[0]['DocumentTypeNumber'], 'GET', $baseUrl.'customerInvoiceViewDocument.php?Number='.$header[0]['DocumentTypeNumber'], NULL, $titleBarColorReportGeneralLedger);
		break;
	
	case 'CustomerPayment':
		$ui->addLabelValueLink('Origin', 'Customer payment #'.$header[0]['DocumentTypeNumber'], 'GET', $baseUrl.'customerPaymentViewDocument.php?Number='.$header[0]['DocumentTypeNumber'], NULL, $titleBarColorReportGeneralLedger);
		break;
	
	case 'VendorInvoice':
		$ui->addLabelValueLink('Origin', 'Vendor invoice #'.$header[0]['DocumentTypeNumber'], 'GET', $baseUrl.'vendorInvoiceViewDocument.php?Number='.$header[0]['DocumentTypeNumber'], NULL, $titleBarColorReportGeneralLedger);
		break;
	
	case 'VendorPaymentCompleted':
		$ui->addLabelValueLink('Origin', 'Vendor payment #'.$header[0]['DocumentTypeNumber'], 'GET', $baseUrl.'vendorPaymentCompletedViewDocument.php?Number='.$header[0]['DocumentTypeNumber'], NULL, $titleBarColorReportGeneralLedger);
		break;    
}

$rows = dbPrepareExecute($pdo, 'SELECT AccountNumber, Debit, Credit FROM GeneralLedgerAccountBookingRow WHERE Number=? AND Year=? ORDER BY Id', array($_GET['Number'], $_GET['Year']));

$i = 0;

foreach ($rows as $row){
	
	$accountDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerAccount WHERE AccountNumber=? AND Year=?', array($row['AccountNumber'], $_GET['Year']));
	$i++;
	$ui->addLabelHeader('Row '.$i);
	$ui->addLabelValueLink('Account', $row['AccountNumber'] . ' ' . $accountDescription[0]['Description']);
	$ui->addLabelValueLink('Debit', decimalFormat($row['Debit']));
	$ui->addLabelValueLink('Credit', decimalFormat($row['Credit']));
}

$pdo->exec('ROLLBACK');

echo $ui->getObjectAsJSONString();
?>