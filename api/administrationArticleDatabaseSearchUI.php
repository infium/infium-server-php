<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationArticleDatabase');

$ui = new UserInterface();

$ui->setTitle('Article');
$ui->setWindow('New');
$ui->setMethod('POST');
$ui->setUrl($baseUrl.'administrationArticleDatabaseSearchProcess.php');
$ui->setButtonLabel('Search');
$ui->setTitleBarColorNewWindow($titleBarColorAdministrationArticleDatabase);

$ui->addField('Query',NULL,'Search query (optional)');
$ui->addLabelTrueFalse('OnlyActive','Only active');
$value['OnlyActive'] = True;

$ui->setVisibleData($value);

echo $ui->getObjectAsJSONString();
?>