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

require('config.php');
require('classUserInterface.php');

checkUserAccess('VendorInvoiceCreate');

try{
	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();

	validateDate($input['BookingDate']);
	validateDate($input['InvoiceDate']);
	validateVendorNumber($pdo, $input['Vendor']);

	$a = 0;
	foreach ($input['Row'] as $row){
		if (($row['Account'] != '')||($row['Amount'] != '')){
			validateAccountNumber($pdo, substr($input['BookingDate'], 0, 4), $row['Account']);
			validateNumber($row['Amount']);
			$a++;
		}
	}

	if ($a == 0){
		throw new Exception('Document contains no rows.');
	}

	$ui = new UserInterface();

	$results = dbPrepareExecute($pdo, 'SELECT InternalName, BankAccount, TaxGroup FROM Vendor WHERE Number=?', array($input['Vendor']));

	$ui->setTitle('Invoice');
	$ui->setWindow('Same');
	$ui->setMethod('POST');
	$ui->setUrl($baseUrl.'vendorInvoiceCreateProcess.php');
	$ui->setButtonLabel('Add');

	$ui->addLabelValue("Booking date:",$input['BookingDate']);
	$hiddenData['BookingDate'] = $input['BookingDate'];

	$ui->addLabelValue("Invoice date:",$input['InvoiceDate']);
	$hiddenData['InvoiceDate'] = $input['InvoiceDate'];

	$ui->addLabelValue('Vendor:',$input['Vendor'].' '.$results[0]['InternalName']);
	$hiddenData['Vendor'] = $input['Vendor'];

	$ui->addLabelValue('Bank account:',$results[0]['BankAccount']);
	$hiddenData['BankAccount'] = $results[0]['BankAccount'];

	$ui->addLabelValue("Vendor reference:",$input['VendorReference']);
	$hiddenData['VendorReference'] = $input['VendorReference'];

	$documentAmountNet = 0;
	$documentAmountTax = 0;
	$documentAmountGross = 0;

	$i = 0;
	foreach ($input['Row'] as $row){
		$iPlusOne = $i + 1;
		if (($input['Row'][$i]['Account'] != '')&&($input['Row'][$i]['Amount'] != '')){

			$taxInclusion = substr($input['Row'][$i]['Tax'], 0, 8);
			$taxGroup = substr($input['Row'][$i]['Tax'], 9);

			$ui->addLabelHeader('Row '.$iPlusOne);

			$results2 = dbPrepareExecute($pdo, 'SELECT Description FROM GeneralLedgerAccount WHERE (AccountNumber=? AND Year=?)', array($input['Row'][$i]['Account'],substr($input['BookingDate'], 0, 4)));

			$tax = dbPrepareExecute($pdo, 'SELECT TaxPercent, AccountTaxOutput, AccountTaxInput FROM GeneralLedgerAccountDeterminationInvoiceRow WHERE Type=? AND TaxGroupCustomerOrVendor=? AND TaxGroupArticleOrAccount=? AND FromDate<=? AND ToDate>=?', array('Buy', $results[0]['TaxGroup'], $taxGroup, $input['BookingDate'], $input['BookingDate']));

			if (count($tax) != 1){
				throw new Exception('No tax rules created that match your booking. Please contact the support.');
			}

			$ui->addLabelValue('Account:', $input['Row'][$i]['Account'].' '.$results2[0]['Description']);
			$ui->addLabelValue('Amount:', decimalFormat($input['Row'][$i]['Amount']));
			$hiddenData['Row'][$i]['Account'] = $input['Row'][$i]['Account'];
			$hiddenData['Row'][$i]['Tax'] = $input['Row'][$i]['Tax'];
			$hiddenData['Row'][$i]['Amount'] = $input['Row'][$i]['Amount'];

			if ($tax[0]['AccountTaxOutput'] != ''){
				$documentAmountNet += $input['Row'][$i]['Amount'];
				$documentAmountGross += $input['Row'][$i]['Amount'];
			}else{
				if ($taxInclusion == 'EXCLUDED'){
					$documentAmountNet += $input['Row'][$i]['Amount'];
					$documentAmountTax += round($input['Row'][$i]['Amount'] * $tax[0]['TaxPercent'], 2);
					$documentAmountGross += $input['Row'][$i]['Amount'] + round($input['Row'][$i]['Amount'] * $tax[0]['TaxPercent'], 2);
				}

				if ($taxInclusion == 'INCLUDED'){
					$documentAmountGross += $input['Row'][$i]['Amount'];
					$documentAmountNet += round($input['Row'][$i]['Amount'] / (1 + $tax[0]['TaxPercent']), 2);
					$documentAmountTax += ($input['Row'][$i]['Amount'] - round($input['Row'][$i]['Amount'] / (1 + $tax[0]['TaxPercent']), 2));

				}
			}
		}
		$i++;
	}

	$ui->addLabelHeader('');

	$ui->addLabelValue("Net amount:", decimalFormat($documentAmountNet));
	$ui->addLabelValue("Tax:", decimalFormat($documentAmountTax));
	$ui->addLabelValue("Gross amount:", decimalFormat($documentAmountGross));

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
