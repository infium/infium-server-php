<?php
require_once('config.php');

class ArticleCreate
{
	private $number = NULL;
	private $description = NULL;
	private $taxGroup = NULL;
	
	public function setNumber($number)
	{
		$this->number = $number;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	public function setTaxGroup($taxGroup)
	{
		$this->taxGroup = $taxGroup;
	}
	
	public function create($pdo)
	{
		dbPrepareExecute($pdo, 'INSERT INTO Article (Active, Number, Description, TaxGroup) VALUES (?, ?, ?, ?)', array(1, $this->number, $this->description, $this->taxGroup));
	
		auditTrailLog($pdo, 'Article', $pdo->lastInsertId(), 'INSERT');
	}
}
?>