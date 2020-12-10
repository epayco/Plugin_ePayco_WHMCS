<?php

class EnumTransaccion {
    const Aceptada = 1;
    const Rechazada = 2;
    const Pendiente = 3;
    const Fallida = 4;
}

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayParams = getGatewayVariables('epayco');

if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

$async = true;
if(!empty($_GET['ref_payco'])){
    $responseData = @file_get_contents('https://api.secure.payco.co/validation/v1/reference/'.$_GET['ref_payco']);
    if($responseData === false){
        logTransaction($gatewayParams['name'], $_GET, 'Ocurrio un error al intentar validar la referencia');
        header("Location: ".$gatewayParams['systemurl']);
    }
    $jsonData = @json_decode($responseData, true);
    if($jsonData === false){
        logTransaction($gatewayParams['name'], $_GET, 'El formato de la respuesta de validación no es correcto');
        header("Location: ".$gatewayParams['systemurl']);
    }
    $validationData = $jsonData['data'];
    $async = false;
}else {
    $validationData = $_GET;
}

if (!empty(trim($_POST['x_ref_payco']))) {
      $responseData = @file_get_contents('https://secure.payco.co/pasarela/estadotransaccion?id_transaccion='.trim($_POST['x_ref_payco']));
          if($responseData === false){
        logTransaction($gatewayParams['name'], $_GET, 'Ocurrio un error al intentar validar la referencia');
        header("Location: ".$gatewayParams['systemurl']);
    }
    $jsonData = @json_decode($responseData, true);
      if($jsonData === false){
        logTransaction($gatewayParams['name'], $_POST, 'El formato de la respuesta de validación no es correcto');
        header("Location: ".$gatewayParams['systemurl']);
    }
     $validationData = $jsonData['data'];
     $async = false;
}

//$checkTransId=checkCbTransID($validationData['x_ref_payco']);
$invoiceid = checkCbInvoiceID($validationData['x_id_invoice'],$gatewayParams['name']);

$invoice = localAPI("getinvoice", array('invoiceid' => $validationData['x_id_invoice']), $gatewayParams['WHMCSAdminUser']);



if($invoice['status'] == 'error'){
    logTransaction($gatewayParams['name'], $validationData, $invoice['message']);
    if($async){
        exit(0);
    }else {
        header("Location: ".$gatewayParams['systemurl'].'/viewinvoice.php?id='.$validationData['x_id_invoice'].'&paymentfailed=true');
    }
}

if($invoice['status'] != 'Unpaid'){
    if($async){
        exit(0);
    }else {
        header("Location: ".$gatewayParams['systemurl']);
    }
}

$signature = hash('sha256',
    $gatewayParams['customerID'].'^'
    .$gatewayParams['privateKey'].'^'
    .$validationData['x_ref_payco'].'^'
    .$validationData['x_transaction_id'].'^'
    .$validationData['x_amount'].'^'
    .$validationData['x_currency_code']
);

if($signature == $validationData['x_signature']){
         switch ((int)$validationData['x_cod_response']) {
        case EnumTransaccion::Aceptada:{
            if($invoice['status'] != 'Paid'){
            addInvoicePayment(
                $invoice['invoiceid'],
                $validationData['x_ref_payco'],
                $invoice['total'],
                null,
                $gatewayParams['paymentmethod']
            );
            logTransaction($gatewayParams['name'], $validationData, "Aceptada");
        }
            if(!$async){
                header("Location: ".$gatewayParams['systemurl'].'/viewinvoice.php?id='.$validationData['x_id_invoice'].'&paymentsuccess=true');
            }
        }break;
        case EnumTransaccion::Rechazada:{
            logTransaction($gatewayParams['name'], $validationData, "Rechazado");
            if(!$async){
                header("Location: ".$gatewayParams['systemurl'].'/viewinvoice.php?id='.$validationData['x_id_invoice'].'&paymentfailed=true');
            }
        }break;
        case EnumTransaccion::Pendiente:{
            logTransaction($gatewayParams['name'], $validationData, "Pendiente");
            if(!$async){
                header("Location: ".$gatewayParams['systemurl'].'/viewinvoice.php?id='.$validationData['x_id_invoice'].'&pendingreview=true');
            }
        }break;
        case EnumTransaccion::Fallida:{
            logTransaction($gatewayParams['name'], $validationData, "Fallida");
            if(!$async){
                header("Location: ".$gatewayParams['systemurl'].'/viewinvoice.php?id='.$validationData['x_id_invoice'].'&paymentfailed=true');
            }
        }break;
    }
}else{
    logTransaction($gatewayParams['name'], $_GET, 'Firma no válida');
    if(!$async){
        header("Location: ".$gatewayParams['systemurl']);
    }
}