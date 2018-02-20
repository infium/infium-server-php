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

checkUserAccess('AdministrationChartOfAccountsBalanceCarryForward');

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$pdo = createPdo();

$years = dbPrepareExecute($pdo, 'SELECT Year FROM GeneralLedgerYear ORDER BY Year ASC', array());

for ($i = 0; $i < count($years) - 1; $i++) {
	$newValue['Value'] = $years[$i]['Year'].'-'.$years[$i+1]['Year'];
	$newValue['Description'] = 'Copy from '.$years[$i]['Year'].' to '.$years[$i+1]['Year'];
	$dataToReturn['Data']['Result'][] = $newValue;
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>
