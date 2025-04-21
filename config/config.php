<?php

define("CLIENT_ID", "AZClHD376iqJAP9y0UgJ-2yrPs-fEDv8eFnD05TXdtdW106h9y13ve6IAAudr-gk2SM704iL_47TFBH9");
define("CURRENCY", "EUR");
define("KEY_TOKEN", "APR.wqc-354*");

define("MONEDA", "€");

session_start();

$num_cart = 0;
if (isset($_SESSION['carrito']['productos'])) {
    $num_cart = count($_SESSION['carrito']['productos']);
}
?>