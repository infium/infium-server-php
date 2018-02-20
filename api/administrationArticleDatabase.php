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

$ui->setTitle('Article');

$pdo = createPdo();

$resultsActive = dbPrepareExecute($pdo, "SELECT Id, Number, Description FROM Article WHERE Active=? ORDER BY Description ASC", array(True));

if (count($resultsActive) > 0){
    $ui->addLabelHeader('Active');
}

foreach ($resultsActive as $row){
    $ui->addLabelValueLink($row['Number'].' '.$row['Description'], NULL, 'GET', $baseUrl.'administrationArticleDatabaseEditUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationArticleDatabase);
}

$resultsInactive = dbPrepareExecute($pdo, "SELECT Id, Number, Description FROM Article WHERE Active=? ORDER BY Description ASC", array(False));

if (count($resultsInactive) > 0){
    $ui->addLabelHeader('Inactive');
}

foreach ($resultsInactive as $row){
    $ui->addLabelValueLink($row['Number'].' '.$row['Description'], NULL, 'GET', $baseUrl.'administrationArticleDatabaseEditUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationArticleDatabase);
}


if (checkUserAccessBoolean('AdministrationArticleDatabase')){
	$ui->addLabelValueLink('Create new...', NULL, 'GET', $baseUrl.'administrationArticleDatabaseAddUI.php', NULL, $titleBarColorAdministrationArticleDatabase);
}

echo $ui->getObjectAsJSONString();
?>
