<?php
require('config.php');
require('functionRenderCustomerPayment.php');

checkUserAccess('CustomerPaymentView');

header('Content-type: text/html');
header('Show-Print-Icon: true');

echo renderCustomerPayment($_GET['Number']);
?>