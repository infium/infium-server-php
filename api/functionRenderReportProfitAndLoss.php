<?php
/*
 * Copyright 2012-2018 Marcus Hammar
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

function renderReportProfitAndLoss($dateFrom, $dateTo, $template){
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
	<title>Profit and loss statement</title>
	</head>
	<body>

	<table style="border: 0; width: 90%; margin-left:5%; margin-right:5%;">
	<tr style="border: 0;">
	<td style="width: 50%; border: 0; vertical-align: middle; text-align: center;">
	<p>
	</p>
	</td><td style="border: 0; width: 50%">
	<h1>
	Profit and loss statement
	</h1>

	<table style="'.getStyle('table','width: 100%;').'">
	<tr><td style="'.getStyle('td').'"><strong>From</strong></td><td style="'.getStyle('td').'">'.$dateFrom.'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>To</strong></td><td style="'.getStyle('td').'">'.$dateTo.'</</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Report created</strong></td><td style="'.getStyle('td').'">'.date("Y-m-d H:i:s").'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Last journal voucher</strong></td><td style="'.getStyle('td').'">'.$lastNumber.'</td></tr>
	<tr><td style="'.getStyle('td').'"><strong>Currency</strong></td><td style="'.getStyle('td').'">'.$currency.'</td></tr>
	</table>

	</td>
	</tr>
	</table>

	<div style="height: 30px;">&nbsp;</div>

	<table style="'.getStyle('table','width: 90%; margin-left:5%; margin-right:5%;').'">
	<tr><td style="'.getStyle('td','width: 80%;').'"><strong>Description</strong></td><td style="'.getStyle('td','width: 20%; text-align: right;').'"><strong>Amount</strong></td></tr>';

  $level = 0;
  $sumAmount = NULL;
  $sumRows = NULL;
  $pendingHeaders = NULL;
  $accountsInReport = array();
  $accountsInReportWithBalance = array();

  $sumAmount[0] = 0;
  $sumRows[0] = 0;
	$sumGrandTotal = 0;

  $AccountYear = substr($dateFrom,0,4);

  function createSpace($width) {
    $outputSpaces = "";
    for ($i = 0; $i < $width; $i++) {
      $outputSpaces .= '&nbsp;&nbsp;';
    }
    return $outputSpaces;
  }

  function processSection($pdo, &$output, $parentId, $parentSection, $AccountYear, $dateFrom, $dateTo, $template, &$level, &$sumAmount, &$sumRows, &$sumGrandTotal, &$pendingHeaders, &$accountsInReport, &$accountsInReportWithBalance){
    $itemsInSection = dbPrepareExecute($pdo, 'SELECT Id, SectionDescription, AccountNumber FROM ReportTemplateRow WHERE ParentId=? AND ParentSection=? ORDER BY \'Order\' ASC', array($parentId, $parentSection));
    foreach ($itemsInSection as $row){
      if ($row['SectionDescription'] != NULL){
        $level++;

        $pendingHeaders[$level] = $row['SectionDescription'];

        processSection($pdo, $output, $parentId, $row['Id'], $AccountYear, $dateFrom, $dateTo, $template, $level, $sumAmount, $sumRows, $sumGrandTotal, $pendingHeaders, $accountsInReport, $accountsInReportWithBalance);

        if (isset($sumRows[$level])&&($sumRows[$level]!=0)){
          $output .= '<tr><td style="'.getStyle('td').'">'.createSpace($level - 1).'Sum '.$row['SectionDescription'].'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumAmount[$level]*-1).'</td></tr>'."\n";
        }
        $previousLevel = $level - 1;

        if (isset($sumAmount[$level])){
          if (isset($sumAmount[$previousLevel])){
            $sumAmount[$previousLevel] += $sumAmount[$level];
          }else{
            $sumAmount[$previousLevel] = $sumAmount[$level];
          }
        }
        $sumAmount[$level] = 0;

        if (isset($sumRows[$level])){
          if (isset($sumRows[$previousLevel])){
            $sumRows[$previousLevel] += $sumRows[$level];
          }else{
            $sumRows[$previousLevel] = $sumRows[$level];
          }
        }
        $sumRows[$level] = 0;
        $level--;
      }
      if ($row['AccountNumber'] != NULL){
        foreach ($accountsInReport as $acc){
          if ($acc == $row['AccountNumber']){
            throw new Exception('Account "'.$row['AccountNumber'].'" exist multiple times in the report template. Please ensure that the account is only present once in the report template.');
          }
        }
        $accountsInReport[] = $row['AccountNumber'];

        $accountDescription = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerAccount WHERE Year=? AND Type=\'PL\' AND AccountNumber=?', array($AccountYear, $row['AccountNumber']));
        $accountSumAmount = dbPrepareExecute($pdo, 'SELECT SUM(Amount) as Amount FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND BookingDate>=? AND BookingDate<=?', array($AccountYear, $row['AccountNumber'], $dateFrom, $dateTo));
        $accountSumRows = dbPrepareExecute($pdo, 'SELECT COUNT(*) as Rows FROM GeneralLedgerAccountBalance WHERE Year=? AND AccountNumber=? AND BookingDate>=? AND BookingDate<=?', array($AccountYear, $row['AccountNumber'], $dateFrom, $dateTo));

        if (isset($sumRows[$level])){
          $sumRows[$level] += $accountSumRows[0]['Rows'];
        }else{
          $sumRows[$level] = $accountSumRows[0]['Rows'];
        }

        if (isset($accountSumRows[0]['Rows'])&&($accountSumRows[0]['Rows'] > 0)){
          for ($i = 0; $i <= 16; $i++) {
            if (isset($pendingHeaders[$i])){
              if ($pendingHeaders[$i] != ''){
                $output .= '<tr><td style="'.getStyle('td').'">'.createSpace($i - 1).$pendingHeaders[$i].'</td><td style="'.getStyle('td','text-align: right;').'">'."".'</td></tr>'."\n";
                $pendingHeaders[$i] = '';
              }
            }
          }

          $output .= '<tr><td style="'.getStyle('td').'">'.createSpace($level).$row['AccountNumber'].' '.$accountDescription[0]['Description'].'</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($accountSumAmount[0]['Amount']*-1).'</td></tr>'."\n";
          if (isset($sumAmount[$level])){
            $sumAmount[$level] += $accountSumAmount[0]['Amount'];
          }else{
            $sumAmount[$level] = $accountSumAmount[0]['Amount'];
          }
          $accountsInReportWithBalance[] = $row['AccountNumber'];
					$sumGrandTotal += $accountSumAmount[0]['Amount'];
        }
      }
    }
  }

  $reportTemplateId = dbPrepareExecute($pdo, 'SELECT Id FROM ReportTemplate WHERE Year=? AND Type=\'PL\' AND Description=?', array($AccountYear, $template));

  if (count($reportTemplateId) != 1){
    throw new Exception('The template does not exist in the year.');
  }

  processSection($pdo, $output, $reportTemplateId[0]['Id'], 0, $AccountYear, $dateFrom, $dateTo, $template, $level, $sumAmount, $sumRows, $sumGrandTotal, $pendingHeaders, $accountsInReport, $accountsInReportWithBalance);

  $accountsWithBalanceInDatabase = dbPrepareExecute($pdo, 'SELECT DISTINCT AccountNumber FROM GeneralLedgerAccountBalance WHERE Year=? AND BookingDate>=? AND BookingDate<=?', array($AccountYear, $dateFrom, $dateTo));
  foreach ($accountsWithBalanceInDatabase as $accountInDatabase){
    $exist = False;
    foreach ($accountsInReportWithBalance as $accountInReport){
      if ($accountInDatabase['AccountNumber'] == $accountInReport){
        $exist = True;
      }
    }
    if ($exist == False){
      $accountMasterData = dbPrepareExecute($pdo, 'SELECT Type FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?', array($AccountYear, $accountInDatabase['AccountNumber']));
      if ($accountMasterData[0]['Type'] == 'PL'){
        throw new Exception('Account "'.$accountInDatabase['AccountNumber'].'" is missing in the report template.');
      }
    }
  }

	$output .= '<tr><td style="'.getStyle('td').'">Sum of all accounts in the profit and loss statement</td><td style="'.getStyle('td','text-align: right;').'">'.decimalFormat($sumGrandTotal*-1).'</td></tr>'."\n";

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
