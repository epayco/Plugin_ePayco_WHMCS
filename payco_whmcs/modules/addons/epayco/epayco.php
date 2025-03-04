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
        "description" => traduccion($idioma, "epayco_config_1"), 
        "author" => "Epayco", 
        "language" => "spanish", 
        "version" => "17.3", 
        "fields" => array(
            "licencia" => array(
                "FriendlyName" => traduccion($idioma, "epayco_config_2"), 
                "Type" => "text", 
                "Size" => "25", 
                "Description" => ""), 
            "verificador" => array(
                "FriendlyName" => traduccion($idioma, "epayco_config_3"), 
                "Type" => "textarea", 
                "Rows" => "6", 
                "Cols" => "60", 
                "Description" => traduccion($idioma, "epayco_config_4")), 
            "idioma" => array(
                "FriendlyName" => traduccion($idioma, "epayco_config_5"), 
                "Type" => "dropdown", 
                "Options" => array(
                    "es" => "Espa単ol", 
                    "en" => "English"), 
                    "Default" => "es", 
                    "Description" => traduccion($idioma, "epayco_config_6")
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
    return array("status" => "success", "description" => traduccion($idioma, "epayco_activate_1"));
}
function epayco_deactivate()
{    
    $obj = new EpaycoConfig();
    $idioma = $obj->checkIdioma();
    $obj->eliminarTablaCustomTransacciones();
    return array("status" => "success", "description" => traduccion($idioma, "epayco_deactivate_1"));
}

?>