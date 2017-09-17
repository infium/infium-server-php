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
require_once('classGeneralLedgerAccountBooking.php');

class CustomerPayment
{
	private $account = NULL;
	private $row = NULL;
	private $documentNumber = NULL;
	private $dateCreatedOurSide = NULL;
	private $dateCreatedPartnerSide = NULL;
	private $amountDocument = 0;
	private $customerPaymentId = NULL;
		
	public function setDateCreatedOurSide($date)
	{
		$this->dateCreatedOurSide = $date;
	}

	public function setDateCreatedPartnerSide($date)
	{
		$this->dateCreatedPartnerSide = $date;
	}
	
	public function setAccount($account)
	{
		$this->account = $account;
	}
	
	
	public function addRow($newAmount,$newPaymentReference)
	{
		$newRow['amount'] = $newAmount;
		$newRow['paymentReference'] = $newPaymentReference;
		
		$this->amountDocument += $newAmount;
		$this->row[] = $newRow;
	}

	public function validateAndWriteToDatabase($InternalDb)
	{
		$this->documentNumber = nextDocumentNumber($InternalDb, 'CustomerPayment');
		
		$stmt2 = $InternalDb->prepare('INSERT INTO CustomerPayment (Number, BookingDate, PartnerDate, AccountNumber, Amount) VALUES (?, ?, ?, ?, ?)');
		$stmt2->execute(array($this->documentNumber, $this->dateCreatedOurSide, $this->dateCreatedPartnerSide, $this->account, $this->amountDocument));
		
		$this->customerPaymentId = $InternalDb->lastInsertId();
		
		auditTrailLog($InternalDb, 'CustomerPayment', $this->customerPaymentId, 'INSERT');
		
		$booking = new GeneralLedgerAccountBooking();
		
		$booking->setDate($this->dateCreatedPartnerSide);
		$booking->setText('Customer payment #' . $this->documentNumber);
		$booking->setDocumentType('CustomerPayment');
		$booking->setDocumentTypeNumber($this->documentNumber);
		
		if ($this->amountDocument > 0){
			$booking->addRowAdvanced($this->account, '', '', '', '', $this->amountDocument, 0, 'CustomerPayment', $this->documentNumber, '');
		}else{
			$booking->addRowAdvanced($this->account, '', '', '', '', 0, $this->amountDocument*-1, 'CustomerPayment', $this->documentNumber, '');
		}
				
		foreach ($this->row as $paymentRow){
			$stmt3 = $InternalDb->prepare('SELECT CustomerId FROM CustomerInvoice WHERE PaymentReference=?');
			$stmt3->execute(array($paymentRow['paymentReference']));
			$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
			
			if (count($results3) != 1){
				throw new Exception('Action was not completed since the payment reference '.$paymentRow['paymentReference'].' could not be found');
			}
			
			$rowValueToBook = $paymentRow['amount']*-1;
			
			if ($rowValueToBook > 0){
				$booking->addRowAdvanced('1510', $results3[0]['CustomerId'], '', '', '', $rowValueToBook, 0, 'CustomerPayment', $this->documentNumber, $paymentRow['paymentReference']);
			}else{
				$booking->addRowAdvanced('1510', $results3[0]['CustomerId'], '', '', '', 0, $rowValueToBook*-1, 'CustomerPayment', $this->documentNumber, $paymentRow['paymentReference']);
			}
			
			$matchOpenItems[$results3[0]['CustomerId']][] = $paymentRow['paymentReference'];
			
			$stm4 = $InternalDb->prepare('INSERT INTO CustomerPaymentRow (ParentId, Number, PaymentReference, Amount) VALUES (?, ?, ?, ?)');
			$stm4->execute(array($this->customerPaymentId, $this->documentNumber, $paymentRow['paymentReference'], $paymentRow['amount']));
			
			auditTrailLog($InternalDb, 'CustomerPaymentRow', $InternalDb->lastInsertId(), 'INSERT');
		}
		
		$booking->validateAndWriteToDatabase($InternalDb);
		
		foreach ($matchOpenItems as $key1 => $value1 ){
			foreach ($value1 as $key2 => $value2 ){
				matchOpenItems($InternalDb, $this->dateCreatedPartnerSide, '1510', $key1, $value2);
			}
		}
		
	}

	public function getDocumentNumber()
	{
		return $this->documentNumber;
	}
}
?>