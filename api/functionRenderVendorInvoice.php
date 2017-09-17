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

require_once('config.php');

function renderVendorInvoice($documentNumber){
	$pdo = createPdo();

	$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT;');

	$stmt = $pdo->prepare('SELECT Id, Number, BookingDate, PartnerDate, DueDate, VendorId, BankAccount, PaymentReference, PaymentTerms, BillFromAddressLine1, BillFromAddressLine2, BillFromAddressLine3, BillFromAddressLine4, BillFromAddressCity, BillFromAddressStateOrProvince, BillFromAddressZipOrPostalCode, BillFromAddressCountry, AmountNet, AmountTax, AmountGross FROM VendorInvoice WHERE Number=?');
	$stmt->execute(array($documentNumber));
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$stmt2 = $pdo->prepare('SELECT Account, Description, TaxPercent, AmountNet, AmountTax, AmountGross FROM VendorInvoiceRow WHERE ParentId=?');
	$stmt2->execute(array($results[0]['Id']));
	$results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
	
	$currencyResult = dbPrepareExecute($pdo, 'SELECT Value FROM Property WHERE Property = ?', array('Currency'));
	$currency = $currencyResult[0]['Value'];

	$documentFootResult = dbPrepareExecute($pdo, 'SELECT Value FROM Property WHERE Property = ?', array('DocumentFoot'));
	$documentFoot = $documentFootResult[0]['Value'];
	
	
	$output = '<!DOCTYPE html>
	<html>
	<head>
	<meta charset="utf-8"/>
	<style type="text/css">
	table {
	    border-collapse: collapse;
	}

	table, td, th {
	    border: 2px solid black;
		font-size: 11.5pt;
	}

	td { 
	    padding: 3px;
	}

	p {
		font-size: 11.5pt;
	}

	</style>
	<title>Vendor invoice #'.$results[0]['Number'].'</title>
	</head>
	<body>

	<table style="border: 0; width: 100%;">
	<tr style="border: 0;">
	<td style="width: 50%; border: 0; vertical-align: middle; text-align: center;">
	<p>
	&nbsp;
	</p>
	</td><td style="border: 0; width: 50%">
	<h1>
	Vendor invoice
	</h1>

	<table style="width: 100%;">
	<tr><td><strong>Invoice number</strong></td><td>'.$results[0]['PaymentReference'].'</td></tr>
	<tr><td><strong>Booking date</strong></td><td>'.$results[0]['BookingDate'].'</</td></tr>
	<tr><td><strong>Invoice date</strong></td><td>'.$results[0]['PartnerDate'].'</</td></tr>
	<tr><td><strong>Due date</strong></td><td>'.$results[0]['DueDate'].'</</td></tr>
	<tr><td><strong>Payment terms</strong></td><td>'.$results[0]['PaymentTerms'].'</td></tr>
	<tr><td><strong>BankAccount</strong></td><td>'.$results[0]['BankAccount'].'</td></tr>
	<tr><td><strong>Currency</strong></td><td>'.$currency.'</td></tr>
	<tr><td><strong>Ref. our system</strong></td><td>'.$results[0]['Number'].'</td></tr>
	</table>

	</td>
	</tr>
	</table>

	<div style="height: 30px;"></div>

	<table style="width: 50%;">
	<tr><td style="width: 100%; vertical-align:top;">
	<strong>Vendor</strong><br/>';

	if ($results[0]['BillFromAddressLine1'] != ''){$output .= $results[0]['BillFromAddressLine1'].'<br/>';}
	if ($results[0]['BillFromAddressLine2'] != ''){$output .= $results[0]['BillFromAddressLine2'].'<br/>';}
	if ($results[0]['BillFromAddressLine3'] != ''){$output .= $results[0]['BillFromAddressLine3'].'<br/>';}
	if ($results[0]['BillFromAddressLine4'] != ''){$output .= $results[0]['BillFromAddressLine4'].'<br/>';}
	if ($results[0]['BillFromAddressCity'] != ''){$output .= $results[0]['BillFromAddressCity'].'<br/>';}
	if ($results[0]['BillFromAddressStateOrProvince'] != ''){$output .= $results[0]['BillFromAddressStateOrProvince'].'<br/>';}
	if ($results[0]['BillFromAddressZipOrPostalCode'] != ''){$output .= $results[0]['BillFromAddressZipOrPostalCode'].'<br/>';}
	if ($results[0]['BillFromAddressCountry'] != ''){$output .= $results[0]['BillFromAddressCountry'].'<br/>';}
	
	$output .= '
	</td></tr>
	</table>
	
	<div style="height: 30px;"></div>

	<table style="width: 100%;">
	<tr><td style="width: 15%;"><strong>Account</strong></td><td><strong>Description</strong></td><td style="width: 15%; text-align: right;"><strong>Amount</strong></td></tr>';

	foreach ($results2 as $row){
		if ($row['Account'] != NULL){
			$stmt3 = $pdo->prepare('SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?');
			$stmt3->execute(array(substr($results[0]['BookingDate'], 0, 4), $row['Account']));
			$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
	
			$output .= '<tr><td>'.$row['Account'].'</td><td>'.$results3[0]['Description'].'</td><td style="text-align: right;">'.decimalFormat($row['AmountNet'])."</td></tr>\n";
		}
	}
	
	$output .= '

	</table>

	<div style="height: 30px;"></div>

	<table style="border: 0; width: 100%;">
	<tr><td style="border: 0; width: 60%; vertical-align:top;">

	<table style="width: 100%">
	<tr><td><strong>Remarks</strong></td></tr>
	</table>

	</td><td style="border: 0; width: 40%; vertical-align:top;">

	<table style="width: 100%;">
	<tr><td><strong>Net amount</strong></td><td style="text-align: right;">'.decimalFormat($results[0]['AmountNet']).'</td></tr>
	<tr><td><strong>Tax</strong></td><td style="text-align: right;">'.decimalFormat($results[0]['AmountTax']).'</td></tr>
	<tr><td><strong>Gross amount</strong></td><td style="text-align: right;">'.decimalFormat($results[0]['AmountGross']).'</td></tr>
	</table>

	</td></tr></table>

	<div style="height: 30px;"></div>

	<p style="text-align: center;">'.$documentFoot.'</p>

	</body>
	</html>';
	
	$pdo->exec('ROLLBACK;');
	
	return $output;
}
?>