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

$ui = new UserInterface();

$ui->setTitle('Add');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationArticleDatabaseAddProcess.php');
$ui->setButtonLabel('Add');

$ui->addField('Number',NULL,'Article number');
$ui->addField('Description',NULL,'Description');

$ui->addSearchSelection('TaxGroup','Tax group',$baseUrl.'administrationArticleDatabaseAddTaxGroupSearchSelection.php');

$valueVisibleData['TaxGroup'] = 'PRODUCT_25';
$valueVisibleDataDescription['TaxGroup'] = 'Produkt - 25% moms normalt';
$ui->setVisibleData($valueVisibleData);
$ui->setVisibleDataDescription($valueVisibleDataDescription);

echo $ui->getObjectAsJSONString();
?>