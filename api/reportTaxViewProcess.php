<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('ReportTax');

$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

$pdo = createPdo();

if ($input['Active'] == True){
	$active = True;
}else{
	$active = -1;	
}

if ($input['Reversal'] == True){
	$reversal = True;
}else{
	$reversal = -1;
}

if ($input['Reversed'] == True){
	$reversed = True;
}else{
	$reversed = -1;
}


$results = dbPrepareExecute($pdo, 'SELECT Number, FromDate, ToDate FROM TaxReport WHERE Active=? OR Reversal=? OR Reversed=? ORDER BY Id DESC', array($active, $reversal, $reversed));

$ui = new UserInterface();

$ui->setTitle('View');

foreach ($results as $row){
	$ui->addLabelValueLink('Tax report #'.$row['Number'].' ('.$row['FromDate'].' - '.$row['ToDate'].')', NULL, 'GET', $baseUrl.'reportTaxViewDocument.php?Number='.$row['Number'], NULL, $titleBarColorReportTax);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No documents match the search');	
}

echo $ui->getObjectAsJSONString();
?>