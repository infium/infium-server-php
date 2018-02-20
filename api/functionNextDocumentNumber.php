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

function nextDocumentNumber ($pdo, $documentType){
    dbPrepareExecute($pdo, 'UPDATE Number SET LastNumber=LastNumber+1 WHERE Type=?', array($documentType));
    $results = dbPrepareExecute($pdo, 'SELECT Id, Prefix, LastNumber FROM Number WHERE Type=?', array($documentType));

    auditTrailLog($pdo, 'Number', $results[0]['Id'], 'UPDATE');

    return $results[0]['Prefix'].$results[0]['LastNumber'];
}
?>
