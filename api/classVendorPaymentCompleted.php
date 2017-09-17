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

class VendorPaymentCompleted {
	
	private $row = NULL;
	private $documentNumber = NULL;
	private $date = NULL;
	private $amountDocument = 0;
	private $id = NULL;
	
	public function setDate($newDate)
	{
		$this->date = $newDate;
	}
	
	public function addRow($newId,$newAmount)
	{
		$newRow['amount'] = $newAmount;
		$newRow['id'] = $newId;
		
		$this->amountDocument += $newAmount;
		$this->row[] = $newRow;
	}

	public function validateAndWriteToDatabase($InternalDb)
	{
		$this->documentNumber = nextDocumentNumber($InternalDb, 'VendorPaymentCompleted');
		
		$stmt2 = $InternalDb->prepare('INSERT INTO VendorPaymentCompleted (Number, BookingDate, PartnerDate, AccountNumber, Amount) VALUES (?, ?, ?, ?, ?)');
		$stmt2->execute(array($this->documentNumber, $this->date, $this->date, '1920', $this->amountDocument));
		
		$this->id = $InternalDb->lastInsertId();
		
		auditTrailLog($InternalDb, 'VendorPaymentCompleted', $InternalDb->lastInsertId(), 'INSERT');
		
		$booking = new GeneralLedgerAccountBooking();
		
		$booking->setDate($this->date);
		$booking->setText('Vendor payment #' . $this->documentNumber);
		$booking->setDocumentType('VendorPaymentCompleted');
		$booking->setDocumentTypeNumber($this->documentNumber);
		
		$amountBank = $this->amountDocument * -1;
		
		if ($amountBank > 0){
			$debit = $amountBank;
			$credit = 0;
		}else{
			$debit = 0;
			$credit = $amountBank * -1;
		}
		
		$booking->addRowAdvanced('1920', '', '', '', '', $debit, $credit, 'VendorPaymentCompleted', $this->documentNumber, '');
		
		foreach ($this->row as $paymentRow){
			
			$stmt3 = $InternalDb->prepare('SELECT PreviousRowId FROM VendorPaymentListRow WHERE Id=?');
			$stmt3->execute(array($paymentRow['id']));
			$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
			
			$stmt4 = $InternalDb->prepare('SELECT Number, VendorId, PaymentReference FROM VendorInvoice WHERE Id=?');
			$stmt4->execute(array($results3[0]['PreviousRowId']));
			$results4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
			
			if ($paymentRow['amount'] > 0){
				$debit = $paymentRow['amount'];
				$credit = 0;
			}else{
				$debit = 0;
				$credit = $paymentRow['amount'] * -1;
			}
			
			$booking->addRowAdvanced('2441', $results4[0]['VendorId'], '', '', '', $debit, $credit, 'VendorPaymentCompleted', $this->documentNumber, $results4[0]['PaymentReference']);
			
			$matchOpenItems[$results4[0]['VendorId']][] = $results4[0]['PaymentReference'];
			
			$stm4 = $InternalDb->prepare('INSERT INTO VendorPaymentCompletedRow (ParentId, Number, PreviousDocumentType, PreviousRowId, PaymentReferenceOurSide, PaymentReferencePartnerSide, Amount) VALUES (?, ?, ?, ?, ?, ?, ?)');
			$stm4->execute(array($this->id, $this->documentNumber, 'VendorPaymentList', $paymentRow['id'], $results4[0]['Number'], $results4[0]['PaymentReference'], $paymentRow['amount']));
			
			auditTrailLog($InternalDb, 'VendorPaymentCompletedRow', $InternalDb->lastInsertId(), 'INSERT');
			
			$stmt5 = $InternalDb->prepare('UPDATE VendorPaymentListRow SET AmountRemaining=AmountRemaining-? WHERE Id=?');
			$stmt5->execute(array($paymentRow['amount'],$paymentRow['id']));
			
			auditTrailLog($InternalDb, 'VendorPaymentListRow', $paymentRow['id'], 'UPDATE');
		}
		$booking->validateAndWriteToDatabase($InternalDb);
		
		foreach ($matchOpenItems as $key1 => $value1 ){
			foreach ($value1 as $key2 => $value2 ){
				matchOpenItems($InternalDb, $this->date, '2441', $key1, $value2);
			}
		}
	}
	
	public function getDocumentNumber()
	{
		return $this->documentNumber;
	}
}
?>