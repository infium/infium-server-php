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

class GeneralLedgerAccountBooking
{
	private $date = NULL;
	private $text = '';
	private $row = NULL;
	private $year = NULL;
	private $documentNumber = NULL;
	private $sumDebit = 0.0;
	private $sumCredit = 0.0;
	private $documentType = '';
	private $documentTypeNumber = '';

	public function setDate($newDate)
	{
		validateDate($newDate);

		$year = substr($newDate,0,4);
		$month = substr($newDate,5,2);
		$day = substr($newDate,8,2);

		if (!checkdate($month,$day,$year)){
			throw new Exception('This date does not exist in the gregorian calendar. Please note that the format should be YYYY-MM-DD.');
		}

		$this->year = $year;
		$this->date = $newDate;
	}

	public function setText($newText)
	{
		$this->text = $newText;
	}

	public function setDocumentType($newDocumentType)
	{
		$this->documentType = $newDocumentType;
	}

	public function setDocumentTypeNumber($newDocumentTypeNumber)
	{
		$this->documentTypeNumber = $newDocumentTypeNumber;
	}

	public function addRow($newAccount, $newProfitCenter, $newDebit, $newCredit)
	{
		$this->addRowAdvanced($newAccount, '', '', $newProfitCenter, '', $newDebit, $newCredit, '', '', '');
	}

	public function addRowAdvanced($newAccount, $newSubAccountNumber, $newBalanceNoteBreakdown, $newProfitCenter, $newTaxCode, $newDebit, $newCredit, $newDocumentType, $newDocumentNumber, $newClearingReference, $taxNumber = '')
	{
		if (!preg_match('/^[0-9]{4}$/', $newAccount)){
			throw new Exception('The format of the account is wrong. It should be NNNN.');
		}

		if ((abs($newDebit) > 0)&&(abs($newCredit) > 0)){
			throw new Exception('Both debit and credit cannot be set for the same row.');
		}

		if (($newDebit < 0)||($newCredit < 0)){
			throw new Exception('Debit and/or credit is negative. Debit and credit needs to be positive values.');
		}

		validateNumber($newDebit);
		validateNumber($newCredit);

		$this->sumDebit += $newDebit;
		$this->sumCredit += $newCredit;

		$newRow['account'] = $newAccount;
		$newRow['subAccountNumber'] = $newSubAccountNumber;
		$newRow['balanceNoteBreakdown'] = $newBalanceNoteBreakdown;
		$newRow['profitCenter'] = $newProfitCenter;
		$newRow['taxCode'] = $newTaxCode;
		$newRow['debit'] = $newDebit;
		$newRow['credit'] = $newCredit;
		$newRow['documentType'] = $newDocumentType;
		$newRow['documentNumber'] = $newDocumentNumber;
		$newRow['clearingReference'] = $newClearingReference;
		$newRow['taxNumber'] = $taxNumber;

		$this->row[] = $newRow;
	}

	public function validateAndWriteToDatabase($InternalDb)
	{
		if (!(bccomp($this->sumDebit, $this->sumCredit, 4) === 0)){
			throw new Exception('The sum of debit and credit must be equal for the whole journal voucher. (Debit = '.$this->sumDebit.' Credit = '.$this->sumCredit.')');
		}

		if ($this->row == NULL){
			throw new Exception('Cannot book when there are no rows with data.');
		}

		$this->documentNumber = nextDocumentNumber($InternalDb, 'GeneralLedgerAccountBooking');

		$yearOpen = dbPrepareExecute($InternalDb, 'SELECT Status FROM GeneralLedgerYear WHERE Year=?', array(substr($this->date,0,4)));

		if ($yearOpen[0]['Status'] != 'Open'){
			throw new Exception('The year '.substr($this->date,0,4).' is currently not open for postings.');
		}

		$stmt3 = $InternalDb->prepare('INSERT INTO GeneralLedgerAccountBooking (Number, Year, BookingDate, Text, DocumentType, DocumentTypeNumber) VALUES (?, ?, ?, ?, ?, ?)');
		$stmt3->execute(array($this->documentNumber, substr($this->date,0,4), $this->date, $this->text, $this->documentType, $this->documentTypeNumber));

		$generalLedgerAccountBookingId = $InternalDb->lastInsertId();

		auditTrailLog($InternalDb, 'GeneralLedgerAccountBooking', $InternalDb->lastInsertId(), 'INSERT');

		foreach ($this->row as $bookingRow){

			validateAccountNumber($InternalDb, substr($this->date,0,4), $bookingRow['account']);

			$stmt4 = $InternalDb->prepare('SELECT Id FROM GeneralLedgerAccountBalance WHERE Year=? AND BookingDate=? AND AccountNumber=? AND SubAccountNumber=? AND ProfitCenter=?');
			$stmt4->execute(array(substr($this->date,0,4), $this->date, $bookingRow['account'], $bookingRow['subAccountNumber'], $bookingRow['profitCenter']));
			$results4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);

			if (count($results4) == 0){
				$stmt5 = $InternalDb->prepare('INSERT INTO GeneralLedgerAccountBalance (Year, BookingDate, AccountNumber, SubAccountNumber, ProfitCenter, Debit, Credit, Amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
				$stmt5->execute(array(substr($this->date,0,4), $this->date, $bookingRow['account'], $bookingRow['subAccountNumber'], $bookingRow['profitCenter'], $bookingRow['debit'], $bookingRow['credit'], $bookingRow['debit'] - $bookingRow['credit']));

				auditTrailLog($InternalDb, 'GeneralLedgerAccountBalance', $InternalDb->lastInsertId(), 'INSERT');

			}elseif (count($results4) == 1){
				$stmt6 = $InternalDb->prepare('UPDATE GeneralLedgerAccountBalance SET Debit=Debit+?, Credit=Credit+?, Amount=Amount+? WHERE Id=?');
				$stmt6->execute(array($bookingRow['debit'], $bookingRow['credit'], $bookingRow['debit'] - $bookingRow['credit'], $results4[0]['Id']));

				auditTrailLog($InternalDb, 'GeneralLedgerAccountBalance', $results4[0]['Id'], 'UPDATE');

			}else{
				throw new Exception('Contact support. Issue in table "GeneralLedgerAccountBalance"');
			}

			$stmt7 = $InternalDb->prepare('INSERT INTO GeneralLedgerAccountBookingRow (ParentId, Year, BookingDate, Number, AccountNumber, SubAccountNumber, BalanceNoteBreakdown, ProfitCenter, Text, Debit, Credit, Amount, TaxCode, TaxNumber, DocumentType, DocumentTypeNumber, ClearingReference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
			$stmt7->execute(array($generalLedgerAccountBookingId, substr($this->date,0,4), $this->date, $this->documentNumber, $bookingRow['account'], $bookingRow['subAccountNumber'], $bookingRow['balanceNoteBreakdown'], $bookingRow['profitCenter'], $this->text, $bookingRow['debit'], $bookingRow['credit'], $bookingRow['debit'] - $bookingRow['credit'], $bookingRow['taxCode'], $bookingRow['taxNumber'], $bookingRow['documentType'], $bookingRow['documentNumber'], $bookingRow['clearingReference']));

			auditTrailLog($InternalDb, 'GeneralLedgerAccountBookingRow', $InternalDb->lastInsertId(), 'INSERT');
		}
	}

	public function getYear()
	{
		return $this->year;
	}

	public function getDocumentNumber()
	{
		return $this->documentNumber;
	}

	public function countRows()
	{
		return count($this->row);
	}
}
?>
