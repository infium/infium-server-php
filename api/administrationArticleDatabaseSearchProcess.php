<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationArticleDatabase');

$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

$ui = new UserInterface();

$ui->setTitle('Results');

$pdo = createPdo();

if ($input['OnlyActive'] == True){
	$results = dbPrepareExecute($pdo, "SELECT Id, Number, Description FROM Article WHERE ((Description LIKE ?) AND Active=?) ORDER BY Description ASC", array('%'.$input['Query'].'%', True));
}else{
	$results = dbPrepareExecute($pdo, "SELECT Id, Number, Description FROM Article WHERE Description LIKE ? ORDER BY Description ASC", array('%'.$input['Query'].'%'));
}

foreach ($results as $row){
	$ui->addLabelValueLink($row['Number'].' '.$row['Description'], NULL, 'GET', $baseUrl.'administrationArticleDatabaseEditUI.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationArticleDatabase);
}

if (count($results) == 0){
	$ui->addLabelValueLink('No articles match the search');	
}

echo $ui->getObjectAsJSONString();
?>