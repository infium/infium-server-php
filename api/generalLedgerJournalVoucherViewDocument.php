<?php
require('config.php');
require('functionRenderGeneralLedgerJournalVoucher.php');

checkUserAccess('GeneralLedgerJournalVoucherView');

header('Content-type: text/html');
header('Show-Print-Icon: true');

echo renderGeneralLedgerJournalVoucher($_GET['Number']);
?>