<?php
function decimalFormat($value){
    $valueFormatted = number_format($value,2,'.',',');

    if ($valueFormatted == '-0.00'){
        return '0.00';
    }else{
        return $valueFormatted;
    }
}
?>