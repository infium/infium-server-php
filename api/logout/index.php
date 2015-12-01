<?php
require('../config.php');

$pdo = createPdo();

$stmt = $pdo->prepare('DELETE FROM UserToken WHERE Token=?');
$stmt->execute(array($_SERVER['HTTP_X_CLIENT_LOGIN_TOKEN']));
?>