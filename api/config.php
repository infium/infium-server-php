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

require('functionValidations.php');
require('functionEmail.php');
require('functionDatabase.php');
require('functionExtendedLogging.php');
require('functionUserValidation.php');
require('functionMatchOpenItems.php');
require('functionAuditTrailLog.php');
require('functionNextDocumentNumber.php');
require('functionDecimalFormat.php');
require('functionSendMessageToClient.php');

$baseUrl = 'https://www.company.com/api/';
$emailFrom = 'noreply@company.com';

$databaseDSN = 'mysql:host=sql.company.com';
$databaseUsername = 'username';
$databasePasswd = 'password';

$extendedLogging = true;

$version = '1.2.0';

$titleBarColorCustomer = '#59B750';
$titleBarColorCustomerInvoice = '#59B750';
$titleBarColorCustomerPayment = '#59B750';

$titleBarColorVendor = '#4169B9';
$titleBarColorVendorInvoice = '#4169B9';
$titleBarColorVendorPaymentList = '#4169B9';
$titleBarColorVendorPaymentCompleted = '#4169B9';

$titleBarColorGeneralLedger = '#ED462F';
$titleBarColorGeneralLedgerJournalVoucher = '#ED462F';
$titleBarColorGeneralLedgerClearing = '#ED462F';

$titleBarColorReport = '#F5A031';
$titleBarColorReportProfitAndLoss = '#F5A031';
$titleBarColorReportBalanceSheet = '#F5A031';
$titleBarColorReportGeneralLedger = '#F5A031';
$titleBarColorReportTax = '#F5A031';
$titleBarColorReportAuditTrail = '#F5A031';

$titleBarColorAdministration = '#E54E9A';
$titleBarColorAdministrationCustomerDatabase = '#E54E9A';
$titleBarColorAdministrationVendorDatabase = '#E54E9A';
$titleBarColorAdministrationArticleDatabase = '#E54E9A';
$titleBarColorAdministrationUserDatabase = '#E54E9A';
$titleBarColorAdministrationChartOfAccounts = '#E54E9A';
$titleBarColorAdministrationProperty = '#E54E9A';

if ($extendedLogging){
    createExtendedLog();
}
?>
