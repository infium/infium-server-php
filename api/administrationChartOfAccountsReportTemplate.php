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
