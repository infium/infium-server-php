<?php
require('config.php');
require('functionRenderCustomerInvoice.php');

checkUserAccess('CustomerInvoiceView');

header('Content-type: text/html');
header('Show-Print-Icon: true');

echo renderCustomerInvoice($_GET['Number']);
?>