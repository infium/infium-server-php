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

$ui->setTitle('Payment list');

if (checkUserAccessBoolean('VendorPaymentListCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'vendorPaymentListCreateUI.php', NULL, $titleBarColorVendorPaymentList, NULL, 'f067', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentListView')){
	$ui->addLabelValueLink('View', NULL, 'GET', $baseUrl.'vendorPaymentListView.php', NULL, $titleBarColorVendorPaymentList, NULL, 'f06e', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentListReverse')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET', $baseUrl.'vendorPaymentListReverseUI.php', NULL, $titleBarColorVendorPaymentList, NULL, 'f0e2', $titleBarColorVendorPaymentList);
}

echo $ui->getObjectAsJSONString();
?>
