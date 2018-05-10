<?php
/*
 * Copyright 2012-2017 Marcus Hammar
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

checkUserAccess('AdministrationChartOfAccountsYearCreate');

try {
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');

	if (!preg_match('/^[0-9]{4}$/', $inputVisible['Year'])){
		throw new Exception('The year needs to be in the format NNNN.');
	}

	$yearPlusOne = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfYears FROM GeneralLedgerYear WHERE Year=?', array($inputVisible['Year']+1));
	$yearMinusOne = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfYears FROM GeneralLedgerYear WHERE Year=?', array($inputVisible['Year']-1));
	$currentYear = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfYears FROM GeneralLedgerYear WHERE Year=?', array($inputVisible['Year']));
	$upcomingOpenYears = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfYears FROM GeneralLedgerYear WHERE Status=\'Closed\' AND Year>?', array($inputVisible['Year']));

	if ($currentYear[0]['NumberOfYears'] == 1){
		throw new Exception('The year already exist.');
	}

	if (!(($yearPlusOne[0]['NumberOfYears'] == 1)||($yearMinusOne[0]['NumberOfYears'] == 1))){
		throw new Exception('The year needs to be an increment of one or decrement of one to a year that already exist in the system. If the latest year is '.date("Y").' you cannot create '.(date("Y")+2).' until '.(date("Y")+1).' has been created.');
	}

	if ($upcomingOpenYears[0]['NumberOfYears'] > 0){
		throw new Exception('If you add a year in the past, all subsequent years needs to be open.');
	}

	if ($yearMinusOne[0]['NumberOfYears'] == 1){
		$copyFromYear = $inputVisible['Year'] - 1;
	}else{
		$copyFromYear = $inputVisible['Year'] + 1;
	}

	dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerYear (Year, Status) VALUES (?, ?)', array($inputVisible['Year'], 'Open'));

	auditTrailLog($pdo, 'GeneralLedgerYear', $pdo->lastInsertId(), 'INSERT');

	$results = dbPrepareExecute($pdo, 'SELECT AccountNumber, Description, Type, SubAccountNumber, ShowInVendorInvoice FROM GeneralLedgerAccount WHERE Year=?', array($copyFromYear));
	foreach ($results as $row){
		dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccount (Year, AccountNumber, Description, Type, SubAccountNumber, ShowInVendorInvoice) VALUES (?, ?, ?, ?, ?, ?)', array($inputVisible['Year'], $row['AccountNumber'], $row['Description'], $row['Type'], $row['SubAccountNumber'], $row['ShowInVendorInvoice']));
		auditTrailLog($pdo, 'GeneralLedgerAccount', $pdo->lastInsertId(), 'INSERT');
	}

	function processSection($pdo, $parentIdOld, $parentIdNew, $parentSectionOld, $parentSectionNew){
		$itemsInSection = dbPrepareExecute($pdo, 'SELECT Id, `Order`, SectionDescription, AccountNumber FROM ReportTemplateRow WHERE ParentId=? AND ParentSection=? ORDER BY \'Order\' ASC', array($parentIdOld, $parentSectionOld));
		foreach ($itemsInSection as $row){

			dbPrepareExecute($pdo, 'INSERT INTO ReportTemplateRow (ParentId, ParentSection, `Order`, SectionDescription, AccountNumber) VALUES (?, ?, ?, ?, ?)', array($parentIdNew, $parentSectionNew, $row['Order'], $row['SectionDescription'], $row['AccountNumber']));

			$parentSectionNewId = $pdo->lastInsertId();

			auditTrailLog($pdo, 'ReportTemplateRow', $parentSectionNewId, 'INSERT');

			if ($row['SectionDescription'] != NULL){
				processSection($pdo, $parentIdOld, $parentIdNew, $row['Id'], $parentSectionNewId);
			}
		}
	}

	$results = dbPrepareExecute($pdo, 'SELECT Id, Type, Code, Description FROM ReportTemplate WHERE Year=?', array($copyFromYear));
	foreach ($results as $row){
		dbPrepareExecute($pdo, 'INSERT INTO ReportTemplate (Year, Type, Code, Description) VALUES (?, ?, ?, ?)', array($inputVisible['Year'], $row['Type'], $row['Code'], $row['Description']));

		$parentIdNew = $pdo->lastInsertId();
		auditTrailLog($pdo, 'ReportTemplate', $parentIdNew, 'INSERT');

		processSection($pdo, $row['Id'], $parentIdNew, 0, 0);
	}

    $results = dbPrepareExecute($pdo, 'SELECT TaxRuleSet, Type, TaxGroupCustomerOrVendor, TaxGroupArticleOrAccount, AccountArticle, AccountArticleTaxCode, TaxPercent, AccountTaxOutput, AccountTaxOutputTaxCode, AccountTaxInput, AccountTaxInputTaxCode FROM GeneralLedgerAccountDeterminationInvoiceRow WHERE FromDate=? AND ToDate=?', array($copyFromYear.'-01-01', $copyFromYear.'-12-31'));
    foreach ($results as $row){
        dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountDeterminationInvoiceRow (TaxRuleSet, FromDate, ToDate, Type, TaxGroupCustomerOrVendor, TaxGroupArticleOrAccount, AccountArticle, AccountArticleTaxCode, TaxPercent, AccountTaxOutput, AccountTaxOutputTaxCode, AccountTaxInput, AccountTaxInputTaxCode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array($row['TaxRuleSet'], $inputVisible['Year'].'-01-01', $inputVisible['Year'].'-12-31', $row['Type'], $row['TaxGroupCustomerOrVendor'], $row['TaxGroupArticleOrAccount'], $row['AccountArticle'], $row['AccountArticleTaxCode'], $row['TaxPercent'], $row['AccountTaxOutput'], $row['AccountTaxOutputTaxCode'], $row['AccountTaxInput'], $row['AccountTaxInputTaxCode']));
        auditTrailLog($pdo, 'GeneralLedgerAccountDeterminationInvoiceRow', $pdo->lastInsertId(), 'INSERT');
    }

    $results = dbPrepareExecute($pdo, 'SELECT TaxRuleSet, `Order`, Field, Description FROM TaxField WHERE FromDate=? AND ToDate=?', array($copyFromYear.'-01-01', $copyFromYear.'-12-31'));
    foreach ($results as $row){
        dbPrepareExecute($pdo, 'INSERT INTO TaxField (TaxRuleSet, FromDate, ToDate, `Order`, Field, Description) VALUES (?, ?, ?, ?, ?, ?)', array($row['TaxRuleSet'], $inputVisible['Year'].'-01-01', $inputVisible['Year'].'-12-31', $row['Order'], $row['Field'], $row['Description']));
        auditTrailLog($pdo, 'TaxField', $pdo->lastInsertId(), 'INSERT');
    }

    $results = dbPrepareExecute($pdo, 'SELECT TaxRuleSet, Field, TaxCode, MoveFromAccount, MoveFromAccountTaxCode, MoveToAccount, MoveToAccountTaxCode, ReversedSignInReport FROM TaxFieldCalculation WHERE FromDate=? AND ToDate=?', array($copyFromYear.'-01-01', $copyFromYear.'-12-31'));
    foreach ($results as $row){
        dbPrepareExecute($pdo, 'INSERT INTO TaxFieldCalculation (TaxRuleSet, FromDate, ToDate, Field, TaxCode, MoveFromAccount, MoveFromAccountTaxCode, MoveToAccount, MoveToAccountTaxCode, ReversedSignInReport) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array($row['TaxRuleSet'], $inputVisible['Year'].'-01-01', $inputVisible['Year'].'-12-31', $row['Field'], $row['TaxCode'], $row['MoveFromAccount'], $row['MoveFromAccountTaxCode'], $row['MoveToAccount'], $row['MoveToAccountTaxCode'], $row['ReversedSignInReport']));
        auditTrailLog($pdo, 'TaxFieldCalculation', $pdo->lastInsertId(), 'INSERT');
    }

    $pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'Year '.$inputVisible['Year'].' created.';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>
