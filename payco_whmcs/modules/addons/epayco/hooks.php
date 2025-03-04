<?php
include ROOTDIR . DIRECTORY_SEPARATOR . "modules/gateways/epayco/epayco.php";
add_hook("AfterCronJob", 1, function ($vars) {
    $obj = new EpaycoConfig();
    $obj->procesarTodosRegistrosCallback(10);
});
?>