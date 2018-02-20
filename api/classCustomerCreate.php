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

class CustomerCreate
{
	private $internalName = NULL;
	private $email = NULL;
	private $emailInvoice = NULL;
	private $taxGroup = NULL;
	private $taxNumber = NULL;
	private $paymentTerms = NULL;
	private $billToAddressLine1 = NULL;
	private $billToAddressLine2 = NULL;
	private $billToAddressLine3 = NULL;
	private $billToAddressLine4 = NULL;
	private $billToAddressCity = NULL;
	private $billToAddressStateOrProvince = NULL;
	private $billToAddressZipOrPostalCode = NULL;
	private $billToAddressCountry = NULL;
	private $shipToAddressLine1 = NULL;
	private $shipToAddressLine2 = NULL;
	private $shipToAddressLine3 = NULL;
	private $shipToAddressLine4 = NULL;
	private $shipToAddressCity = NULL;
	private $shipToAddressStateOrProvince = NULL;
	private $shipToAddressZipOrPostalCode = NULL;
	private $shipToAddressCountry = NULL;

	private $customerNumber = NULL;

	public function setInternalName($internalName)
	{
		$this->internalName = $internalName;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function setEmailInvoice($emailInvoice)
	{
		$this->emailInvoice = $emailInvoice;
	}

	public function setTaxGroup($taxGroup)
	{
		$this->taxGroup = $taxGroup;
	}

	public function setTaxNumber($taxNumber)
	{
		$this->taxNumber = $taxNumber;
	}

	public function setPaymentTerms($paymentTerms)
	{
		$this->paymentTerms = $paymentTerms;
	}

	public function setBillToAddressLine1($billToAddressLine1)
	{
		$this->billToAddressLine1 = $billToAddressLine1;
	}

	public function setBillToAddressLine2($billToAddressLine2)
	{
		$this->billToAddressLine2 = $billToAddressLine2;
	}

	public function setBillToAddressLine3($billToAddressLine3)
	{
		$this->billToAddressLine3 = $billToAddressLine3;
	}

	public function setBillToAddressLine4($billToAddressLine4)
	{
		$this->billToAddressLine4 = $billToAddressLine4;
	}

	public function setBillToAddressCity($billToAddressCity)
	{
		$this->billToAddressCity = $billToAddressCity;
	}

	public function setBillToAddressStateOrProvince($billToAddressStateOrProvince)
	{
		$this->billToAddressStateOrProvince = $billToAddressStateOrProvince;
	}

	public function setBillToAddressZipOrPostalCode($billToAddressZipOrPostalCode)
	{
		$this->billToAddressZipOrPostalCode = $billToAddressZipOrPostalCode;
	}

	public function setBillToAddressCountry($billToAddressCountry)
	{
		$this->billToAddressCountry = $billToAddressCountry;
	}

	public function setShipToAddressLine1($shipToAddressLine1)
	{
		$this->shipToAddressLine1 = $shipToAddressLine1;
	}

	public function setShipToAddressLine2($shipToAddressLine2)
	{
		$this->shipToAddressLine2 = $shipToAddressLine2;
	}

	public function setShipToAddressLine3($shipToAddressLine3)
	{
		$this->shipToAddressLine3 = $shipToAddressLine3;
	}

	public function setShipToAddressLine4($shipToAddressLine4)
	{
		$this->shipToAddressLine4 = $shipToAddressLine4;
	}

	public function setShipToAddressCity($shipToAddressCity)
	{
		$this->shipToAddressCity = $shipToAddressCity;
	}

	public function setShipToAddressStateOrProvince($shipToAddressStateOrProvince)
	{
		$this->shipToAddressStateOrProvince = $shipToAddressStateOrProvince;
	}

	public function setShipToAddressZipOrPostalCode($shipToAddressZipOrPostalCode)
	{
		$this->shipToAddressZipOrPostalCode = $shipToAddressZipOrPostalCode;
	}

	public function setShipToAddressCountry($shipToAddressCountry)
	{
		$this->shipToAddressCountry = $shipToAddressCountry;
	}

	public function create($pdo)
	{
		$this->customerNumber = nextDocumentNumber($pdo, 'Customer');

		dbPrepareExecute($pdo, 'INSERT INTO Customer (Active, Number, InternalName, Email, EmailInvoice, TaxGroup, TaxNumber, PaymentTerms, BillToAddressLine1, BillToAddressLine2, BillToAddressLine3, BillToAddressLine4, BillToAddressCity, BillToAddressStateOrProvince, BillToAddressZipOrPostalCode, BillToAddressCountry, ShipToAddressLine1, ShipToAddressLine2, ShipToAddressLine3, ShipToAddressLine4, ShipToAddressCity, ShipToAddressStateOrProvince, ShipToAddressZipOrPostalCode, ShipToAddressCountry) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array(1, $this->customerNumber, $this->internalName, $this->email, $this->emailInvoice, $this->taxGroup, $this->taxNumber, $this->paymentTerms, $this->billToAddressLine1, $this->billToAddressLine2, $this->billToAddressLine3, $this->billToAddressLine4, $this->billToAddressCity, $this->billToAddressStateOrProvince, $this->billToAddressZipOrPostalCode, $this->billToAddressCountry, $this->shipToAddressLine1, $this->shipToAddressLine2, $this->shipToAddressLine3, $this->shipToAddressLine4, $this->shipToAddressCity, $this->shipToAddressStateOrProvince, $this->shipToAddressZipOrPostalCode, $this->shipToAddressCountry));

		auditTrailLog($pdo, 'Customer', $pdo->lastInsertId(), 'INSERT');
	}

	public function getCustomerNumber()
	{
		return $this->customerNumber;
	}
}
?>
