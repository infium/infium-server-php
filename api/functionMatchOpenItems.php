<?php
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