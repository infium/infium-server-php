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

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Payment');

if (checkUserAccessBoolean('CustomerPaymentCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET',$baseUrl.'customerPaymentCreateUI.php', NULL, $titleBarColorCustomerPayment, NULL, 'f067', $titleBarColorCustomerPayment);
}

if (checkUserAccessBoolean('CustomerPaymentView')){
	$ui->addLabelValueLink('View', NULL, 'GET',$baseUrl.'customerPaymentView.php', NULL, $titleBarColorCustomerPayment, NULL, 'f06e', $titleBarColorCustomerPayment);
}

if (checkUserAccessBoolean('CustomerPaymentReverse')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET',$baseUrl.'customerPaymentReverseUI.php', NULL, $titleBarColorCustomerPayment, NULL, 'f0e2', $titleBarColorCustomerPayment);
}

echo $ui->getObjectAsJSONString();
?>