<?php
use WHMCS\Carbon;

include ROOTDIR . DIRECTORY_SEPARATOR . "modules/gateways/epayco/epayco.php";
add_hook("AfterCronJob", 1, function ($vars) {
    $obj = new EpaycoConfig();
    $now = Carbon::now();
    $lastRunFile = __DIR__ . '/last_run_time.txt';

    $lastRun = 0;
    if (file_exists($lastRunFile)) {
        $lastRun = (int) file_get_contents($lastRunFile);
    }

    if (($now->timestamp - $lastRun) >= 300) { // 300 segundos = 5 minutos
        file_put_contents($lastRunFile, $now->timestamp);

        // Ejecutar tu función personalizada
        $obj->procesarTodosRegistrosCallback(10);
        logActivity("ePayco cron Ejecución automática cada 5 minutos ");
        logModuleCall(
            'epayco',
            'cron_each_5_min',
            'Ejecución automática cada 5 minutos',
            date('Y-m-d H:i:s'),
            'success'
        );
        
    }
});
?>