<?php
require_once('config.php');

class GeneralLedgerClearing
{
	private $date = NULL;
	private $accountNumber = NULL;
	private $subAccountNumber = NULL;
	private $row = NULL;
	private $documentNumber = NULL;
	private $sum = 0;
	
	public function setDate($newDate)
	{
		validateDate($newDate);
		$this->date = $newDate;
	}
	
	public function setAccountNumber($newAccountNumber)
	{
		$this->accountNumber = $newAccountNumber;
	}
	
	public function setSubAccountNumber($newSubAccountNumber)
	{
		$this->subAccountNumber = $newSubAccountNumber;
	}
	
	public function addRow($pdo, $newRowId)
	{
		$newRowData = dbPrepareExecute($pdo, 'SELECT BookingDate, AccountNumber, SubAccountNumber, Amount FROM GeneralLedgerAccountBookingRow WHERE Id=?', array($newRowId));
		
		if ($newRowData[0]['BookingDate'] > $this->date){
			throw new Exception('The clearing date have to be at least the date of the latest booking done.');
		}
		
		if ($newRowData[0]['AccountNumber'] != $this->accountNumber){
			throw new Exception('The row must be booked on the same account as already specified.');
		}
		
		if ($newRowData[0]['SubAccountNumber'] != $this->subAccountNumber){
			throw new Exception('The row must be booked on the same sub account as already specified.');
		}
		
		$this->row[] = $newRowId;
		$this->sum += $newRowData[0]['Amount'];
	}
	
	public function validateAndWriteToDatabase($pdo)
	{
		if (!(bccomp($this->sum, 0, 4) === 0)){
			throw new Exception('The sum must be zero. Now it is '.decimalFormat($this->sum).'.');
		}
		
		if ($this->row == NULL){
			throw new Exception('Cannot book when there are no rows with data.');
		}
		
		$this->documentNumber = nextDocumentNumber($pdo, 'GeneralLedgerAccountClearing');
		
		dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountClearing (Number, BookingDate) VALUES (?, ?)', array($this->documentNumber, $this->date));
		$clearingNumberId = $pdo->lastInsertId();
		auditTrailLog($pdo, 'GeneralLedgerAccountClearing', $clearingNumberId, 'INSERT');
		
		foreach ($this->row as $bookingRowId){
			dbPrepareExecute($pdo, 'UPDATE GeneralLedgerAccountBookingRow SET ClearingDate=?, ClearingNumber=? WHERE Id=?', array($this->date, $this->documentNumber, $bookingRowId));
			auditTrailLog($pdo, 'GeneralLedgerAccountBookingRow', $bookingRowId, 'UPDATE');
			
			dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountClearingRow (ParentId, Number, BookingRowId) VALUES (?, ?, ?)', array($clearingNumberId, $this->documentNumber, $bookingRowId));
			auditTrailLog($pdo, 'GeneralLedgerAccountClearingRow', $pdo->lastInsertId(), 'INSERT');
		}
	}
	
	public function getDocumentNumber()
	{
		return $this->documentNumber;
	}
}
?>