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

function matchOpenItems ($pdo, $date, $account, $subAccount, $reference){
    $sum = 0;
    $results = dbPrepareExecute($pdo, 'SELECT Id, Amount FROM GeneralLedgerAccountBookingRow WHERE AccountNumber=? AND SubAccountNumber=? AND ClearingReference=? AND ClearingDate IS NULL', array($account, $subAccount, $reference));

    foreach ($results as $row){
        $sum += $row['Amount'];
    }

    if (($sum == 0)&&(count($results) > 0)){

        $clearingNumber = nextDocumentNumber($pdo, 'GeneralLedgerAccountClearing');

        dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountClearing (Number, BookingDate) VALUES (?, ?)', array($clearingNumber, $date));
        $clearingNumberId = $pdo->lastInsertId();
        auditTrailLog($pdo, 'GeneralLedgerAccountClearing', $clearingNumberId, 'INSERT');

        foreach ($results as $row){
            dbPrepareExecute($pdo, 'UPDATE GeneralLedgerAccountBookingRow SET ClearingDate=?, ClearingNumber=? WHERE Id=?', array($date, $clearingNumber, $row['Id']));
            auditTrailLog($pdo, 'GeneralLedgerAccountBookingRow', $row['Id'], 'UPDATE');

            dbPrepareExecute($pdo, 'INSERT INTO GeneralLedgerAccountClearingRow (ParentId, Number, BookingRowId) VALUES (?, ?, ?)', array($clearingNumberId, $clearingNumber, $row['Id']));
            auditTrailLog($pdo, 'GeneralLedgerAccountClearingRow', $pdo->lastInsertId(), 'INSERT');
        }
    }
}
?>