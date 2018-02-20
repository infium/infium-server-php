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

checkUserAccess('ReportAuditTrail');

$input = json_decode(file_get_contents('php://input'), TRUE);

$dataToReturn = '';

$dataToReturn['Response'] = 'SearchResult';
$dataToReturn['Data']['SearchQuery'] = '';
$dataToReturn['Data']['SearchSerialNumber'] = '';

$dataToReturn['Data']['Result'][0]['Value'] = 'CustomerInvoice';
$dataToReturn['Data']['Result'][0]['Description'] = 'Customer invoice';

$dataToReturn['Data']['Result'][1]['Value'] = 'CustomerPayment';
$dataToReturn['Data']['Result'][1]['Description'] = 'Customer payment';

$dataToReturn['Data']['Result'][2]['Value'] = 'VendorInvoice';
$dataToReturn['Data']['Result'][2]['Description'] = 'Vendor invoice';

$dataToReturn['Data']['Result'][3]['Value'] = 'VendorPaymentList';
$dataToReturn['Data']['Result'][3]['Description'] = 'Vendor payment list';

$dataToReturn['Data']['Result'][4]['Value'] = 'VendorPaymentCompleted';
$dataToReturn['Data']['Result'][4]['Description'] = 'Vendor payment completed';

$dataToReturn['Data']['Result'][5]['Value'] = 'GeneralLedgerAccountBooking';
$dataToReturn['Data']['Result'][5]['Description'] = 'Journal voucher';

$dataToReturn['Data']['Result'][6]['Value'] = 'GeneralLedgerAccountClearing';
$dataToReturn['Data']['Result'][6]['Description'] = 'Clearing';

$dataToReturn['Data']['Result'][7]['Value'] = 'Customer';
$dataToReturn['Data']['Result'][7]['Description'] = 'Customer';

$dataToReturn['Data']['Result'][8]['Value'] = 'Vendor';
$dataToReturn['Data']['Result'][8]['Description'] = 'Vendor';

$dataToReturn['Data']['Result'][9]['Value'] = 'Article';
$dataToReturn['Data']['Result'][9]['Description'] = 'Article';

$dataToReturn['Data']['Result'][10]['Value'] = 'TaxReport';
$dataToReturn['Data']['Result'][10]['Description'] = 'TaxReport';

$dataToReturn['Data']['Result'][11]['Value'] = 'User';
$dataToReturn['Data']['Result'][11]['Description'] = 'User';

echo json_encode($dataToReturn,JSON_PRETTY_PRINT);
?>
