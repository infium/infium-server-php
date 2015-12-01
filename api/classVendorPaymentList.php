<?php
require_once('classGeneralLedgerAccountBooking.php');

class VendorPaymentList
{
	private $id = NULL;
	private $documentNumber = NULL;
	private $row = NULL;

	private $debug = NULL;
	private $bookingDate = NULL;
	
	public function setBookingDate($newBookingDate)
	{
		$this->bookingDate = $newBookingDate;
	}
	
	public function getDebug()
	{
		return $this->debug;
	}
	
	public function addRow($id, $amount = NULL, $reversalRowId = NULL)
	{		
		$newRow['id'] = $id;
		$newRow['amount'] = $amount;
		
		$newRow['reversalRowId'] = $reversalRowId;
		
		$this->row[] = $newRow;		
	}

	public function validateAndWriteToDatabase($InternalDb)
	{
		$this->documentNumber = nextDocumentNumber($InternalDb, 'VendorPaymentList');
		
		$stmt2 = $InternalDb->prepare('INSERT INTO VendorPaymentList (Number, BookingDate) VALUES (?, ?)');
		$stmt2->execute(array($this->documentNumber, $this->bookingDate));
		
		$this->id = $InternalDb->lastInsertId();
		
		$docAmount = 0;
		
		foreach ($this->row as $documentRow){
			$stmt3 = $InternalDb->prepare('SELECT Number, DueDate, VendorId, InternalName, BankAccount, PaymentReference, AmountGross FROM VendorInvoice WHERE Id=?');
			$stmt3->execute(array($documentRow['id']));
			$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
			
			if ($documentRow['amount'] != NULL){
				$rowAmount = $documentRow['amount'];
			}else{
				$rowAmount = $results3[0]['AmountGross'];
			}
			
			$docAmount = $docAmount + $rowAmount;
						
			$stmt5 = $InternalDb->prepare('UPDATE VendorInvoice SET AmountGrossRemaining = AmountGrossRemaining - ? WHERE Id = ?');
			$stmt5->execute(array($rowAmount, $documentRow['id']));
			
			auditTrailLog($InternalDb, 'VendorInvoice', $documentRow['id'], 'UPDATE');
			
			$reversalRowId = NULL;
			if ($documentRow['reversalRowId'] != NULL){
				$previousReversalRow = dbPrepareExecute($InternalDb, 'SELECT ReversalRowId FROM VendorPaymentListRow WHERE Id=?', array($documentRow['reversalRowId']));
				if ($previousReversalRow[0]['ReversalRowId'] != NULL){
					dbPrepareExecute($InternalDb, 'UPDATE VendorPaymentListRow SET AmountRemaining=AmountRemaining+? WHERE Id=?', array($rowAmount, $previousReversalRow[0]['ReversalRowId']));
					
					auditTrailLog($InternalDb, 'VendorPaymentListRow', $previousReversalRow[0]['ReversalRowId'], 'UPDATE');
					
					$reversalRowId = $previousReversalRow[0]['ReversalRowId'];
				}else{
					dbPrepareExecute($InternalDb, 'UPDATE VendorPaymentListRow SET AmountRemaining=AmountRemaining+? WHERE Id=?', array($rowAmount, $documentRow['reversalRowId']));
					
					auditTrailLog($InternalDb, 'VendorPaymentListRow', $documentRow['reversalRowId'], 'UPDATE');
					
					$reversalRowId = $documentRow['reversalRowId'];
				}
				$amountRemaining = 0;
			}else{
				$amountRemaining = $rowAmount;
			}
			
			$stmt6 = $InternalDb->prepare('INSERT INTO VendorPaymentListRow (ParentId, Number, ReversalRowId, PreviousDocumentType, PreviousRowId, BankAccount, InternalName, PaymentReference, DueDate, Amount, AmountRemaining) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
			$stmt6->execute(array($this->id, $this->documentNumber, $reversalRowId, 'VendorInvoice', $documentRow['id'], $results3[0]['BankAccount'], $results3[0]['InternalName'], $results3[0]['PaymentReference'], $results3[0]['DueDate'], $rowAmount, $amountRemaining));
			
			auditTrailLog($InternalDb, 'VendorPaymentListRow', $InternalDb->lastInsertId(), 'INSERT');
		}
		
		$stmt7 = $InternalDb->prepare('UPDATE VendorPaymentList SET Amount=? WHERE Id=?');
		$stmt7->execute(array($docAmount, $this->id));
		
		auditTrailLog($InternalDb, 'VendorPaymentList', $this->id, 'INSERT');
	}

	public function getDocumentNumber()
	{
		return $this->documentNumber;
	}
}
?>