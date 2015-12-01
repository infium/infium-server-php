<?php
require('config.php');
require('functionRenderVendorPaymentCompleted.php');

checkUserAccess('VendorPaymentCompletedView');

header('Content-type: text/html');
header('Show-Print-Icon: true');

echo renderVendorPaymentCompleted($_GET['Number']);
?>