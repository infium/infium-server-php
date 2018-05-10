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

checkUserAccess('AdministrationUserDatabaseCreate');

$ui = new UserInterface();

$ui->setTitle('New user');
$ui->setWindow('Same');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationUserDatabaseCreateProcess.php');
$ui->setButtonLabel('Create');

$ui->addField('Name',NULL,'Name');
$ui->addField('Email',NULL,'E-mail');
$ui->addField('Username',NULL,'Username prefix (before @'.$_SERVER['HTTP_X_CLIENT_LOGIN_COMPANY'].')');
$ui->addField('Password',NULL,'Password');

echo $ui->getObjectAsJSONString();
?>
