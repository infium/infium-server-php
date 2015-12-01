<?php
require('config.php');
require('functionRenderReportTax.php');

checkUserAccess('ReportTax');

header('Content-type: text/html');
header('Show-Print-Icon: true');

echo renderReportTax($_GET['Number']);
?>