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

$now = time();

function auditTrailLog($pdo, $table, $tableId, $operation){
    global $now;
    global $testing;

    if (!preg_match('/^[a-zA-Z]{1,64}$/', $table)){
        throw new Exception('The table must consist of the letters A-Z or a-z. No other characters.');
    }

    if (($operation == 'INSERT')||($operation == 'UPDATE')){
        $data = dbPrepareExecute($pdo, 'SELECT * FROM '.$table.' WHERE Id=?', array($tableId));

        foreach($data[0] as $key => $value){
            if (($key == 'PasswordSalt')||($key == 'PasswordEncrypted')){
                $data2[$key] = '*';
            }else{
                $data2[$key] = $value;
            }
        }

        $dataJSON = json_encode($data2);
    }else{
        $dataJSON = NULL;
    }

    if (isset($testing) && $testing == True){
        $user = 0;
    }else{
        $user = getUser($pdo);
    }

    dbPrepareExecute($pdo, "INSERT INTO AuditTrail (`Table`, `TableId`, `Operation`, `Data`, `Time`, `User`, `IP`) VALUES (?, ?, ?, ?, ?, ?, ?)", array($table, $tableId, $operation, $dataJSON, date("Y-m-d H:i:s", $now), $user, $_SERVER['REMOTE_ADDR']));
}
?>