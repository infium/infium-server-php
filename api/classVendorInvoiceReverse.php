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

require_once('classVendorInvoice.php');

class VendorInvoiceReverse
{
	private $vendorInvoice = NULL;

	public function reverse($pdo, $bookingDate, $documentNumber)
	{
		
		$previousDoc = dbPrepareExecute($pdo, 'SELECT Id, PartnerDate, VendorId, BankAccount, PaymentReference FROM VendorInvoice WHERE Number=?', array($documentNumber));
		$vendorNumber = dbPrepareExecute($pdo, 'SELECT Number FROM Vendor WHERE Id=?', array($previousDoc[0]['VendorId']));
		
		$previousDocRows = dbPrepareExecute($pdo, 'SELECT Id, PreviousRowId, Account, ProfitCenter, Description, Amount, TaxInclusionGroup, AmountNet, AmountGross FROM VendorInvoiceRow WHERE ParentId=?', array($previousDoc[0]['Id']));
	
		$this->vendorInvoice = new VendorInvoice();
	
		$this->vendorInvoice->setBookingDate($bookingDate);
		$this->vendorInvoice->setDate($previousDoc[0]['PartnerDate']);
		$this->vendorInvoice->setPaymentReference($previousDoc[0]['PaymentReference']);
		$this->vendorInvoice->setReversalDocId($previousDoc[0]['Id']);
		$this->vendorInvoice->setVendorNumber($vendorNumber[0]['Number']);
		$this->vendorInvoice->setBankAccount($previousDoc[0]['BankAccount']);
		
		foreach ($previousDocRows as $row){				
				$taxInclusion = substr($row['TaxInclusionGroup'], 0, 8);
				
				if ($taxInclusion == 'INCLUDED'){
					$this->vendorInvoice->addRow($row['Account'], $row['TaxInclusionGroup'], $row['AmountGross']*-1);					
				}
				
				if ($taxInclusion == 'EXCLUDED'){
					$this->vendorInvoice->addRow($row['Account'], $row['TaxInclusionGroup'], $row['AmountNet']*-1);					
				}
		}
	
		$this->vendorInvoice->validateAndWriteToDatabase($pdo);
		
		return $this->vendorInvoice->getDocumentNumber();
		
	}	
}
?>