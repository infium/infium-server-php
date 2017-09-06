<?php
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