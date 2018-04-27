<?php
/*
 * Copyright 2012-2018 Infium AB
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

function createJournalVoucherOutput(&$output, $pdo, $journalVoucher) {

  $journalVoucherHeader = dbPrepareExecute($pdo, 'SELECT Id, Number, Year, BookingDate, Text FROM GeneralLedgerAccountBooking WHERE Number=?', array($journalVoucher));

  $journalVoucherRows = dbPrepareExecute($pdo, 'SELECT AccountNumber, Debit, Credit FROM GeneralLedgerAccountBookingRow WHERE ParentId=?', array($journalVoucherHeader[0]['Id']));

	$output .= '<table style="'.getStyle('table','width: 90%; margin-left:5%; margin-right:5%;').'">';
	$output .= '<tr><td style="'.getStyle('td').'" colspan="6"><strong>'.$journalVoucherHeader[0]['Number'].' '.$journalVoucherHeader[0]['Text'].'</strong></td></tr>';
  $output .= '<tr><td style="'.getStyle('td','width: 12%;').'"><strong>Date</strong></td><td style="'.getStyle('td','width: 67%;').'"><strong>Account</strong></td><td style="'.getStyle('td','width: 10%; text-align: right;').'"><strong>Debit</strong></td><td style="'.getStyle('td','width: 10%; text-align: right;').'"><strong>Credit</strong></td></tr>';

  $sumDebit = 0;
  $sumCredit = 0;

  foreach ($journalVoucherRows as $row){
    $accountDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?', array(substr($journalVoucherHeader[0]['BookingDate'], 0, 4), $row['AccountNumber']));

    $sumDebit += $row['Debit'];
    $sumCredit += $row['Credit'];

    $output .= '<tr><td style="'.getStyle('td').'">'.$journalVoucherHeader[0]['BookingDate'].'</td><td style="'.getStyle('td').'">'.$row['AccountNumber'].' '.$accountDescription[0]['Description'].'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($row['Debit']).'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($row['Credit']).'</td></tr>'."\n";
  }

  $output .= '<tr><td style="'.getStyle('td').'">&nbsp;</td><td style="'.getStyle('td').'">Sum</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumDebit).'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumCredit).'</td></tr>'."\n";

	$output .= '</table>';

	$output .= '<div style="height: 30px;">&nbsp;</div>';
}

function renderReportJournalVouchers($from, $to){
	$pdo = createPdo();

  $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT;');

	if (!function_exists('getStyle')) {
		function getStyle($object, $extra = NULL){
			if ($object == 'table'){
				$style = 'border-collapse: collapse; border: 1px solid black; font-size: 10pt;';
			}

			if ($object == 'td'){
				$style = 'border: 1px solid black; font-size: 10pt; padding: 2px;';
			}

			if ($extra != NULL){
				$style = $style.' '.$extra;
			}

			return $style;
		}
	}

  $lastNumberResult = dbPrepareExecute($pdo, 'SELECT Number FROM GeneralLedgerAccountBooking ORDER BY Number DESC LIMIT 0, 1', array());
	$lastNumber = $lastNumberResult[0]['Number'];

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
	    border: 1px solid black;
		font-size: 10pt;
	}

	td {
	    padding: 2px;
	}

	p {
		font-size: 10pt;
	}

	</style>
	<title>Journal vouchers</title>
	</head>
	<body>

	<table style="border: 0; width: 90%; margin-left:5%; margin-right:5%;">
	<tr style="border: 0;">
	<td style="width: 50%; border: 0; vertical-align: middle; text-align: center;">
	<p>
	</p>
	</td><td style="border: 0; width: 50%">
	<h1>
	Journal vouchers
	</h1>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td').'"><strong>From</strong></td><td style="'.getStyle('td').'">'.$from.'</</td></tr>
  <tr><td style="'.getStyle('td').'"><strong>To</strong></td><td style="'.getStyle('td').'">'.$to.'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Report created</strong></td><td style="'.getStyle('td').'">'.date("Y-m-d H:i:s").'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Last journal voucher</strong></td><td style="'.getStyle('td').'">'.$lastNumber.'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Currency</strong></td><td style="'.getStyle('td').'">'.$currency.'</td></tr>
	</table>

	</td>
	</tr>
	</table>

	<div style="height: 30px;">&nbsp;</div>';

	$journalVouchers = dbPrepareExecute($pdo, 'SELECT Number FROM GeneralLedgerAccountBooking WHERE Number>=? AND Number<=? ORDER BY Number ASC', array($from, $to));

	foreach ($journalVouchers as $journalVoucher){
		createJournalVoucherOutput($output, $pdo, $journalVoucher['Number']);
	}

  $pdo->exec('ROLLBACK');

	$output .= '

	<p style="text-align: center;">'.$documentFoot.'</p>

	</body>
	</html>';

	return $output;
}
?>
