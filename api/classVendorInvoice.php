<?php
require_once('classGeneralLedgerAccountBooking.php');

class VendorInvoice
{
	private $date = NULL;
	private $paymentReference = NULL;
	
	private $debug = NULL;
	
	private $vendorReceiptDocumentNumber = NULL;
	private $row = NULL;
	private $documentNumber = NULL;
	private $vendorInvoiceId = NULL;
	
	private $lowestVendorReceiptId = NULL;

	private $reversalDocId = NULL;
	private $bookingDate = NULL;
	
	private $vendorNumber = NULL;
	private $bankAccount = NULL;
	
	public function setBookingDate($newBookingDate)
	{
		$this->bookingDate = $newBookingDate;
	}
	
	public function setVendorNumber($vendorNumber)
	{
		$this->vendorNumber = $vendorNumber;
	}

	public function setBankAccount($bankAccount)
	{
		$this->bankAccount = $bankAccount;
	}
	
	
	public function getDebug()
	{
		return $this->debug;
	}
	
	public function setReversalDocId($id)
	{
		$this->reversalDocId = $id;
	}
	
	public function setDate($newDate)
	{
		$this->date = $newDate;
	}

	public function setPaymentReference($newPaymentReference)
	{
		$this->paymentReference = $newPaymentReference;
	}
		
	public function addRow($account, $tax, $amount)
	{
		validateNumber($amount);
		
		$newRow['Account'] = $account;
		$newRow['Tax'] = $tax;
		$newRow['Amount'] = $amount;
				
		$this->row[] = $newRow;
	}

	public function validateAndWriteToDatabase($pdo)
	{
		$this->documentNumber = nextDocumentNumber($pdo, 'VendorInvoice');
		
		$results3 = dbPrepareExecute($pdo, 'SELECT Id, InternalName, TaxGroup, BillFromAddressLine1, BillFromAddressLine2, BillFromAddressLine3, BillFromAddressLine4, BillFromAddressCity, BillFromAddressStateOrProvince, BillFromAddressZipOrPostalCode, BillFromAddressCountry FROM Vendor WHERE Number=?', array($this->vendorNumber));
		
		$dueDate = date('Y-m-d',mktime(0,0,0,substr($this->date,5,2),substr($this->date,8,2),substr($this->date,0,4))+3600*24*30);
		
		dbPrepareExecute($pdo, 'INSERT INTO VendorInvoice (Number, BookingDate, PartnerDate, DueDate, VendorId, InternalName, BankAccount, PaymentReference, PaymentTerms, BillFromAddressLine1, BillFromAddressLine2, BillFromAddressLine3, BillFromAddressLine4, BillFromAddressCity, BillFromAddressStateOrProvince, BillFromAddressZipOrPostalCode, BillFromAddressCountry) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array($this->documentNumber, $this->bookingDate, $this->date, $dueDate, $results3[0]['Id'], $results3[0]['InternalName'], $this->bankAccount, $this->paymentReference, '30DAYS', $results3[0]['BillFromAddressLine1'], $results3[0]['BillFromAddressLine2'], $results3[0]['BillFromAddressLine3'], $results3[0]['BillFromAddressLine4'], $results3[0]['BillFromAddressCity'], $results3[0]['BillFromAddressStateOrProvince'], $results3[0]['BillFromAddressZipOrPostalCode'], $results3[0]['BillFromAddressCountry']));
		
		$this->vendorInvoiceId = $pdo->lastInsertId();
		
		$documentCurrencyAmountNet = 0;
		$documentCurrencyAmountTax = 0;
		$documentCurrencyAmountGross = 0;
		
		$vendorTaxGroup = $results3[0]['TaxGroup'];
		
		foreach ($this->row as $documentRow){
				
				$taxInclusion = substr($documentRow['Tax'], 0, 8);
				$taxGroup = substr($documentRow['Tax'], 9);
				
				$accountDetermination = dbPrepareExecute($pdo, 'SELECT AccountArticleTaxCode, TaxPercent, AccountTaxOutput, AccountTaxOutputTaxCode, AccountTaxInput, AccountTaxInputTaxCode FROM GeneralLedgerAccountDeterminationInvoiceRow WHERE Type=? AND TaxGroupCustomerOrVendor=? AND TaxGroupArticleOrAccount=? AND FromDate<=? AND ToDate>=?', array('Buy', $results3[0]['TaxGroup'], $taxGroup, $this->bookingDate, $this->bookingDate));
				
				if (count($accountDetermination) != 1){
					throw new Exception('No tax rules created that match your booking. Please contact the support.');
				}
				
				if ($accountDetermination[0]['AccountTaxOutput'] == ''){
					
					if ($taxInclusion == 'EXCLUDED'){
						$rowDocumentCurrencyAmountNet = $documentRow['Amount'];
						$rowDocumentCurrencyAmountTax = round($documentRow['Amount'] * $accountDetermination[0]['TaxPercent'], 2);
						$rowDocumentCurrencyAmountGross = $rowDocumentCurrencyAmountNet + $rowDocumentCurrencyAmountTax;
					}

					if ($taxInclusion == 'INCLUDED'){
						$rowDocumentCurrencyAmountGross = $documentRow['Amount'];
						$rowDocumentCurrencyAmountNet = round($documentRow['Amount'] / (1 + $accountDetermination[0]['TaxPercent']), 2);
						$rowDocumentCurrencyAmountTax = $rowDocumentCurrencyAmountGross - $rowDocumentCurrencyAmountNet;

					}
					
					if (isset($bookingRow[$documentRow['Account']][$accountDetermination[0]['AccountArticleTaxCode']])){
						$bookingRow[$documentRow['Account']][$accountDetermination[0]['AccountArticleTaxCode']] += $rowDocumentCurrencyAmountNet;
					}else{
						$bookingRow[$documentRow['Account']][$accountDetermination[0]['AccountArticleTaxCode']] = $rowDocumentCurrencyAmountNet;
					}
					
					if (isset($bookingRow[$accountDetermination[0]['AccountTaxInput']][$accountDetermination[0]['AccountTaxInputTaxCode']])){
						$bookingRow[$accountDetermination[0]['AccountTaxInput']][$accountDetermination[0]['AccountTaxInputTaxCode']] += $rowDocumentCurrencyAmountTax;
					}else{
						$bookingRow[$accountDetermination[0]['AccountTaxInput']][$accountDetermination[0]['AccountTaxInputTaxCode']] = $rowDocumentCurrencyAmountTax;
					}
					
				}else{
					
					$rowDocumentCurrencyAmountNet = $documentRow['Amount'];
					$rowDocumentCurrencyAmountTax = 0;
					$rowDocumentCurrencyAmountGross = $documentRow['Amount'];
					
					if (isset($bookingRow[$documentRow['Account']][$accountDetermination[0]['AccountArticleTaxCode']])){
						$bookingRow[$documentRow['Account']][$accountDetermination[0]['AccountArticleTaxCode']] += $rowDocumentCurrencyAmountNet;
					}else{
						$bookingRow[$documentRow['Account']][$accountDetermination[0]['AccountArticleTaxCode']] = $rowDocumentCurrencyAmountNet;
					}
					
					if (isset($bookingRow[$accountDetermination[0]['AccountTaxInput']][$accountDetermination[0]['AccountTaxInputTaxCode']])){
						$bookingRow[$accountDetermination[0]['AccountTaxInput']][$accountDetermination[0]['AccountTaxInputTaxCode']] += round($rowDocumentCurrencyAmountNet * $accountDetermination[0]['TaxPercent'], 2);
					}else{
						$bookingRow[$accountDetermination[0]['AccountTaxInput']][$accountDetermination[0]['AccountTaxInputTaxCode']] = round($rowDocumentCurrencyAmountNet * $accountDetermination[0]['TaxPercent'], 2);
					}
					
					if (isset($bookingRow[$accountDetermination[0]['AccountTaxOutput']][$accountDetermination[0]['AccountTaxOutputTaxCode']])){
						$bookingRow[$accountDetermination[0]['AccountTaxOutput']][$accountDetermination[0]['AccountTaxOutputTaxCode']] += round($rowDocumentCurrencyAmountNet * $accountDetermination[0]['TaxPercent'], 2)*-1;
					}else{
						$bookingRow[$accountDetermination[0]['AccountTaxOutput']][$accountDetermination[0]['AccountTaxOutputTaxCode']] = round($rowDocumentCurrencyAmountNet * $accountDetermination[0]['TaxPercent'], 2)*-1;
					}
					
				}
				
				$documentCurrencyAmountNet += $rowDocumentCurrencyAmountNet;
				$documentCurrencyAmountTax += $rowDocumentCurrencyAmountTax;
				$documentCurrencyAmountGross += $rowDocumentCurrencyAmountGross;
			
				$stmt10 = $pdo->prepare('INSERT INTO VendorInvoiceRow (ParentId, Number, PreviousDocumentType, PreviousRowId, Account, ProfitCenter, Description, Amount, TaxInclusionGroup, TaxPercent, AmountNet, AmountTax, AmountGross) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
				$stmt10->execute(array($this->vendorInvoiceId, $this->documentNumber, NULL, NULL, $documentRow['Account'], NULL, NULL, $documentRow['Amount'], $documentRow['Tax'], $accountDetermination[0]['TaxPercent'], $rowDocumentCurrencyAmountNet, $rowDocumentCurrencyAmountTax, $rowDocumentCurrencyAmountGross));
				
				auditTrailLog($pdo, 'VendorInvoiceRow', $pdo->lastInsertId(), 'INSERT');
		}
		
		$reversalId = NULL;
		if ($this->reversalDocId != NULL){
			$previousReversal = dbPrepareExecute($pdo, 'SELECT ReversalId FROM VendorInvoice WHERE Id=?', array($this->reversalDocId));
			if ($previousReversal[0]['ReversalId'] != NULL){
				dbPrepareExecute($pdo, 'UPDATE VendorInvoice SET AmountGrossRemaining=AmountGrossRemaining+? WHERE Id=?', array($documentCurrencyAmountGross, $previousReversal[0]['ReversalId']));
				
				auditTrailLog($pdo, 'VendorInvoice', $previousReversal[0]['ReversalId'], 'UPDATE');
				
				$reversalId = $previousReversal[0]['ReversalId'];
			}else{
				dbPrepareExecute($pdo, 'UPDATE VendorInvoice SET AmountGrossRemaining=AmountGrossRemaining+? WHERE Id=?', array($documentCurrencyAmountGross, $this->reversalDocId));
				
				auditTrailLog($pdo, 'VendorInvoice', $this->reversalDocId, 'UPDATE');
				
				$reversalId = $this->reversalDocId;
			}
			$amountRemaining = 0;
		}else{
			$amountRemaining = $documentCurrencyAmountGross;
		}		
		
		$stmt11 = $pdo->prepare('UPDATE VendorInvoice SET ReversalId=?, AmountNet=?, AmountTax=?, AmountGross=?, AmountGrossRemaining=? WHERE Id=?');
		$stmt11->execute(array($reversalId, $documentCurrencyAmountNet, $documentCurrencyAmountTax, $documentCurrencyAmountGross, $amountRemaining, $this->vendorInvoiceId));
		
		auditTrailLog($pdo, 'VendorInvoice', $this->vendorInvoiceId, 'INSERT');
		
		$booking = new GeneralLedgerAccountBooking();

		$booking->setDate($this->bookingDate);
		$booking->setText('Vendor invoice #' . $this->documentNumber);
		$booking->setDocumentType('VendorInvoice');
		$booking->setDocumentTypeNumber($this->documentNumber);
		
		foreach ($bookingRow as $key1 => $value1){
			foreach ($value1 as $key2 => $value2){
				if ($bookingRow[$key1][$key2] > 0){
					$booking->addRowAdvanced($key1, '', '', '', $key2, $value2, 0, 'VendorInvoice', $this->documentNumber, '');
				}
				if ($bookingRow[$key1][$key2] < 0){
					$booking->addRowAdvanced($key1, '', '', '', $key2, 0, $value2*-1, 'VendorInvoice', $this->documentNumber, '');
				}
			}
		}
				
		if ($documentCurrencyAmountGross > 0){
			$booking->addRowAdvanced('2441', $results3[0]['Id'], '', '', '', 0, $documentCurrencyAmountGross, 'VendorInvoice', $this->documentNumber, $this->paymentReference);
		}
		if ($documentCurrencyAmountGross < 0){
			$booking->addRowAdvanced('2441', $results3[0]['Id'], '', '', '', $documentCurrencyAmountGross*-1, 0, 'VendorInvoice', $this->documentNumber, $this->paymentReference);
		}
		
		if ($booking->countRows() > 0){
			$booking->validateAndWriteToDatabase($pdo);				
		}
		
	}

	public function getDocumentNumber()
	{
		return $this->documentNumber;
	}
}
?>