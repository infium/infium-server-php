<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationArticleDatabase');

$ui = new UserInterface();

$ui->setTitle('Article');

if (checkUserAccessBoolean('AdministrationArticleDatabase')){
	$ui->addLabelValueLink('Search', NULL, 'GET', $baseUrl.'administrationArticleDatabaseSearchUI.php', NULL, $titleBarColorAdministrationArticleDatabase);
}

if (checkUserAccessBoolean('AdministrationArticleDatabase')){
	$ui->addLabelValueLink('Add', NULL, 'GET', $baseUrl.'administrationArticleDatabaseAddUI.php', NULL, $titleBarColorAdministrationArticleDatabase);
}

echo $ui->getObjectAsJSONString();
?>