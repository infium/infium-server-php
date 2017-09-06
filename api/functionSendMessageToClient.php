<?php
function sendMessageToClient($message){
    $response['Response'] = 'LocalActions';
    $response['Data'][0]['Action'] = 'MessageFlash';
    $response['Data'][0]['Message'] = $message;

    header('Content-type: application/json');
    echo json_encode($response,JSON_PRETTY_PRINT);
}
?>