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

checkUserAccess('AdministrationVendorDatabase');

$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

$ui = new UserInterface();

$ui->setTitle('Results');

$pdo = createPdo();

if ($input['OnlyActive'] == True){
	$results = dbPrepareExecute($pdo, "SELECT Id, Number, InternalName FROM Vendor WHERE ((InternalName LIKE ?) AND Active=?) ORDER BY InternalName ASC", array('%'.$input['Query'].'%', True));
}else{
	$results = dbPrepareExecute($pdo, "SELECT Id, Number, InternalName FROM Vendor WHERE InternalName LIKE ? ORDER BY InternalName ASC", array('%'.$input['Query'].'%'));
}

foreach ($results as $row){
	$ui->addLabelValueLink($row['Number'].' '.$row['InternalName'], NULL, 'GET', $baseUrl.'administrationVendorDatabaseEditUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationVendorDatabase);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No vendors match the search');	
}

echo $ui->getObjectAsJSONString();
?>