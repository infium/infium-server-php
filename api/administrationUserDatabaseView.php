<?php
require('config.php');
require('classUserInterface.php');

checkUserAccess('AdministrationUserDatabaseView');

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT Id,Name FROM User ORDER BY Name ASC');
$stmt->execute(array());
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ui = new UserInterface();

$ui->setTitle('Change');

foreach ($results as $row){
	$ui->addLabelValueLink($row['Name'], NULL, 'GET', $baseUrl.'administrationUserDatabaseView2.php?Id='.$row['Id'], NULL, $titleBarColorAdministrationUserDatabase);
}

echo $ui->getObjectAsJSONString();
?>