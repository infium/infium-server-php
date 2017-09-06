<?php
function nextDocumentNumber ($pdo, $documentType){
    dbPrepareExecute($pdo, 'UPDATE Number SET LastNumber=LastNumber+1 WHERE Type=?', array($documentType));
    $results = dbPrepareExecute($pdo, 'SELECT Id, Prefix, LastNumber FROM Number WHERE Type=?', array($documentType));

    auditTrailLog($pdo, 'Number', $results[0]['Id'], 'UPDATE');

    return $results[0]['Prefix'].$results[0]['LastNumber'];
}
?>