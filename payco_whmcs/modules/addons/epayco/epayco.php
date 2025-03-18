<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require_once(ROOTDIR . "/modules/gateways/epayco/epayco.php");
function epayco_config()
{
    $obj = new EpaycoConfig();
    $idioma = $obj->checkIdioma();
    return array(
        "name" => "Epayco", 
        "description" => traduccionEpayco($idioma, "epayco_config_1"), 
        "author" => "Epayco", 
        "language" => "spanish", 
        "version" => "17.3", 
        "fields" => array(
            "idioma" => array(
                "FriendlyName" => traduccionEpayco($idioma, "epayco_config_5"), 
                "Type" => "dropdown", 
                "Options" => array(
                    "es" => "Español", 
                    "en" => "English"), 
                    "Default" => "es", 
                    "Description" => traduccionEpayco($idioma, "epayco_config_6")
            )
        )
    );
}
function epayco_activate()
{
    $obj = new EpaycoConfig();
    $idioma = $obj->checkIdioma();
    try {
        $obj->crearTablaCustomTransacciones();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    return array("status" => "success", "description" => traduccionEpayco($idioma, "epayco_activate_1"));
}
function epayco_deactivate()
{    
    $obj = new EpaycoConfig();
    $idioma = $obj->checkIdioma();
    $obj->eliminarTablaCustomTransacciones();
    return array("status" => "success", "description" => traduccion($idioma, "epayco_deactivate_1"));
}

?>
