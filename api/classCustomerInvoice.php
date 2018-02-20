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

require_once('classGeneralLedgerAccountBooking.php');
require_once('config.php');

class CustomerInvoice
{
	private $bookingDate = NULL;
	private $customerNumber = NULL;
	private $customerReference = NULL;

	private $row = NULL;

	private $documentNumber = NULL;

	private $taxNumber = NULL;

	public function setBookingDate($bookingDate)
	{
		$this->bookingDate = $bookingDate;
	}

	public function setCustomerNumber($customerNumber)
	{
		$this->customerNumber = $customerNumber;
	}

	public function setCustomerReference($customerReference)
	{
		$this->customerReference = $customerReference;
	}

	public function setTaxNumber($taxNumber)
	{
		$this->taxNumber = $taxNumber;
	}


	public function addRow($pdo, $articleNumber, $quantity, $price, $reversalRowId = NULL)
	{
		validateArticleNumber($pdo, $articleNumber);
		validateNumber($quantity);
		validateNumber($price);

		$newRow['articleNumber'] = $articleNumber;
		$newRow['quantity'] = $quantity;
		$newRow['price'] = $price;

		$newRow['reversalRowId'] = $reversalRowId;

		$this->row[] = $newRow;
	}

	public function validateAndWriteToDatabase($pdo)
	{
		validateDate($this->bookingDate);
		validateCustomerNumber($pdo, $this->customerNumber);

		$customerData = dbPrepareExecute($pdo, 'SELECT Id, TaxGroup, PaymentTerms, BillToAddressLine1, BillToAddressLine2, BillToAddressLine3, BillToAddressLine4, BillToAddressCity, BillToAddressStateOrProvince, BillToAddressZipOrPostalCode, BillToAddressCountry, ShipToAddressLine1, ShipToAddressLine2, ShipToAddressLine3, ShipToAddressLine4, ShipToAddressCity, ShipToAddressStateOrProvince, ShipToAddressZipOrPostalCode, ShipToAddressCountry FROM Customer WHERE Number=?', array($this->customerNumber));

		$this->documentNumber = nextDocumentNumber($pdo, 'CustomerInvoice');

		if ($customerData[0]['PaymentTerms'] == '30DAYS'){
			$dueDate = date('Y-m-d',mktime(0,0,0,substr($this->bookingDate, 5, 2),substr($this->bookingDate, 8, 2),substr($this->bookingDate, 0, 4))+3600*24*30);
		}else{
			$dueDate = $this->bookingDate;
		}

		dbPrepareExecute($pdo, 'INSERT INTO CustomerInvoice (Number, BookingDate, DateDue, CustomerId, CustomerReference, PaymentReference, PaymentTerms, TaxNumber, BillToAddressLine1, BillToAddressLine2, BillToAddressLine3, BillToAddressLine4, BillToAddressCity, BillToAddressStateOrProvince, BillToAddressZipOrPostalCode, BillToAddressCountry, ShipToAddressLine1, ShipToAddressLine2, ShipToAddressLine3, ShipToAddressLine4, ShipToAddressCity, ShipToAddressStateOrProvince, ShipToAddressZipOrPostalCode, ShipToAddressCountry) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array($this->documentNumber, $this->bookingDate, $dueDate, $customerData[0]['Id'], $this->customerReference, $this->documentNumber, $customerData[0]['PaymentTerms'], $this->taxNumber, $customerData[0]['BillToAddressLine1'], $customerData[0]['BillToAddressLine2'], $customerData[0]['BillToAddressLine3'], $customerData[0]['BillToAddressLine4'], $customerData[0]['BillToAddressCity'], $customerData[0]['BillToAddressStateOrProvince'], $customerData[0]['BillToAddressZipOrPostalCode'], $customerData[0]['BillToAddressCountry'], $customerData[0]['ShipToAddressLine1'], $customerData[0]['ShipToAddressLine2'], $customerData[0]['ShipToAddressLine3'], $customerData[0]['ShipToAddressLine4'], $customerData[0]['ShipToAddressCity'], $customerData[0]['ShipToAddressStateOrProvince'], $customerData[0]['ShipToAddressZipOrPostalCode'], $customerData[0]['ShipToAddressCountry']));

		$customerInvoiceId = $pdo->lastInsertId();

		$documentCurrencyAmountNet = 0;
		$documentCurrencyAmountTax = 0;
		$documentCurrencyAmountGross = 0;

		foreach ($this->row as $row){

			$articleData = dbPrepareExecute($pdo, 'SELECT Id, TaxGroup FROM Article WHERE Number=?', array($row['articleNumber']));

			$accountDetermination = dbPrepareExecute($pdo, 'SELECT AccountArticle, AccountArticleTaxCode, TaxPercent, AccountTaxOutput, AccountTaxOutputTaxCode FROM GeneralLedgerAccountDeterminationInvoiceRow WHERE Type=? AND TaxGroupCustomerOrVendor=? AND TaxGroupArticleOrAccount=? AND FromDate<=? AND ToDate>=?', array('Sell', $customerData[0]['TaxGroup'], $articleData[0]['TaxGroup'], $this->bookingDate, $this->bookingDate));

			if (count($accountDetermination) != 1){
				throw new Exception('No tax rules created that match your booking. Please contact the support.');
			}

			$rowDocumentCurrencyAmountNet = round($row['price'] * $row['quantity'], 2);
			$rowDocumentCurrencyAmountTax = round($rowDocumentCurrencyAmountNet * $accountDetermination[0]['TaxPercent'], 2);
			$rowDocumentCurrencyAmountGross = $rowDocumentCurrencyAmountNet + $rowDocumentCurrencyAmountTax;

			if ($rowDocumentCurrencyAmountNet != 0){
				if (isset($bookingRow[$accountDetermination[0]['AccountArticle']][$accountDetermination[0]['AccountArticleTaxCode']])){
					$bookingRow[$accountDetermination[0]['AccountArticle']][$accountDetermination[0]['AccountArticleTaxCode']] += $rowDocumentCurrencyAmountNet;
				}else{
					$bookingRow[$accountDetermination[0]['AccountArticle']][$accountDetermination[0]['AccountArticleTaxCode']] = $rowDocumentCurrencyAmountNet;
				}
			}

			if ($rowDocumentCurrencyAmountTax != 0){
				if (isset($bookingRow[$accountDetermination[0]['AccountTaxOutput']][$accountDetermination[0]['AccountTaxOutputTaxCode']])){
					$bookingRow[$accountDetermination[0]['AccountTaxOutput']][$accountDetermination[0]['AccountTaxOutputTaxCode']] += $rowDocumentCurrencyAmountTax;
				}else{
					$bookingRow[$accountDetermination[0]['AccountTaxOutput']][$accountDetermination[0]['AccountTaxOutputTaxCode']] = $rowDocumentCurrencyAmountTax;
				}
			}

			$documentCurrencyAmountNet += $rowDocumentCurrencyAmountNet;
			$documentCurrencyAmountTax += $rowDocumentCurrencyAmountTax;
			$documentCurrencyAmountGross += $rowDocumentCurrencyAmountGross;

			dbPrepareExecute($pdo, 'INSERT INTO CustomerInvoiceRow (ParentId, Number, ArticleId, Quantity, Price, TaxPercent, TaxCode, AmountNet, AmountTax, AmountGross) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array($customerInvoiceId, $this->documentNumber, $articleData[0]['Id'], $row['quantity'], $row['price'], $accountDetermination[0]['TaxPercent'], $accountDetermination[0]['AccountArticleTaxCode'], $rowDocumentCurrencyAmountNet, $rowDocumentCurrencyAmountTax, $rowDocumentCurrencyAmountGross));

			auditTrailLog($pdo, 'CustomerInvoiceRow', $pdo->lastInsertId(), 'INSERT');



			}

			dbPrepareExecute($pdo, 'UPDATE CustomerInvoice SET AmountNet=?, AmountTax=?, AmountGross=? WHERE Id=?', array($documentCurrencyAmountNet, $documentCurrencyAmountTax, $documentCurrencyAmountGross, $customerInvoiceId));

			auditTrailLog($pdo, 'CustomerInvoice', $customerInvoiceId, 'INSERT');

			$booking = new GeneralLedgerAccountBooking();

			$booking->setDate($this->bookingDate);
			$booking->setText('Customer invoice #' . $this->documentNumber);
			$booking->setDocumentType('CustomerInvoice');
			$booking->setDocumentTypeNumber($this->documentNumber);

			foreach ($bookingRow as $key1 => $value1){
				foreach ($value1 as $key2 => $value2){
					if ($bookingRow[$key1][$key2] > 0){
						$booking->addRowAdvanced($key1, '', '', '', $key2, 0, $value2, 'CustomerInvoice', $this->documentNumber, '', $this->taxNumber);
					}
					if ($bookingRow[$key1][$key2] < 0){
						$booking->addRowAdvanced($key1, '', '', '', $key2, $value2*-1, 0, 'CustomerInvoice', $this->documentNumber, '', $this->taxNumber);
					}
				}
			}

			if ($documentCurrencyAmountGross > 0){
				$booking->addRowAdvanced('1510', $customerData[0]['Id'], '', '', '', $documentCurrencyAmountGross, 0, 'CustomerInvoice', $this->documentNumber, $this->documentNumber);
			}
			if ($documentCurrencyAmountGross < 0){
				$booking->addRowAdvanced('1510', $customerData[0]['Id'], '', '', '', 0, $documentCurrencyAmountGross*-1, 'CustomerInvoice', $this->documentNumber, $this->documentNumber);
			}

			if ($booking->countRows() > 0){
				$booking->validateAndWriteToDatabase($pdo);
			}
	}

	public function getDocumentNumber()
	{
		return $this->documentNumber;
	}

	public function sendMail($pdo){
		global $emailFrom;
			try {
				$customerResult = dbPrepareExecute($pdo, 'SELECT CustomerId FROM CustomerInvoice WHERE Number=?', array($this->documentNumber));

				$emailResult = dbPrepareExecute($pdo, 'SELECT Email, EmailInvoice FROM Customer WHERE Id=?', array($customerResult[0]['CustomerId']));

				if ($emailResult[0]['EmailInvoice'] == True){
					require('functionRenderCustomerInvoice.php');

					$invoiceHTML = renderCustomerInvoice($this->documentNumber);

					emailSend($emailFrom, $emailResult[0]['Email'], 'Invoice #'.$this->documentNumber, 'This e-mail has been HTML formatted. Please use an e-mail client that supports HTML.', $invoiceHTML, 'Invoice_'.$this->documentNumber.'.html');
				}
			} catch (Exception $e) {
				syslog(LOG_ERROR, 'Error when trying to send email for customer invoice #'.$this->documentNumber);
			}
	}
}
?>
