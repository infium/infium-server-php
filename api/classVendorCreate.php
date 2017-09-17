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

require_once('config.php');

class VendorCreate
{
	private $internalName = NULL;
	private $bankAccount = NULL;
	private $email = NULL;
	private $taxGroup = NULL;
	private $paymentTerms = NULL;
	private $billFromAddressLine1 = NULL;
	private $billFromAddressLine2 = NULL;
	private $billFromAddressLine3 = NULL;
	private $billFromAddressLine4 = NULL;
	private $billFromAddressCity = NULL;
	private $billFromAddressStateOrProvince = NULL;
	private $billFromAddressZipOrPostalCode = NULL;
	private $billFromAddressCountry = NULL;
	
	private $vendorNumber = NULL;
	
	public function setInternalName($internalName)
	{
		$this->internalName = $internalName;
	}

	public function setBankAccount($bankAccount)
	{
		$this->bankAccount = $bankAccount;
	}
	
	public function setEmail($email)
	{
		$this->email = $email;
	}
	
	public function setTaxGroup($taxGroup)
	{
		$this->taxGroup = $taxGroup;
	}
	
	public function setPaymentTerms($paymentTerms)
	{
		$this->paymentTerms = $paymentTerms;
	}
	
	public function setBillFromAddressLine1($billFromAddressLine1)
	{
		$this->billFromAddressLine1 = $billFromAddressLine1;
	}

	public function setBillFromAddressLine2($billFromAddressLine2)
	{
		$this->billFromAddressLine2 = $billFromAddressLine2;
	}

	public function setBillFromAddressLine3($billFromAddressLine3)
	{
		$this->billFromAddressLine3 = $billFromAddressLine3;
	}

	public function setBillFromAddressLine4($billFromAddressLine4)
	{
		$this->billFromAddressLine4 = $billFromAddressLine4;
	}
	
	public function setBillFromAddressCity($billFromAddressCity)
	{
		$this->billFromAddressCity = $billFromAddressCity;
	}
	
	public function setBillFromAddressStateOrProvince($billFromAddressStateOrProvince)
	{
		$this->billFromAddressStateOrProvince = $billFromAddressStateOrProvince;
	}
	
	public function setBillFromAddressZipOrPostalCode($billFromAddressZipOrPostalCode)
	{
		$this->billFromAddressZipOrPostalCode = $billFromAddressZipOrPostalCode;
	}
	
	public function setBillFromAddressCountry($billFromAddressCountry)
	{
		$this->billFromAddressCountry = $billFromAddressCountry;
	}
	
	public function create($pdo)
	{
		$this->vendorNumber = nextDocumentNumber($pdo, 'Vendor');
		
		dbPrepareExecute($pdo, 'INSERT INTO Vendor (Active, Number, InternalName, BankAccount, Email, TaxGroup, PaymentTerms, BillFromAddressLine1, BillFromAddressLine2, BillFromAddressLine3, BillFromAddressLine4, BillFromAddressCity, BillFromAddressStateOrProvince, BillFromAddressZipOrPostalCode, BillFromAddressCountry) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array(1, $this->vendorNumber, $this->internalName, $this->bankAccount, $this->email, $this->taxGroup, $this->paymentTerms, $this->billFromAddressLine1, $this->billFromAddressLine2, $this->billFromAddressLine3, $this->billFromAddressLine4, $this->billFromAddressCity, $this->billFromAddressStateOrProvince, $this->billFromAddressZipOrPostalCode, $this->billFromAddressCountry));
		
		auditTrailLog($pdo, 'Vendor', $pdo->lastInsertId(), 'INSERT');
	}
	
	public function getVendorNumber()
	{
		return $this->vendorNumber;
	}
}
?>