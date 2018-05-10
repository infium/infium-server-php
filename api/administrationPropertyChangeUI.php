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
require('classUserInterface.php');

checkUserAccess('AdministrationProperty');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT Id, Property, Value, ReadOnly FROM Property WHERE Id = ?', array($_GET['Id']));

$ui = new UserInterface();

$ui->setTitle($results[0]['Property']);

if ($results[0]['ReadOnly'] == False){
	$ui->setWindow('Same');
	$ui->setMethod('POST');
	$ui->setUrl($baseUrl.'administrationPropertyChangeProcess.php');
	$ui->setButtonLabel('Change');
}

$ui->addField('Value',NULL,'Value');

$value['Value'] = $results[0]['Value'];
$ui->setVisibleData($value);

$hiddenData['Id'] = $_GET['Id'];
$ui->setHiddenData($hiddenData);

echo $ui->getObjectAsJSONString();
?>
