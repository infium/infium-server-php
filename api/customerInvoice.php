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

$ui->setTitle('Invoice');

if (checkUserAccessBoolean('CustomerInvoiceCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'customerInvoiceCreateUI.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f067', $titleBarColorCustomerInvoice);
}

if (checkUserAccessBoolean('CustomerInvoiceView')){
	$ui->addLabelValueLink('View', NULL, 'GET', $baseUrl.'customerInvoiceView.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f06e', $titleBarColorCustomerInvoice);
}

if (checkUserAccessBoolean('CustomerInvoiceEmail')){
	$ui->addLabelValueLink('E-mail', NULL, 'GET', $baseUrl.'customerInvoiceEmailUI.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f1d8', $titleBarColorCustomerInvoice);
}

if (checkUserAccessBoolean('CustomerInvoiceReverse')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET', $baseUrl.'customerInvoiceReverseUI.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f0e2', $titleBarColorCustomerInvoice);
}

echo $ui->getObjectAsJSONString();
?>
