<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationChartOfAccountsYearChange');

$ui = new UserInterface();

$pdo = createPdo();

$year = dbPrepareExecute($pdo, 'SELECT Id, Year, Status FROM GeneralLedgerYear WHERE Id=?', array($_GET['Id']));

$ui->setTitle($year[0]['Year']);
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationChartOfAccountsYearChangeProcess.php');
$ui->setButtonLabel('Change');

$ui->addSearchSelection('Status','Locking status',$baseUrl.'administrationChartOfAccountsYearChangeStatusSearchSelection.php');

$valueVisibleData['Status'] = $year[0]['Status'];
$valueVisibleDataDescription['Status'] = $year[0]['Status'];
$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

$valueHiddenData['Id'] = $year[0]['Id'];
$ui->setHiddenData($valueHiddenData);

echo $ui->getObjectAsJSONString();
?>