<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationChartOfAccountsReportTemplateChange');

$pdo = createPdo();

$reportTemplate = dbPrepareExecute($pdo, 'SELECT Id, Year, Type, Description FROM ReportTemplate WHERE Id=?', array($_GET['Id']));

if ($reportTemplate[0]['Type'] == 'BS'){
	$reportType = 'Balance sheet';
}

if ($reportTemplate[0]['Type'] == 'PL'){
	$reportType = 'Profit and loss statement';
}

$ui = new UserInterface();

$ui->setTitle($reportTemplate[0]['Year'].' - '.$reportType.' - '.$reportTemplate[0]['Description']);

$level = 0;

function processSection($pdo, $ui, $parentId, $parentSection, $accountYear){
	global $level;
	global $baseUrl;
	global $titleBarColorAdministrationChartOfAccounts;

	$itemsInSection = dbPrepareExecute($pdo, 'SELECT Id, `Order`, SectionDescription, AccountNumber FROM ReportTemplateRow WHERE ParentId=? AND ParentSection=? ORDER BY \'Order\' ASC', array($parentId, $parentSection));
	foreach ($itemsInSection as $row){
		if ($row['SectionDescription'] != NULL){
			$level++;
						
			$ui->addLabelValueLink($row['SectionDescription'].' ('.$row['Order'].')', NULL, 'POST', $baseUrl.'administrationChartOfAccountsReportTemplateRowDeleteProcess.php?Id='.$_GET['Id'].'&ThisId='.$row['Id'], NULL, $titleBarColorAdministrationChartOfAccounts, $level - 1);
			
			processSection($pdo, $ui, $parentId, $row['Id'], $accountYear);
			
			$ui->addLabelValueLink('Create new row in "'.$row['SectionDescription'].'"...', NULL, 'GET', $baseUrl.'administrationChartOfAccountsReportTemplateRowCreateUI.php?Id='.$_GET['Id'].'&ParentSection='.$row['Id'], NULL, $titleBarColorAdministrationChartOfAccounts, $level);
			
			$level--;
		}
		
		if ($row['AccountNumber'] != NULL){
			$accountDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?', array($accountYear, $row['AccountNumber']));
			
			$ui->addLabelValueLink($row['AccountNumber'].' '.$accountDescription[0]['Description'].' ('.$row['Order'].')', NULL, 'POST', $baseUrl.'administrationChartOfAccountsReportTemplateRowDeleteProcess.php?Id='.$_GET['Id'].'&ThisId='.$row['Id'], NULL, $titleBarColorAdministrationChartOfAccounts, $level);
		}
	}
}

processSection($pdo, $ui, $_GET['Id'], 0, $reportTemplate[0]['Year']);


$ui->addLabelValueLink('Create new row...', NULL, 'GET', $baseUrl.'administrationChartOfAccountsReportTemplateRowCreateUI.php?Id='.$_GET['Id'].'&ParentSection=0', NULL, $titleBarColorAdministrationChartOfAccounts);


echo $ui->getObjectAsJSONString();
?>