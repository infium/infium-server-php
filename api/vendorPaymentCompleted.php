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

checkUserAccess();

$ui = new UserInterface();

$ui->setTitle('Payment completed');

if (checkUserAccessBoolean('VendorPaymentCompletedCreate')){
	$ui->addLabelValueLink('Create', NULL, 'GET', $baseUrl.'vendorPaymentCompletedCreateUI.php', NULL, $titleBarColorVendorPaymentCompleted, NULL, 'f067', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentCompletedView')){
	$ui->addLabelValueLink('View', NULL, 'GET', $baseUrl.'vendorPaymentCompletedView.php', NULL, $titleBarColorVendorPaymentCompleted, NULL, 'f06e', $titleBarColorVendorPaymentList);
}

if (checkUserAccessBoolean('VendorPaymentCompletedReverse')){
	$ui->addLabelValueLink('Reverse', NULL, 'GET', $baseUrl.'vendorPaymentCompletedReverseUI.php', NULL, $titleBarColorVendorPaymentCompleted, NULL, 'f0e2', $titleBarColorVendorPaymentList);
}

echo $ui->getObjectAsJSONString();
?>
