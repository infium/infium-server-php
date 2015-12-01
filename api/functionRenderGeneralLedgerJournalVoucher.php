<?php
require_once('config.php');

function renderGeneralLedgerJournalVoucher($documentNumber){
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
		
			if ($extra != NULL){
				$style = $style.' '.$extra;
			}
			
			return $style;
		}
	}
	
	$pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT;');

	$stmt = $pdo->prepare('SELECT Id, Number, Year, BookingDate, Text FROM GeneralLedgerAccountBooking WHERE Number=?');
	$stmt->execute(array($documentNumber));
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$stmt2 = $pdo->prepare('SELECT AccountNumber, Debit, Credit FROM GeneralLedgerAccountBookingRow WHERE ParentId=?');
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
	<title>Journal voucher #'.$results[0]['Number'].'</title>
	</head>
	<body>

	<table style="border: 0; width: 100%;">
	<tr style="border: 0;">
	<td style="width: 50%; border: 0; vertical-align: middle; text-align: center;">
	<p>
	</p>
	</td><td style="border: 0; width: 50%">
	<h1>
	Journal voucher
	</h1>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td').'"><strong>Number</strong></td><td style="'.getStyle('td', 'vertical-align: middle;').'">'.$results[0]['Number'].'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Booking date</strong></td><td style="'.getStyle('td').'">'.$results[0]['BookingDate'].'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Text</strong></td><td style="'.getStyle('td').'">'.$results[0]['Text'].'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Currency</strong></td><td style="'.getStyle('td').'">'.$currency.'</td></tr>
	</table>

	</td>
	</tr>
	</table>

	<div style="height: 30px;">&nbsp;</div>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td','width: 60%;').'"><strong>Account</strong></td><td style="'.getStyle('td','width: 20%; text-align: right;').'"><strong>Debit</strong></td><td style="'.getStyle('td','width: 20%; text-align: right;').'"><strong>Credit</strong></td></tr>';
	
	$sumDebit = 0;
	$sumCredit = 0;
	
	foreach ($results2 as $row){
	
		$stmt3 = $pdo->prepare('SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?');
		$stmt3->execute(array(substr($results[0]['BookingDate'], 0, 4), $row['AccountNumber']));
		$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
		
		$sumDebit += $row['Debit'];
		$sumCredit += $row['Credit'];
		
		$output .= '<tr><td style="'.getStyle('td').'">'.$row['AccountNumber'].' '.$results3[0]['Description'].'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($row['Debit']).'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($row['Credit']).'</td></tr>'."\n";
	
	}
	
	$output .= '<tr><td style="'.getStyle('td').'">Sum</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumDebit).'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumCredit).'</td></tr>'."\n";
		
	$output .= '

	</table>


	<div style="height: 30px;">&nbsp;</div>

	<p style="text-align: center;">'.$documentFoot.'</p>

	</body>
	</html>';
	
	$pdo->exec('ROLLBACK;');
	
	return $output;
}
?>