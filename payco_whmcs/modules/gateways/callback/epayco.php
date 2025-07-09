<?php
include "../../../init.php";
include ROOTDIR . "/includes/functions.php";
include ROOTDIR . "/includes/gatewayfunctions.php";
include ROOTDIR . "/includes/invoicefunctions.php";
use Illuminate\Database\Capsule\Manager as Capsule;
require_once(ROOTDIR . "/modules/gateways/epayco/epayco.php");
$gatewayModule = "epayco";
$gateway = new WHMCS\Module\Gateway();

if (!$gateway->isActiveGateway($gatewayModule) || !$gateway->load($gatewayModule)) {
    WHMCS\Terminus::getInstance()->doDie("Module not Active");
}
$GATEWAY = $gateway->getParams();
$gatewayParams = getGatewayVariables('epayco');

if (!$gatewayParams['type']) {
    die("Module Not Activated");
}
$obj = new EpaycoConfig("Epayco",$gatewayModule);
if(!empty($_GET['ref_payco'])){
    $responseData = @file_get_contents('https://eks-checkout-service.epayco.io/validation/v1/reference/'.$_GET['ref_payco']);
    if($responseData === false){
        logTransaction($gatewayParams['name'], $_GET, 'Ocurrio un error al intentar validar la referencia');
        header("Location: ".$gatewayParams['systemurl']);
    }
    if($jsonData["status"] === false){
        $responseData = @file_get_contents('https://eks-ms-checkout-response-transaction-service.epayco.io/checkout/history?historyId='.$_GET['ref_payco']);
        if($responseData === false){
            logTransaction($gatewayParams['name'], $_GET, 'Ocurrio un error al intentar validar la referencia');
            header("Location: ".$gatewayParams['systemurl']);
        }
        $jsonData = @json_decode($responseData, true);
        $validationData = $jsonData['data'];
        $validationData['x_extra1'] = $validationData['storeReference'];
        $validationData['x_ref_payco'] = $validationData['ePaycoID'];
        $validationData['x_amount'] = $validationData['total'];
        $validationData['x_currency_code'] = 'COP';
        $validationData['x_transaction_id'] = $validationData['storeReference'];
        $validationData['x_signature'] = 'Authorized';
        
    }else{
    $jsonData = @json_decode($responseData, true);
    $validationData = $jsonData['data'];
    }

    $obj->crearTablaCustomTransacciones();
    $respuesta = $obj->epaycoConfirmation($GATEWAY, $validationData);
    $returnUrl = $gatewayParams['systemurl'].'modules/gateways/epayco/response.php';
    header("Location: ".$returnUrl.'?ref_payco='.$_GET['ref_payco']);
}else {
    if (!empty(trim($_REQUEST['x_ref_payco']))) {
        $validationData = $_REQUEST;
        $obj->crearTablaCustomTransacciones();
        $respuesta = $obj->epaycoConfirmation($GATEWAY,$validationData,true);
        
        header("HTTP/1.1 " . $respuesta);
        exit("Callback completo: " . var_export($respuesta,1));
    }
}


