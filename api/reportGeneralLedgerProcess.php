<?php
/*
 * Copyright 2012-2018 Marcus Hammar
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

require('config.php');
require('classUserInterface.php');
require('functionRenderReportGeneralLedger.php');

checkUserAccess('ReportGeneralLedger');

try {
	$input = json_decode(file_get_contents('php://input'), TRUE)['VisibleData'];

	validateDate($input['DateFrom']);
	validateDate($input['DateTo']);

	if ($input['DateTo'] < $input['DateFrom']){
		throw new Exception('The "From" date needs to be greater or equal to the "To" date.');
	}

	if (substr($input['DateFrom'],0,4) != substr($input['DateTo'],0,4)) {
		throw new Exception('The "From" date needs to have the same year as the "To" date.');
	}

	$output = renderReportGeneralLedger($input['DateFrom'], $input['DateTo'], $input['Account']);
	header('Content-type: text/html');
	header('Show-Print-Icon: true');
	echo $output;

} catch (Exception $e) {
	$response['Response'] = 'LocalActions';
	$response['Data'][0]['Action'] = 'Pop';
	$response['Data'][1]['Action'] = 'MessageFlash';
	$response['Data'][1]['Message'] = 'The following error occurred: ' . $e->getMessage();

	header('Content-type: application/json');
	echo json_encode($response,JSON_PRETTY_PRINT);
}
?>
