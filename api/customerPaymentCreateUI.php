<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('CustomerPaymentCreate');

$ui = new UserInterface();

$ui->setTitle('Payment');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'customerPaymentCreateUI2.php');
$ui->setButtonLabel('Next');
$ui->setTitleBarColorNewWindow($titleBarColorCustomerPayment);

$pdo = createPdo();

$currencyResult = dbPrepareExecute($pdo, 'SELECT Value FROM Property WHERE Property = ?', array('Currency'));
$currency = $currencyResult[0]['Value'];

$results = dbPrepareExecute($pdo, 'SELECT DISTINCT SubAccountNumber, ClearingReference FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? ORDER BY ClearingReference ASC', array('1510'));

$i = 0;
foreach ($results as $row){
	
	$results2 = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as RemainingAmount FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? AND SubAccountNumber=? AND ClearingReference=?', array('1510', $row['SubAccountNumber'], $row['ClearingReference']));
	
	$results3 = dbPrepareExecute($pdo, 'SELECT Number, InternalName FROM Customer WHERE Id=?', array($row['SubAccountNumber']));
	
	if ($results2[0]['RemainingAmount'] != 0){
		$ui->addLabelTrueFalse('Index'.$i, "Customer: ".$results3[0]['Number'].' '.$results3[0]['InternalName']."\nReference: ".$row['ClearingReference']."\nAmount: ".decimalFormat($results2[0]['RemainingAmount']).' '.$currency);
		$hiddenData['Payment'][$i]['SubAccountNumber'] = $row['SubAccountNumber'];
		$hiddenData['Payment'][$i]['ClearingReference'] = $row['ClearingReference'];
		$hiddenData['Payment'][$i]['Amount'] = $results2[0]['RemainingAmount'];
		$i++;
	}
}

if ($i == 0){
	$ui->addLabelValueLink('No unpaid invoices to select');	
}

if (isset($hiddenData)){
	$ui->setHiddenData($hiddenData);	
}

echo $ui->getObjectAsJSONString();
?>