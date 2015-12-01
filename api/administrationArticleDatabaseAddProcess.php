<?php
require('config.php');
require('classUserInterface.php');
require('classArticleCreate.php');

checkUserAccess('AdministrationArticleDatabase');

try{
	$inputVisible = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	$pdo = createPdo();

	$pdo->exec('START TRANSACTION');
	
	$articleCreate = new ArticleCreate();
	$articleCreate->setNumber($inputVisible['Number']);
	$articleCreate->setDescription($inputVisible['Description']);
	$articleCreate->setTaxGroup($inputVisible['TaxGroup']);
	$articleCreate->create($pdo);
		
	$pdo->exec('COMMIT');

	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Reload';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'Article created #'.$inputVisible['Number'];
} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'MessageFlash';
	$response['Data'][0]['Message'] = 'The following error occurred: ' . $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($response,JSON_PRETTY_PRINT);
?>