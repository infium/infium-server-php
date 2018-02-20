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
