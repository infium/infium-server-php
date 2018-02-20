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

$ui->setTitle('Customer');

if (checkUserAccessBoolean('CustomerInvoiceCreate')||checkUserAccessBoolean('CustomerInvoiceEmail')||checkUserAccessBoolean('CustomerInvoiceReverse')||checkUserAccessBoolean('CustomerInvoiceView')){
	$ui->addLabelValueLink('Invoice', NULL, 'GET',$baseUrl.'customerInvoice.php', NULL, $titleBarColorCustomerInvoice, NULL, 'f0f6', $titleBarColorCustomerInvoice);
}

if (checkUserAccessBoolean('CustomerPaymentCreate')||checkUserAccessBoolean('CustomerPaymentReverse')||checkUserAccessBoolean('CustomerPaymentView')){
	$ui->addLabelValueLink('Payment', NULL, 'GET',$baseUrl.'customerPayment.php', NULL, $titleBarColorCustomerPayment, NULL, 'f153', $titleBarColorCustomerPayment);
}

echo $ui->getObjectAsJSONString();
?>
