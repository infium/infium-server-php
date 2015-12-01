<?php
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