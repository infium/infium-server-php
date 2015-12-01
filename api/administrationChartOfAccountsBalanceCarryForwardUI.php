<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationChartOfAccountsBalanceCarryForward');

$ui = new UserInterface();

$pdo = createPdo();

$ui->setTitle('Balance carry forward');

$years = dbPrepareExecute($pdo, 'SELECT Year FROM GeneralLedgerYear ORDER BY Year ASC', array());

$numYears = count($years);

if ($numYears > 1){
	$ui->setWindow('Same');
	$ui->setMethod('POST');
	$ui->setUrl($baseUrl.'administrationChartOfAccountsBalanceCarryForwardProcess.php');
	$ui->setButtonLabel('Create');
	
	$ui->addSearchSelection('Period','Period',$baseUrl.'administrationChartOfAccountsBalanceCarryForwardPeriodSearchSelection.php');
	
	$valueVisibleData['Period'] = $years[$numYears-2]['Year'].'-'.$years[$numYears-1]['Year'];
	$valueVisibleDataDescription['Period'] = 'Copy from '.$years[$numYears-2]['Year'].' to '.$years[$numYears-1]['Year'];
	
	$ui->setVisibleData($valueVisibleData);
	$ui->setVisibleDataDescription($valueVisibleDataDescription);
}else{	
	$ui->addLabelValueLink('Only one year exists');	
}

echo $ui->getObjectAsJSONString();
?>