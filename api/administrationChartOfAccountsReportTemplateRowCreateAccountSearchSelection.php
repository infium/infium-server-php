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

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$pdo = createPdo();

$yearToUse = dbPrepareExecute($pdo, 'SELECT Year FROM ReportTemplate WHERE Id=?', array($_GET['Id']));

$years = dbPrepareExecute($pdo, 'SELECT AccountNumber, Description FROM GeneralLedgerAccount WHERE Year=? AND (Description LIKE ? OR AccountNumber LIKE ?) ORDER BY AccountNumber ASC', array($yearToUse[0]['Year'], '%'.$input['SearchQuery'].'%', '%'.$input['SearchQuery'].'%'));

$newValue['Value'] = '';
$newValue['Description'] = 'Using section instead';
$dataToReturn['Data']['Result'][] = $newValue;

foreach ($years as $row){
	$newValue['Value'] = $row['AccountNumber'];
	$newValue['Description'] = $row['AccountNumber'].' '.$row['Description'];
	$dataToReturn['Data']['Result'][] = $newValue;
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>
