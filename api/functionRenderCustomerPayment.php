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

function renderCustomerPayment($documentNumber){
	$pdo = createPdo();

	$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');

	$stmt = $pdo->prepare('SELECT Id, Number, BookingDate, PartnerDate, AccountNumber, Amount FROM CustomerPayment WHERE Number=?');
	$stmt->execute(array($documentNumber));
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$stmt2 = $pdo->prepare('SELECT PaymentReference, Amount FROM CustomerPaymentRow WHERE ParentId=?');
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
	<title>Customer payment #'.$results[0]['Number'].'</title>
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
	Customer payment
	</h1>

	<table style="width: 100%;">
	<tr><td><strong>Payment number</strong></td><td>'.$results[0]['Number'].'</td></tr>
	<tr><td><strong>Payment date</strong></td><td>'.$results[0]['PartnerDate'].'</</td></tr>
	<tr><td><strong>Account</strong></td><td>'.$results[0]['AccountNumber'].'</</td></tr>
	<tr><td><strong>Currency</strong></td><td>'.$currency.'</td></tr>
	</table>

	</td>
	</tr>
	</table>

	<div style="height: 30px;"></div>

	<table style="width: 100%;">
	<tr><td style="width: 70%;"><strong>Payment reference</strong></td><td style="width: 30%; text-align: right;"><strong>Amount</strong></td></tr>';

	foreach ($results2 as $row){
		$output .= '<tr><td>'.$row['PaymentReference'].'</td><td style="text-align: right;">'.decimalFormat($row['Amount'])."</td></tr>\n";

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
	<tr><td><strong>Sum</strong></td><td style="text-align: right;">'.decimalFormat($results[0]['Amount']).'</td></tr>
	</table>

	</td></tr></table>

	<div style="height: 30px;"></div>

	<p style="text-align: center;">'.$documentFoot.'</p>

	</body>
	</html>';

	$pdo->exec('ROLLBACK');

	return $output;
}
?>
