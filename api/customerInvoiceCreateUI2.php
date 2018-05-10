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

require('config.php');
require('classUserInterface.php');

checkUserAccess('CustomerInvoiceCreate');

try{
	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();

	validateDate($input['BookingDate']);
	validateCustomerNumber($pdo, $input['Customer']);

	$a = 0;
	foreach ($input['Row'] as $row){
		if (($row['ArticleID'] != '')||($row['Quantity'] != '')||($row['Price'] != '')){
			validateArticleNumber($pdo, $row['ArticleID']);
			validateNumber($row['Quantity']);
			validateNumber($row['Price']);
			$a++;
		}
	}

	if ($a == 0){
		throw new Exception('Document contains no rows.');
	}

	$ui = new UserInterface();

	$ui->setTitle('Invoice');
	$ui->setWindow('Same');
	$ui->setMethod('POST');
	$ui->setUrl($baseUrl.'customerInvoiceCreateProcess.php');
	$ui->setButtonLabel('Add');
	$ui->setTitleBarColorNewWindow($titleBarColorCustomerInvoice);


	$ui->addLabelValueLink('Booking date:', $input['BookingDate']);
	$hiddenData['BookingDate'] = $input['BookingDate'];

	$results = dbPrepareExecute($pdo, 'SELECT InternalName, TaxGroup FROM Customer WHERE Number=?', array($input['Customer']));
	$ui->addLabelValueLink('Customer:',$input['Customer'].' '.$results[0]['InternalName']);
	$hiddenData['Customer'] = $input['Customer'];

	$ui->addLabelValueLink("Customer reference:",$input['CustomerReference']);
	$hiddenData['CustomerReference'] = $input['CustomerReference'];

	$documentAmountNet = 0;
	$documentAmountTax = 0;

	$i = 0;
	foreach ($input['Row'] as $row){
		if (($input['Row'][$i]['ArticleID'] != '')&&($input['Row'][$i]['Quantity'] != '')&&($input['Row'][$i]['Price'] != '')){
			$iPlusOne = $i + 1;
			$ui->addLabelHeader('Row '.$iPlusOne);

			$results2 = dbPrepareExecute($pdo, 'SELECT Description, TaxGroup FROM Article WHERE Number=?', array($input['Row'][$i]['ArticleID']));

			$tax = dbPrepareExecute($pdo, 'SELECT TaxPercent FROM GeneralLedgerAccountDeterminationInvoiceRow WHERE Type=? AND TaxGroupCustomerOrVendor=? AND TaxGroupArticleOrAccount=? AND FromDate<=? AND ToDate>=?', array('Sell', $results[0]['TaxGroup'], $results2[0]['TaxGroup'], $input['BookingDate'], $input['BookingDate']));

			if (count($tax) != 1){
				throw new Exception('No tax rules created that match your booking. Please contact the support.');
			}

			$input['Row'][$i]['Quantity'] = str_replace(',', '.', $input['Row'][$i]['Quantity']);
			$input['Row'][$i]['Price'] = str_replace(',', '.', $input['Row'][$i]['Price']);

			$ui->addLabelValueLink('Article:', $input['Row'][$i]['ArticleID'].' '.$results2[0]['Description']);
			$ui->addLabelValueLink('Quantity:', decimalFormat($input['Row'][$i]['Quantity']));
			$ui->addLabelValueLink('Price each:', decimalFormat($input['Row'][$i]['Price']));
			$ui->addLabelValueLink('Price row:', decimalFormat(round($input['Row'][$i]['Quantity']*$input['Row'][$i]['Price'], 2)));
			$hiddenData['Row'][$i]['ArticleID'] = $input['Row'][$i]['ArticleID'];
			$hiddenData['Row'][$i]['Quantity'] = $input['Row'][$i]['Quantity'];
			$hiddenData['Row'][$i]['Price'] = $input['Row'][$i]['Price'];

			$rowAmountNet = round($input['Row'][$i]['Quantity']*$input['Row'][$i]['Price'], 2);
			$documentAmountNet += $rowAmountNet;
			$documentAmountTax += round($rowAmountNet * $tax[0]['TaxPercent'],2);
		}
		$i++;
	}

	$documentAmountGross = $documentAmountNet + $documentAmountTax;

	$ui->addLabelHeader('');

	$ui->addLabelValueLink("Net amount:", decimalFormat($documentAmountNet));
	$ui->addLabelValueLink("Tax:", decimalFormat($documentAmountTax));
	$ui->addLabelValueLink("Gross amount:", decimalFormat($documentAmountGross));

	$hiddenData['DocumentAmountNet'] = $documentAmountNet;
	$hiddenData['DocumentAmountTax'] = $documentAmountTax;
	$hiddenData['DocumentAmountGross'] = $documentAmountGross;

	$ui->setHiddenData($hiddenData);

	echo $ui->getObjectAsJSONString();
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'The following error occurred: ' . $e->getMessage();

	header('Content-type: application/json');
	echo json_encode($response,JSON_PRETTY_PRINT);
}
?>
