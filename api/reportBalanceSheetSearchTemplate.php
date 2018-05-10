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

checkUserAccess('ReportBalanceSheet');

$input = json_decode(file_get_contents('php://input'), TRUE);

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT DISTINCT Description FROM ReportTemplate WHERE Description LIKE ?  AND Type=\'BS\' ORDER BY Description ASC', array('%'.$input['SearchQuery'].'%'));

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = $input['SearchQuery'];
$dataToReturn['Data']['SearchSerialNumber'] = $input['SearchSerialNumber'];

$newValue['Value'] = '';
$newValue['Description'] = 'None';

$dataToReturn['Data']['Result'][] = $newValue;

foreach ($results as $row){
	$newValue['Value'] = $row['Description'];
	$newValue['Description'] = $row['Description'];

	$dataToReturn['Data']['Result'][] = $newValue;
}

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>
