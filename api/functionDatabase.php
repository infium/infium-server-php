<?php
function createPdo($db = NULL){
    global $databaseDSN, $databaseUsername, $databasePasswd;
    if ($db != NULL){
        $dbName = $db;
    }else{
        $company = $_SERVER['HTTP_X_CLIENT_LOGIN_COMPANY'];

        if (!preg_match('/^[0-9]{6}$/', $company)){
            throw new Exception('The format of the company must be NNNNNN.');
        }

        $dbName = 'Company_'.$company;
    }

    return new PDO($databaseDSN.';dbname='.$dbName.';charset=utf8', $databaseUsername, $databasePasswd, array(PDO::ATTR_TIMEOUT => '10',PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}

function dbPrepareExecute($pdo, $prepare, $dataArray = array()){
    $stmt = $pdo->prepare($prepare);
    $stmt->execute($dataArray);
    try{
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(Exception $e){
        return NULL;
    }
}
?>