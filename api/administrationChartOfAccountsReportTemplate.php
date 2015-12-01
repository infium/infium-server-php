<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Report templates');

if (checkUserAccessBoolean('AdministrationChartOfAccountsReportTemplateChange')){
	$pdo = createPdo();
	$reportTemplates = dbPrepareExecute($pdo, 'SELECT Id, Year, Type, Description FROM ReportTemplate ORDER BY Year ASC, Type ASC, Description ASC', array());

	foreach ($reportTemplates as $row){
		
		if ($row['Type'] == 'BS'){
			$reportType = 'Balance sheet';
		}

		if ($row['Type'] == 'PL'){
			$reportType = 'Profit and loss statement';
		}
		
		$ui->addLabelValueLink($row['Year'].' - '.$reportType.' - '.$row['Description'], NULL, 'GET', $baseUrl.'administrationChartOfAccountsReportTemplateRow.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationChartOfAccounts);
	}
}

if (checkUserAccessBoolean('AdministrationChartOfAccountsReportTemplateCreate')){
	$ui->addLabelValueLink('Create new...', NULL, 'GET', $baseUrl.'administrationChartOfAccountsReportTemplateCreateUI.php', NULL, $titleBarColorAdministrationChartOfAccounts);
}

echo $ui->getObjectAsJSONString();
?>