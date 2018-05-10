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

checkUserAccess('AdministrationChartOfAccountsReportTemplateChange');

try {
	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');

	$reportTemplateData = dbPrepareExecute($pdo, 'SELECT Year FROM ReportTemplate WHERE Id=?', array($_GET['Id']));

	$yearOpen = dbPrepareExecute($pdo, 'SELECT Status FROM GeneralLedgerYear WHERE Year=?', array($reportTemplateData[0]['Year']));

	if ($yearOpen[0]['Status'] != 'Open'){
		throw new Exception('The year '.$reportTemplateData[0]['Year'].' is currently not open.');
	}

	$existingSubLevels = dbPrepareExecute($pdo, 'SELECT COUNT(*) as ExistingSubLevels FROM ReportTemplateRow WHERE ParentId=? AND ParentSection=?', array($_GET['Id'], $_GET['ThisId']));

	if ($existingSubLevels[0]['ExistingSubLevels'] > 0){
		throw new Exception('You first need to delete sublevel sections and accounts. Delete was cancelled.');
	}

	dbPrepareExecute($pdo, 'DELETE FROM ReportTemplateRow WHERE Id=?', array($_GET['ThisId']));

	auditTrailLog($pdo, 'ReportTemplateRow', $_GET['ThisId'], 'DELETE');

	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'Reload';
	$response['Data'][2]['Action'] = 'MessageFlash';
	$response['Data'][2]['Message'] = 'The row was deleted';

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>
