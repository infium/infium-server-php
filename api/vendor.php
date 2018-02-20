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

$ui->setTitle('Vendor');

if (checkUserAccessBoolean('VendorInvoiceCreate')||checkUserAccessBoolean('VendorInvoiceReverse')||checkUserAccessBoolean('VendorInvoiceView')){
	$ui->addLabelValueLink('Invoice', NULL, 'GET', $baseUrl.'vendorInvoice.php', NULL, $titleBarColorVendorInvoice, NULL, 'f0f6', $titleBarColorVendorInvoice);
}

if (checkUserAccessBoolean('VendorPaymentListCreate')||checkUserAccessBoolean('VendorPaymentListReverse')||checkUserAccessBoolean('VendorPaymentListView')){
	$ui->addLabelValueLink('Payment list', NULL, 'GET', $baseUrl.'vendorPaymentList.php', NULL, $titleBarColorVendorPaymentList, NULL, 'f0cb', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentCompletedCreate')||checkUserAccessBoolean('VendorPaymentCompletedReverse')||checkUserAccessBoolean('VendorPaymentCompletedView')){
	$ui->addLabelValueLink('Payment completed', NULL, 'GET', $baseUrl.'vendorPaymentCompleted.php', NULL, $titleBarColorVendorPaymentCompleted, NULL, 'f153', $titleBarColorVendorPaymentCompleted);
}

echo $ui->getObjectAsJSONString();
?>
