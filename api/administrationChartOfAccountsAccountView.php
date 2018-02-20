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

checkUserAccess('AdministrationChartOfAccountsAccountChange');

$ui = new UserInterface();

$ui->setTitle('Select year');

$pdo = createPdo();

$years = dbPrepareExecute($pdo, 'SELECT Year FROM GeneralLedgerYear ORDER BY Year DESC', array());

foreach ($years as $row){
	$ui->addLabelValueLink($row['Year'], NULL, 'GET', $baseUrl.'administrationChartOfAccountsAccountView2.php?Year='.$row['Year'], NULL, $titleBarColorAdministrationChartOfAccounts);
}

echo $ui->getObjectAsJSONString();
?>
