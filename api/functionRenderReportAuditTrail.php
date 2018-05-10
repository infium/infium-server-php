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

function renderReportAuditTrail($type, $number){
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

	if (($type == 'CustomerInvoice')||($type == 'CustomerPayment')||($type == 'VendorInvoice')||($type == 'VendorPaymentList')||($type == 'VendorPaymentCompleted')||($type == 'GeneralLedgerAccountBooking')||($type == 'GeneralLedgerAccountClearing')||($type == 'TaxReport')){
		$id = dbPrepareExecute($pdo, 'SELECT Id FROM '.$type.' WHERE Number=?', array($number));

		$auditTrailHeader = dbPrepareExecute($pdo, 'SELECT `Operation`, `Data`, `Time`, `User`, `IP` FROM `AuditTrail` WHERE `Table`=? AND `TableId`=?', array($type, $id[0]['Id']));

		$rowId = dbPrepareExecute($pdo, 'SELECT Id FROM '.$type.'Row WHERE ParentId=? ORDER BY Id', array($id[0]['Id']));
	}

	if (($type == 'Customer')||($type == 'Vendor')||($type == 'Article')){
		$id = dbPrepareExecute($pdo, 'SELECT Id FROM '.$type.' WHERE Number=?', array($number));

		$auditTrailHeader = dbPrepareExecute($pdo, 'SELECT `Operation`, `Data`, `Time`, `User`, `IP` FROM `AuditTrail` WHERE `Table`=? AND `TableId`=?', array($type, $id[0]['Id']));
	}

	if ($type == 'User'){
		$id = dbPrepareExecute($pdo, 'SELECT Id FROM '.$type.' WHERE Username=?', array($number));

		$auditTrailHeader = dbPrepareExecute($pdo, 'SELECT `Operation`, `Data`, `Time`, `User`, `IP` FROM `AuditTrail` WHERE `Table`=? AND `TableId`=?', array($type, $id[0]['Id']));
	}

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


	<title>Audit trail report</title>
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
	Audit trail report
	</h1>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td').'"><strong>Type</strong></td><td style="'.getStyle('td').'">'.$type.'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Document number</strong></td><td style="'.getStyle('td').'">'.$number.'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Report date</strong></td><td style="'.getStyle('td').'">'.date('Y-m-d H:i:s').'</td></tr>
	</table>

	</td>
	</tr>
	</table>

	<div style="height: 30px;"></div>

	<table style="'.getStyle('table','width: 100%;').'">';


	if (($type == 'CustomerInvoice')||($type == 'CustomerPayment')||($type == 'VendorInvoice')||($type == 'VendorPaymentList')||($type == 'VendorPaymentCompleted')||($type == 'GeneralLedgerAccountBooking')||($type == 'GeneralLedgerAccountClearing')||($type == 'TaxReport')){

		$previousRowData = array();

		foreach ($auditTrailHeader as $row){
			$output .= '<tr><td style="'.getStyle('td').'" colspan="2"><strong>Header - '.$row['Time'].' - '.$row['IP'].' - '.getUserName($pdo, $row['User']).' - '.$row['Operation']."</strong></td></tr>\n";

			$data = json_decode($row['Data'], TRUE);

			foreach ($data as $key => $value){
				if (array_key_exists($key, $previousRowData)){
					if ($previousRowData[$key] == $value){
						// No output
					}else{
						$output .= '<tr><td style="'.getStyle('td').'">'.$key.'</td><td style="'.getStyle('td').'">'.str_replace('","', '", "', $value)."</td></tr>\n";
					}
				}else{
					$output .= '<tr><td style="'.getStyle('td').'">'.$key.'</td><td style="'.getStyle('td').'">'.str_replace('","', '", "', $value)."</td></tr>\n";
				}
			}
			$previousRowData = $data;
		}

		$rowNumber = 0;
		foreach ($rowId as $row1){
			$rowNumber++;
			$auditTrailRow = dbPrepareExecute($pdo, 'SELECT `Operation`, `Data`, `Time`, `User`, `IP` FROM `AuditTrail` WHERE `Table`=? AND `TableId`=?', array($type.'Row', $row1['Id']));

			$previousRowData = array();

			foreach ($auditTrailRow as $row2){
				$output .= '<tr><td style="'.getStyle('td').'" colspan="2"><strong>Row '.$rowNumber.' - '.$row2['Time'].' - '.$row2['IP'].' - '.getUserName($pdo, $row2['User']).' - '.$row2['Operation']."</strong></td></tr>\n";
				$data = json_decode($row2['Data'], TRUE);
				foreach ($data as $key => $value){
					if (array_key_exists($key, $previousRowData)){
						if ($previousRowData[$key] == $value){
							// No output
						}else{
							$output .= '<tr><td style="'.getStyle('td').'">'.$key.'</td><td style="'.getStyle('td').'">'.str_replace('","', '", "', $value)."</td></tr>\n";
						}
					}else{
						$output .= '<tr><td style="'.getStyle('td').'">'.$key.'</td><td style="'.getStyle('td').'">'.str_replace('","', '", "', $value)."</td></tr>\n";
					}
				}
				$previousRowData = $data;
			}
		}
	}


	if (($type == 'Customer')||($type == 'Vendor')||($type == 'Article')||($type == 'User')){

		$previousRowData = array();

		foreach ($auditTrailHeader as $row){
			$output .= '<tr><td style="'.getStyle('td').'" colspan="2"><strong>'.$row['Time'].' - '.$row['IP'].' - '.getUserName($pdo, $row['User']).' - '.$row['Operation']."</strong></td></tr>\n";

			$data = json_decode($row['Data'], TRUE);

			foreach ($data as $key => $value){
				if (array_key_exists($key, $previousRowData)){
					if ($previousRowData[$key] == $value){
						// No output
					}else{
						$output .= '<tr><td style="'.getStyle('td').'">'.$key.'</td><td style="'.getStyle('td').'">'.str_replace('","', '", "', $value)."</td></tr>\n";
					}
				}else{
					$output .= '<tr><td style="'.getStyle('td').'">'.$key.'</td><td style="'.getStyle('td').'">'.str_replace('","', '", "', $value)."</td></tr>\n";
				}
			}
			$previousRowData = $data;
		}
	}

	$output .= '

	</table>

	<div style="height: 30px;"></div>

	<p style="text-align: center;">'.$documentFoot.'</p>

	</body>
	</html>';

	$pdo->exec('ROLLBACK;');

	return $output;
}
?>
