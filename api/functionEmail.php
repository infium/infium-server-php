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