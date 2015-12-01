<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportGeneralLedger');

try {
	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];


	validateDate($input['DateFrom']);
	validateDate($input['DateTo']);
	
	if ($input['DateTo'] < $input['DateFrom']){
		throw new Exception('The "From" date needs to be greater or equal to the "To" date.');
	}

	$AccountYear = substr($input['DateFrom'],0,4);

	$pdo = createPdo();
	
	validateAccountNumber($pdo, $AccountYear, $input['Account']);

	$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

	$ui = new UserInterface();

	$stmt = $pdo->prepare("SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?");
	$stmt->execute(array($AccountYear, $input['Account']));
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$ui->setTitle($input['Account'].' '.$results[0]['Description'].' '.$input['DateFrom'].' - '.$input['DateTo']);

	$SumResult = 0;
	
	$stmt2 = $pdo->prepare('SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND (BookingDate<? OR BookingDate IS NULL)');
	$stmt2->execute(array($AccountYear, $input['Account'], $input['DateFrom']));
	$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

	$SumResult = $SumResult + $results2[0]['Amount'];

	$ui->addLabelValueLink('Opening balance', decimalFormat($SumResult));

	$stmt3 = $pdo->prepare('SELECT Number, BookingDate, Text, Amount FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? AND BookingDate>=? AND BookingDate<=? ORDER BY BookingDate, Id');
	$stmt3->execute(array($input['Account'], $input['DateFrom'], $input['DateTo']));
	$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);	
	
	foreach ($results3 as $row){
		$SumResult += $row['Amount'];
		$Amount = number_format($row['Amount'], 2, '.', ',');
		$ui->addLabelValueLink($row['BookingDate']."\n".$row['Text'], $Amount, 'GET', $baseUrl.'reportGeneralLedgerRowProcess.php?Year='.$AccountYear.'&Number='.$row['Number'], NULL, $titleBarColorReportGeneralLedger);	
	}

	$ui->addLabelValueLink('Closing balance', decimalFormat($SumResult));

	$pdo->exec('ROLLBACK');

	echo $ui->getObjectAsJSONString();
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'The following error occurred: ' . $e->getMessage();
	
	header('Content-type: application/json');
	echo json_encode($response,JSON_PRETTY_PRINT);
}
?>