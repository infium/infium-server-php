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

function createAccountOutput(&$output, $pdo, $dateFrom, $dateTo, $account) {
	$accountYear = substr($dateFrom,0,4);

  validateAccountNumber($pdo, $accountYear, $account);

	$accountDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?', array($accountYear, $account));

	$output .= '<table style="'.getStyle('table','width: 90%; margin-left:5%; margin-right:5%;').'">';
	$output .= '<tr><td style="'.getStyle('td').'" colspan="6"><strong>'.$account.' '.$accountDescription[0]['Description'].'</strong></td></tr>';
  $output .= '<tr><td style="'.getStyle('td','width: 12%;').'"><strong>Date</strong></td><td style="'.getStyle('td','width: 10%;').'"><strong>Journal voucher</strong></td><td style="'.getStyle('td','width: 48%;').'"><strong>Description</strong></td><td style="'.getStyle('td','width: 10%; text-align: right;').'"><strong>Debit</strong></td><td style="'.getStyle('td','width: 10%; text-align: right;').'"><strong>Credit</strong></td><td style="'.getStyle('td','width: 10%; text-align: right;').'"><strong>Balance</strong></td></tr>';

  $sumBalance = 0;
	$sumDebit = 0;
	$sumCredit = 0;

	$openingBalance = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND (BookingDate<? OR BookingDate IS NULL)', array($accountYear, $account, $dateFrom));

  $sumBalance += $openingBalance[0]['Amount'];

  $output .= '<tr><td style="'.getStyle('td').'">&nbsp;</td><td style="'.getStyle('td').'">&nbsp;</td><td style="'.getStyle('td').'">Opening balance</td><td style="'.getStyle('td','text-align: right;').'">&nbsp;</td><td style="'.getStyle('td','text-align: right;').'">&nbsp;</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumBalance).'</td></tr>';

	$bookingRows = dbPrepareExecute($pdo, 'SELECT Number, BookingDate, Text, Amount FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? AND BookingDate>=? AND BookingDate<=? ORDER BY BookingDate, Id', array($account, $dateFrom, $dateTo));

  foreach ($bookingRows as $row){
    $sumBalance += $row['Amount'];

    if ($row['Amount'] > 0) {
      $debit = decimalFormat($row['Amount']);
      $credit = '';
			$sumDebit += $row['Amount'];
    } else {
      $debit = '';
      $credit = decimalFormat($row['Amount']*-1);
			$sumCredit += $row['Amount']*-1;
    }

    $balance = decimalFormat($sumBalance);

    $output .= '<tr><td style="'.getStyle('td').'">'.$row['BookingDate'].'</td><td style="'.getStyle('td').'">'.$row['Number'].'</td><td style="'.getStyle('td').'">'.$row['Text'].'</td><td style="'.getStyle('td','text-align: right;').'">'.$debit.'</td><td style="'.getStyle('td','text-align: right;').'">'.$credit.'</td><td style="'.getStyle('td','text-align: right;').'">'.$balance.'</td></tr>';
  }

	$output .= '<tr><td style="'.getStyle('td').'">&nbsp;</td><td style="'.getStyle('td').'">&nbsp;</td><td style="'.getStyle('td').'">Period total</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumDebit).'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumCredit).'</td><td style="'.getStyle('td','text-align: right;').'">&nbsp;</td></tr>';
  $output .= '<tr><td style="'.getStyle('td').'">&nbsp;</td><td style="'.getStyle('td').'">&nbsp;</td><td style="'.getStyle('td').'">Closing balance</td><td style="'.getStyle('td','text-align: right;').'">&nbsp;</td><td style="'.getStyle('td','text-align: right;').'">&nbsp;</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumBalance).'</td></tr>';

	$output .= '</table>';
	$output .= '<div style="height: 30px;">&nbsp;</div>';
}

function renderReportGeneralLedger($dateFrom, $dateTo, $account){
	$pdo = createPdo();

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

	$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT;');

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
	<title>General ledger</title>
	</head>
	<body>

	<table style="border: 0; width: 90%; margin-left:5%; margin-right:5%;">
	<tr style="border: 0;">
	<td style="width: 50%; border: 0; vertical-align: middle; text-align: center;">
	<p>
	</p>
	</td><td style="border: 0; width: 50%">
	<h1>
	General ledger
	</h1>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td').'"><strong>Account</strong></td><td style="'.getStyle('td').'">'.$account.'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>From</strong></td><td style="'.getStyle('td').'">'.$dateFrom.'</</td></tr>
  <tr><td style="'.getStyle('td').'"><strong>To</strong></td><td style="'.getStyle('td').'">'.$dateTo.'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Report created</strong></td><td style="'.getStyle('td').'">'.date("Y-m-d H:i:s").'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Last journal voucher</strong></td><td style="'.getStyle('td').'">'.$lastNumber.'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Currency</strong></td><td style="'.getStyle('td').'">'.$currency.'</td></tr>
	</table>

	</td>
	</tr>
	</table>

	<div style="height: 30px;">&nbsp;</div>';

	if ($account == 'ALL') {
		$accountYear = substr($dateFrom,0,4);

		$relevantAccounts = dbPrepareExecute($pdo, 'SELECT DISTINCT AccountNumber FROM GeneralLedgerAccountBalance WHERE Year=? AND (BookingDate<=? OR BookingDate IS NULL) ORDER BY AccountNumber ASC', array($accountYear, $dateTo));

		foreach ($relevantAccounts as $specificAccount){
			createAccountOutput($output, $pdo, $dateFrom, $dateTo, $specificAccount['AccountNumber']);
		}

	} else {
		createAccountOutput($output, $pdo, $dateFrom, $dateTo, $account);
	}

  $pdo->exec('ROLLBACK');

	$output .= '

	<p style="text-align: center;">'.$documentFoot.'</p>

	</body>
	</html>';

	return $output;
}
?>
