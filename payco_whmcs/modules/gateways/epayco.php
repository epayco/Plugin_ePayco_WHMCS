<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require_once(ROOTDIR . "/modules/gateways/epayco/epayco.php");
function Epayco_config()
{    
    $modulo = "epayco";
    $nombre = "Epayco";
    $obj = new EpaycoConfig($nombre,$modulo);
    $salida = $obj->getConfigModulo();
    return $salida;
}
function Epayco_link($params)
{
    $obj = new EpaycoConfig();
    $salida = $obj->getLinkPago($params);
    return $salida;
}
?>