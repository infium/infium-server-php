<?php

function validateDate($date){
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)){
        throw new Exception('The date needs to be in the format YYYY-MM-DD.');
    }

    if (!checkdate(substr($date,5,2), substr($date,8,2), substr($date,0,4))){
        throw new Exception('The date '.$date.' does not exist.');
    }
}

function validateCustomerNumber($pdo, $customerNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumCustomer FROM Customer WHERE Number=?', array($customerNumber));

    if ($results[0]['NumCustomer'] != 1){
        throw new Exception('A valid customer needs to be selected.');
    }
}

function validateVendorNumber($pdo, $vendorNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumVendor FROM Vendor WHERE Number=?', array($vendorNumber));

    if ($results[0]['NumVendor'] != 1){
        throw new Exception('A valid vendor needs to be selected.');
    }
}

function validateArticleNumber($pdo, $articleNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumArticle FROM Article WHERE Number=?', array($articleNumber));

    if ($results[0]['NumArticle'] != 1){
        throw new Exception('A valid article needs to be selected.');
    }
}

function validateAccountNumber($pdo, $year, $accountNumber){
    if (!preg_match('/^[0-9]{4}$/', $accountNumber)){
        throw new Exception('The account needs to be in the format NNNN.');
    }

    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumAccount FROM GeneralLedgerAccount WHERE Year=? AND AccountNumber=?', array($year, $accountNumber));

    if ($results[0]['NumAccount'] != 1){
        throw new Exception('A valid account that exist in the booking date year needs to be selected.');
    }
}

function validateNewAccountNumber($accountNumber){
    if (!preg_match('/^[0-9]{4}$/', $accountNumber)){
        throw new Exception('The account needs to be in the format NNNN.');
    }
}

function validateNumber($number){
    if (!preg_match('/^\-{0,1}[0-9]{1,16}\.{0,1}[0-9]{0,2}$/', $number)){
        throw new Exception('Numbers need to be in the format (-)NNNNN.NN');
    }
}

function validateUsername($username){
    if (!preg_match('/^[a-z0-9]{1,32}$/', $username)){
        throw new Exception('Usernames need to be between 1 and 32 characters and may only consist of lowercase "a" to "z" and/or "0" to "9"');
    }
}

function validateCustomerInvoiceDocumentNumber($pdo, $documentNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM CustomerInvoice WHERE Number=?', array($documentNumber));

    if ($results[0]['NumberOfDocuments'] != 1){
        throw new Exception('A valid document number needs to be entered.');
    }
}

function validateCustomerTransactionDocumentNumber($pdo, $documentNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM CustomerTransaction WHERE Number=?', array($documentNumber));

    if ($results[0]['NumberOfDocuments'] != 1){
        throw new Exception('A valid document number needs to be entered.');
    }
}

function validateCustomerPaymentDocumentNumber($pdo, $documentNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM CustomerPayment WHERE Number=?', array($documentNumber));

    if ($results[0]['NumberOfDocuments'] != 1){
        throw new Exception('A valid document number needs to be entered.');
    }
}

function validateVendorInvoiceDocumentNumber($pdo, $documentNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM VendorInvoice WHERE Number=?', array($documentNumber));

    if ($results[0]['NumberOfDocuments'] != 1){
        throw new Exception('A valid document number needs to be entered.');
    }
}

function validateVendorTransactionDocumentNumber($pdo, $documentNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM VendorTransaction WHERE Number=?', array($documentNumber));

    if ($results[0]['NumberOfDocuments'] != 1){
        throw new Exception('A valid document number needs to be entered.');
    }
}

function validateVendorPaymentListDocumentNumber($pdo, $documentNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM VendorPaymentList WHERE Number=?', array($documentNumber));

    if ($results[0]['NumberOfDocuments'] != 1){
        throw new Exception('A valid document number needs to be entered.');
    }
}

function validateVendorPaymentCompletedDocumentNumber($pdo, $documentNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM VendorPaymentCompleted WHERE Number=?', array($documentNumber));

    if ($results[0]['NumberOfDocuments'] != 1){
        throw new Exception('A valid document number needs to be entered.');
    }
}

function validateTaxReportDocumentNumber($pdo, $documentNumber){
    $results = dbPrepareExecute($pdo, 'SELECT COUNT(*) as NumberOfDocuments FROM TaxReport WHERE Number=?', array($documentNumber));

    if ($results[0]['NumberOfDocuments'] != 1){
        throw new Exception('A valid document number needs to be entered.');
    }
}
?>