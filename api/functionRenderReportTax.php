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

function renderReportTax($documentNumber){
	$pdo = createPdo();

	$styleTable = ' style="border-collapse: collapse; border: 2px solid black; font-size: 11.5pt;"';
	$styleThTd = ' style="border: 2px solid black; font-size: 11.5pt; padding: 3px;"';

	function getStyle($object, $extra = NULL){
		if ($object == 'table'){
			$style = 'border-collapse: collapse; border: 2px solid black; font-size: 11.5pt;';
		}

		if ($object == 'td'){
			$style = 'border: 2px solid black; font-size: 11.5pt; padding: 3px;';
		}

		if ($extra != NULL){
			$style = $style.' '.$extra;
		}

		return $style;
	}

	$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT;');

	$stmt = $pdo->prepare('SELECT Id, Number, BookingDate, FromDate, ToDate, Active, Reversal FROM TaxReport WHERE Number=?');
	$stmt->execute(array($documentNumber));
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$stmt2 = $pdo->prepare('SELECT Field, Description, Amount FROM TaxReportRow WHERE ParentId=? ORDER BY Id ASC');
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

	<title>Tax report #'.$results[0]['Number'].'</title>
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
	Tax report
	</h1>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td').'"><strong>Tax report number</strong></td><td style="'.getStyle('td').'">'.$results[0]['Number'].'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>From</strong></td><td style="'.getStyle('td').'">'.$results[0]['FromDate'].'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>To</strong></td><td style="'.getStyle('td').'">'.$results[0]['ToDate'].'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Booking date</strong></td><td style="'.getStyle('td').'">'.$results[0]['BookingDate'].'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Currency</strong></td><td style="'.getStyle('td').'">'.$currency.'</td></tr>
	</table>

	</td>
	</tr>
	</table>

	<div style="height: 30px;"></div>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td','width: 15%;').'"><strong>Field</strong></td><td style="'.getStyle('td').'"><strong>Description</strong></td><td style="'.getStyle('td','width: 15%; text-align: right;').'"><strong>Amount</strong></td></tr>';

	foreach ($results2 as $row){
		$output .= '<tr><td style="'.getStyle('td').'">'.$row['Field'].'</td><td style="'.getStyle('td').'">'.$row['Description'].'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($row['Amount'])."</td></tr>\n";

	}

	$output .= '

	</table>';

	$customers = dbPrepareExecute($pdo, 'SELECT DISTINCT TaxNumber FROM TaxReportRegionRow WHERE ParentId=?', array($results[0]['Id']));

	if (count($customers) > 0){
		$output .= '<div style="height: 30px;"></div><table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td','width: 15%;').'"><strong>Customer VAT</strong></td><td style="'.getStyle('td', 'width: 15%; text-align: right;').'"><strong>EU sales of products</strong></td><td style="'.getStyle('td','width: 15%; text-align: right;').'"><strong>EU sales of services</strong></td></tr>';

		foreach ($customers as $row){
			$amountProducts = dbPrepareExecute($pdo, 'SELECT SUM(Amount) AS Amount FROM TaxReportRegionRow WHERE ParentId=? AND TaxNumber=? AND ProductOrService=?', array($results[0]['Id'], $row['TaxNumber'], 'Product'));
			$amountServices = dbPrepareExecute($pdo, 'SELECT SUM(Amount) AS Amount FROM TaxReportRegionRow WHERE ParentId=? AND TaxNumber=? AND ProductOrService=?', array($results[0]['Id'], $row['TaxNumber'], 'Service'));

			$output .= '<tr><td style="'.getStyle('td').'">'.$row['TaxNumber'].'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($amountProducts[0]['Amount']).'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($amountServices[0]['Amount'])."</td></tr>\n";

		}

		$output .= '</table>';
	}

	$output .='

	<div style="height: 30px;"></div>

	<p style="text-align: center;">'.$documentFoot.'</p>

	</body>
	</html>';

	$pdo->exec('ROLLBACK;');

	return $output;
}
?>
