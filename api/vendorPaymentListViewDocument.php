<?php
require('config.php');
require('functionRenderVendorPaymentList.php');

checkUserAccess('VendorPaymentListView');

header('Content-type: text/html');
header('Show-Print-Icon: true');

echo renderVendorPaymentList($_GET['Number']);
?>