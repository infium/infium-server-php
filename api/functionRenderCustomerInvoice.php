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

require_once('config.php');

function renderCustomerInvoice($documentNumber){
	$pdo = createPdo();

	$styleTable = ' style="border-collapse: collapse; border: 2px solid black; font-size: 11.5pt;"';
	$styleThTd = ' style="border: 2px solid black; font-size: 11.5pt; padding: 3px;"';

	if (!function_exists('getStyle')) {
		function getStyle($object, $extra = NULL){

			if ($object == 'table'){
				$style = 'border-collapse: collapse; border: 2px solid black; font-size: 11.5pt;';
			}

			if ($object == 'td'){
				$style = 'border: 2px solid black; font-size: 11.5pt; padding: 3px;';
			}

			if ($object == 'invisibleTable'){
				$style = 'border-collapse: collapse; border: 0px none black; font-size: 11.5pt;';
			}

			if ($object == 'invisibleTableTd'){
				$style = 'border: 0px none black; font-size: 11.5pt; padding: 3px; text-align: center;';
			}

			if ($extra != NULL){
				$style = $style.' '.$extra;
			}

			return $style;
		}
	}

	$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT;');

	$stmt = $pdo->prepare('SELECT Id, Number, BookingDate, DateDue, CustomerId, CustomerReference, PaymentTerms, TaxNumber, BillToAddressLine1, BillToAddressLine2, BillToAddressLine3, BillToAddressLine4, BillToAddressCity, BillToAddressStateOrProvince, BillToAddressZipOrPostalCode, BillToAddressCountry, ShipToAddressLine1, ShipToAddressLine2, ShipToAddressLine3, ShipToAddressLine4, ShipToAddressCity, ShipToAddressStateOrProvince, ShipToAddressZipOrPostalCode, ShipToAddressCountry, AmountNet, AmountTax, AmountGross FROM CustomerInvoice WHERE Number=?');
	$stmt->execute(array($documentNumber));
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$stmt2 = $pdo->prepare('SELECT ArticleId, Quantity, Price, TaxPercent, AmountNet, AmountTax, AmountGross FROM CustomerInvoiceRow WHERE ParentId=?');
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
	<title>Invoice #'.$results[0]['Number'].'</title>
	</head>
	<body>

	<table style="border: 0; width: 100%;">
	<tr style="border: 0;">
	<td style="width: 50%; border: 0; vertical-align: middle; text-align: center;">
	<p>
	<!--img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA
AAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO
9TXL0Y4OHwAAAABJRU5ErkJggg==" alt="Red dot"-->

	<!--img src="http://www.infium.com/images/logo.png"-->
	</p>
	</td><td style="border: 0; width: 50%">
	<h1>
	Invoice
	</h1>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td').'"><strong>Invoice number</strong></td><td style="'.getStyle('td', 'vertical-align: middle;').'">'.$results[0]['Number'].'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Invoice date</strong></td><td style="'.getStyle('td').'">'.$results[0]['BookingDate'].'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Due date</strong></td><td style="'.getStyle('td').'">'.$results[0]['DateDue'].'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Customer reference</strong></td><td style="'.getStyle('td').'">'.$results[0]['CustomerReference'].'</td></tr>';

	if ($results[0]['TaxNumber'] != ''){$output .= '<tr><td style="'.getStyle('td').'"><strong>VAT number</strong></td><td style="'.getStyle('td').'">'.$results[0]['TaxNumber'].'</td></tr>';}

	$output .= '<tr><td style="'.getStyle('td').'"><strong>Currency</strong></td><td style="'.getStyle('td').'">'.$currency.'</td></tr>
	</table>

	</td>
	</tr>
	</table>

	<div style="height: 30px;">&nbsp;</div>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td','width: 50%; vertical-align:top;').'">
	<strong>Bill to address</strong><br/>';

	if ($results[0]['BillToAddressLine1'] != ''){$output .= $results[0]['BillToAddressLine1'].'<br/>';}
	if ($results[0]['BillToAddressLine2'] != ''){$output .= $results[0]['BillToAddressLine2'].'<br/>';}
	if ($results[0]['BillToAddressLine3'] != ''){$output .= $results[0]['BillToAddressLine3'].'<br/>';}
	if ($results[0]['BillToAddressLine4'] != ''){$output .= $results[0]['BillToAddressLine4'].'<br/>';}
	if ($results[0]['BillToAddressCity'] != ''){$output .= $results[0]['BillToAddressCity'].'<br/>';}
	if ($results[0]['BillToAddressStateOrProvince'] != ''){$output .= $results[0]['BillToAddressStateOrProvince'].'<br/>';}
	if ($results[0]['BillToAddressZipOrPostalCode'] != ''){$output .= $results[0]['BillToAddressZipOrPostalCode'].'<br/>';}
	if ($results[0]['BillToAddressCountry'] != ''){$output .= $results[0]['BillToAddressCountry'].'<br/>';}

	$output .= '
	</td><td style="'.getStyle('td','width: 50%; vertical-align:top;').'">
	<strong>Ship to address</strong><br/>';

	if ($results[0]['ShipToAddressLine1'] != ''){$output .= $results[0]['ShipToAddressLine1'].'<br/>';}
	if ($results[0]['ShipToAddressLine2'] != ''){$output .= $results[0]['ShipToAddressLine2'].'<br/>';}
	if ($results[0]['ShipToAddressLine3'] != ''){$output .= $results[0]['ShipToAddressLine3'].'<br/>';}
	if ($results[0]['ShipToAddressLine4'] != ''){$output .= $results[0]['ShipToAddressLine4'].'<br/>';}
	if ($results[0]['ShipToAddressCity'] != ''){$output .= $results[0]['ShipToAddressCity'].'<br/>';}
	if ($results[0]['ShipToAddressStateOrProvince'] != ''){$output .= $results[0]['ShipToAddressStateOrProvince'].'<br/>';}
	if ($results[0]['ShipToAddressZipOrPostalCode'] != ''){$output .= $results[0]['ShipToAddressZipOrPostalCode'].'<br/>';}
	if ($results[0]['ShipToAddressCountry'] != ''){$output .= $results[0]['ShipToAddressCountry'].'<br/>';}

	$output .= '
	</td></tr>
	</table>

	<div style="height: 30px;">&nbsp;</div>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td','width: 15%;').'"><strong>Article</strong></td><td style="'.getStyle('td','width: 45%;').'"><strong>Description</strong></td><td style="'.getStyle('td','width: 10%; text-align: right;').'"><strong>Quantity</strong></td><td style="'.getStyle('td','width: 15%; text-align: right;').'"><strong>Price each</strong></td><td style="'.getStyle('td','width: 15%; text-align: right;').'"><strong>Price row</strong></td></tr>';

	foreach ($results2 as $row){

		$stmt3 = $pdo->prepare('SELECT Number, Description FROM Article WHERE Id=?');
		$stmt3->execute(array($row['ArticleId']));
		$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

		if (isset($taxSummary[$row['TaxPercent']]['AmountNet'])){
			$taxSummary[$row['TaxPercent']]['AmountNet'] += $row['AmountNet'];
		}else{
			$taxSummary[$row['TaxPercent']]['AmountNet'] = $row['AmountNet'];
		}

		if (isset($taxSummary[$row['TaxPercent']]['AmountTax'])){
			$taxSummary[$row['TaxPercent']]['AmountTax'] += $row['AmountTax'];
		}else{
			$taxSummary[$row['TaxPercent']]['AmountTax'] = $row['AmountTax'];
		}

		$output .= '<tr><td style="'.getStyle('td').'">'.$results3[0]['Number'].'</td><td style="'.getStyle('td').'">'.$results3[0]['Description'].'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($row['Quantity']).'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($row['Price']).'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($row['AmountNet'])."</td></tr>\n";

	}

	$output .= '

	</table>

	<div style="height: 30px;">&nbsp;</div>

	<table style="border: 0; width: 100%;">
	<tr><td style="border: 0; width: 60%; vertical-align:top;">

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td').'"><strong>Remarks</strong></td></tr>
	</table>

	</td><td style="border: 0; width: 40%; vertical-align:top;">

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td').'"><strong>Net amount</strong></td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($results[0]['AmountNet']).'</td></tr>';


	foreach ($taxSummary as $taxRowKey => $taxRowValue){
		$output .= '<tr><td style="'.getStyle('td').'"><strong>Tax '.number_format($taxRowKey*100, 0, '', '').'% on '.decimalFormat($taxRowValue['AmountNet']).'</strong></td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($taxRowValue['AmountTax']).'</td></tr>';
	}

	$output .= '

	<tr><td style="'.getStyle('td').'"><strong>Gross amount</strong></td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($results[0]['AmountGross']).'</td></tr>
	</table>

	</td></tr></table>

	<div style="height: 30px;">&nbsp;</div>

	<table style="'.getStyle('invisibleTable','width: 100%;').'">
	<tr><td style="'.getStyle('invisibleTableTd','width: 100%;').'">'.$documentFoot.'</td></tr>
	</table>

	</body>
	</html>';

	$pdo->exec('ROLLBACK;');

	return $output;
}

?>
