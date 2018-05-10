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

function getUser($pdo){
    $results = dbPrepareExecute($pdo, 'SELECT UserId FROM UserToken WHERE Token=?', array($_SERVER['HTTP_X_CLIENT_LOGIN_TOKEN']));

    if (count($results) != 1){
        throw new Exception('User token cannot be found');
    }

    return $results[0]['UserId'];
}

function getUserName($pdo, $userId){
    if ($userId == 0){
        return 'Initial system setup';
    }

    $results = dbPrepareExecute($pdo, 'SELECT Name FROM User WHERE Id=?', array($userId));

    if (count($results) != 1){
        throw new Exception('User cannot be found');
    }

    return $results[0]['Name'];
}

function checkUserAccess($resourceName = NULL, $throwException = False){
    $pdo = createPdo();

    $stmt = $pdo->prepare('SELECT COUNT(*) as MatchingUsers FROM UserToken WHERE Token=?');
    $stmt->execute(array($_SERVER['HTTP_X_CLIENT_LOGIN_TOKEN']));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results[0]['MatchingUsers'] != 1){

        if ($throwException == True){
            throw new Exception('User token cannot be found');
        }else{
            $response['Response'] = 'LocalActions';
            $response['Data'][0]['Action'] = 'Logout';
            $response['Data'][1]['Action'] = 'MessageFlash';
            $response['Data'][1]['Message'] = 'User is not logged in';

            header('Content-type: application/json');
            echo json_encode($response,JSON_PRETTY_PRINT);

            exit();
        }
    }

    if ($resourceName != NULL){
        $stmt2 = $pdo->prepare('SELECT UserId FROM UserToken WHERE Token=?');
        $stmt2->execute(array($_SERVER['HTTP_X_CLIENT_LOGIN_TOKEN']));
        $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $results3 = dbPrepareExecute($pdo, 'SELECT Access FROM User WHERE Id=?', array($results2[0]['UserId']));

        $accessArray = json_decode($results3[0]['Access'], TRUE);

        $accessGranted = False;

        foreach($accessArray as $access){
            if ($resourceName == $access){
                $accessGranted = True;
            }
        }

        if ($accessGranted == False){
            if ($throwException == True){
                throw new Exception('User has no access to this');
            }else{
                $response['Response'] = 'LocalActions';
                $response['Data'][0]['Action'] = 'MessageFlash';
                $response['Data'][0]['Message'] = 'User has no access to this';

                header('Content-type: application/json');
                echo json_encode($response,JSON_PRETTY_PRINT);

                exit();
            }
        }
    }
}

function checkUserAccessBoolean($resourceName = NULL) {
    try {
        checkUserAccess($resourceName, True);
    } catch (Exception $e) {
        return false;
    }
    return true;
}
?>
