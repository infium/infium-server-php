<?php
use \google\appengine\api\mail\Message;

function emailSend($from, $to, $subject, $textBody, $htmlBody, $attachmentName){
    $pdo = createPdo();

    $userTokenData = dbPrepareExecute($pdo, 'SELECT UserId FROM UserToken WHERE Token=?', array($_SERVER['HTTP_X_CLIENT_LOGIN_TOKEN']));
    $userData = dbPrepareExecute($pdo, 'SELECT Email FROM User WHERE Id=?', array($userTokenData[0]['UserId']));

    $message = new Message();
    $message->setSender($from);
    $message->setReplyTo($userData[0]['Email']);
    $message->addTo($to);
    $message->addCc($userData[0]['Email']);
    $message->setSubject($subject);
    $message->setTextBody($textBody);
    $message->setHtmlBody($htmlBody);
    $message->addAttachment($attachmentName, $htmlBody);
    $message->send();
}
?>