<?php


#Required File Includes
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewaymodule = "payco"; 
$GATEWAY       = getGatewayVariables($gatewaymodule);

#se reciben las variables de respuesta
$x_fecha_transaccion    = $_REQUEST["x_fecha_transaccion"];
$x_cod_respuesta        = $_REQUEST["x_cod_respuesta"];
$x_respuesta            = $_REQUEST["x_respuesta"];
$x_response_reason_text = $_REQUEST["x_response_reason_text"];
$x_id_factura           = $_REQUEST["x_id_factura"];
$x_ref_payco            = $_REQUEST["x_ref_payco"];
$x_amount               = $_REQUEST["x_amount"];
$x_currency_code        = $_REQUEST["x_currency_code"];
$x_franchise            = $_REQUEST["x_franchise"];
$x_extra1               = $_REQUEST["x_extra1"];
$x_extra2               = $_REQUEST["x_extra2"];
$x_extra3               = $_REQUEST["x_extra3"];

$invoicearr  = explode(':', $x_id_factura);
$invoiceint  = (int)end($invoicearr);  

#Checks invoice ID is a valid invoice number or ends processing
$invoiceid = checkCbInvoiceID($invoiceint,$GATEWAY["name"]); 

#Checks transaction number isn't already in the database and ends processing if it does

$checkTransId=checkCbTransID($x_ref_payco); 
$fee=0;

$invoice = localAPI("getinvoice", array('invoiceid' => $_REQUEST['x_id_invoice']), $GATEWAY["name"]);

if ($x_respuesta=="Aceptada") {
    if($invoice['status'] != 'Paid'){
    #Successful: Save to Gateway Log: name, data array, status        
    addInvoicePayment($invoiceid,$x_ref_payco,$amount,$fee,$gatewaymodule); 
    logTransaction($GATEWAY["name"],$_POST,"Successful"); 
    }
} else {

    #Unsuccessful : Save to Gateway Log: name, data array, status
    logTransaction($GATEWAY["name"],$_POST,"Unsuccessful");
}

#redirect 

header('Location: '.$GATEWAY["systemurl"].'/viewinvoice.php?id='.$invoiceid);

    
?>