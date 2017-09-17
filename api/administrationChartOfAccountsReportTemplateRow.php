<?php
/*
 * Copyright 2012-2017 Infium AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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