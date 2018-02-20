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

checkUserAccess('AdministrationArticleDatabase');

$pdo = createPdo();

$results = dbPrepareExecute($pdo, 'SELECT Active, Number, Description, TaxGroup FROM Article WHERE Id=?', array($_GET['Id']));

$taxGroupDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerTaxGroupArticleOrAccount WHERE TaxGroup=?', array($results[0]['TaxGroup']));

$ui = new UserInterface();

$ui->setTitle('Edit');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationArticleDatabaseEditProcess.php');
$ui->setButtonLabel('Change');

$ui->addField('Description',NULL,'Description');
$valueVisibleData['Description'] = $results[0]['Description'];

$ui->addSearchSelection('TaxGroup','Tax group',$baseUrl.'administrationArticleDatabaseAddTaxGroupSearchSelection.php');

$valueVisibleData['TaxGroup'] = $results[0]['TaxGroup'];
$valueVisibleDataDescription['TaxGroup'] = $taxGroupDescription[0]['Description'];

$ui->addLabelHeader('Other');

$ui->addLabelTrueFalse('Active','Active');

if ($results[0]['Active'] == 1){
	$valueVisibleData['Active'] = True;
}else{
	$valueVisibleData['Active'] = False;
}

$valueHidden['Id'] = $_GET['Id'];

$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);
$ui->setHiddenData($valueHidden);

echo $ui->getObjectAsJSONString();
?>
