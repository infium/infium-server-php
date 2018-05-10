<?php
/*
 * Copyright 2012-2017 Marcus Hammar
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
