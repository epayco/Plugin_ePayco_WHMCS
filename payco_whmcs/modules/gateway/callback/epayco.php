<?php
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
use Illuminate\Database\Capsule\Manager as Capsule;

$gatewayParams = getGatewayVariables('epayco');

if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

$confirmation = false;
$async = true;
if(!empty($_GET['ref_payco'])){
    $responseData = @file_get_contents('https://secure.epayco.io/validation/v1/reference/'.$_GET['ref_payco']);
    if($responseData === false){
        logTransaction($gatewayParams['name'], $_GET, 'Ocurrio un error al intentar validar la referencia');
        header("Location: ".$gatewayParams['systemurl']);
    }
    $jsonData = @json_decode($responseData, true);

    if($jsonData["status"] === false){
        logTransaction($gatewayParams['name'], $_GET, 'El formato de la respuesta de validación no es correcto');
        header("Location: ".$gatewayParams['systemurl']);
    }
    $validationData = $jsonData['data'];
    $async = false;
}else {
    $validationData = $_GET;
}

if (!empty(trim($_POST['x_ref_payco']))) {
     $validationData = $_POST;
     $async = false;
     $confirmation= true;
}

$invoiceid = checkCbInvoiceID($validationData['x_extra1'],$gatewayParams['name']);

$invoice = localAPI("getinvoice", array('invoiceid' => $validationData['x_extra1']), $gatewayParams['WHMCSAdminUser']);

if($invoice['status'] == 'error'){
    logTransaction($gatewayParams['name'], $validationData, $invoice['message']);
    if($async){
        exit(0);
    }else {
        if(!$confirmation){
            header("Location: ".$gatewayParams['systemurl'].'/viewinvoice.php?id='.$validationData['x_extra1'].'&paymentfailed=true');
        }
    }
}

if($invoice['status'] != 'Unpaid'){
    if($async){
        exit(0);
    }else {
        if(!$confirmation){
            header("Location: ".$gatewayParams['systemurl']);
        }
    }
}

$invoiceData = Capsule::table('tblorders')
            ->select('tblorders.amount')
            ->where('tblorders.invoiceid', '=', $validationData['x_extra1'])
            ->get();
$invoiceAmount = $invoiceData[0]->amount;
$x_test_request= $validationData['x_test_request'];
$isTestTransaction = $x_test_request == 'TRUE' ? "yes" : "no";
$isTestMode = $isTestTransaction == "yes" ? "true" : "false";
$isTestPluginMode = $gatewayParams["testMode"] == "on" ? "yes": "no";
$x_amount= $validationData['x_amount'];
if(floatval($invoiceAmount) === floatval($x_amount)){
    if("yes" == $isTestPluginMode){
        $validation = true;
    }
    if("no" == $isTestPluginMode ){
        if($x_approval_code != "000000" && $x_cod_transaction_state == 1){
            $validation = true;
        }else{
            if($x_cod_transaction_state != 1){
                $validation = true;
            }else{
                $validation = false;
            }
        }
                        
    }
}else{
    $validation = false;
}

$data = Capsule::table('tbladmins')
            ->join('tbladminroles', 'tbladmins.roleid', '=', 'tbladmins.roleid')
            ->join('tbladminperms', 'tbladminroles.id', '=', 'tbladminperms.roleid')
            ->select('tbladmins.username')
            ->where('tbladmins.disabled', '=', 0)
            ->where('tbladminperms.permid', '=', 81)
            ->get();
$command = 'CancelOrder';
$postData = array(
    'orderid' => $validationData['x_extra1'],
);
$adminUsername = $data[0]->username;

$signature = hash('sha256',
    $gatewayParams['customerID'].'^'
    .$gatewayParams['privateKey'].'^'
    .$validationData['x_ref_payco'].'^'
    .$validationData['x_transaction_id'].'^'
    .$validationData['x_amount'].'^'
    .$validationData['x_currency_code']
);

if($signature == $validationData['x_signature'] && $validation){
    switch ((int)$validationData['x_cod_response']) {
        case 1:{
            if($invoice['status'] != 'Paid'){
            addInvoicePayment(
                $invoice['invoiceid'],
                $validationData['x_ref_payco'],
                $invoice['total'],
                null,
                $gatewayParams['paymentmethod']
            );
            logTransaction($gatewayParams['name'], $validationData, "Aceptada");
            $results = localAPI('AcceptOrder', $postData, $adminUsername);
        }
            if(!$async){
                $returnUrl = $gatewayParams['systemurl'].'modules/gateways/epayco/epayco.php';
                if(!$confirmation){
                    header("Location: ".$returnUrl.'?ref_payco='.$_GET['ref_payco']);
                }
            }
             echo "1: ";
        }break;
        case 2:{
            logTransaction($gatewayParams['name'], $validationData, "Cancelled");
            if($invoice['status'] != 'Cancelled'){
                $results = localAPI($command, $postData, $adminUsername);
            }
            if(!$async){
                if($confirmation){
                    echo "2: ";
                }else{
                    $returnUrl = $gatewayParams['systemurl'].'modules/gateways/epayco/epayco.php';
                    if(!$confirmation){
                        header("Location: ".$returnUrl.'?ref_payco='.$_GET['ref_payco']);
                    }
                }
            }
        }break;
        case 3:{
            $returnUrl = $gatewayParams['systemurl'].'modules/gateways/epayco/epayco.php';
            if(!$confirmation){
                header("Location: ".$returnUrl.'?ref_payco='.$_GET['ref_payco']);
            }else{
                $results = localAPI('PendingOrder', $postData, $adminUsername);
            }
            echo "3: ";
        }break;
        case 4:{
            logTransaction($gatewayParams['name'], $validationData, "Failure");
            if($invoice['status'] != 'Cancelled'){
                $results = localAPI($command, $postData, $adminUsername);
            }
            if(!$async){
                if($confirmation){
                    echo "Fallida: ";
                }else{
                     $returnUrl = $gatewayParams['systemurl'].'modules/gateways/epayco/epayco.php';
                    if(!$confirmation){
                        header("Location: ".$returnUrl.'?ref_payco='.$_GET['ref_payco']);
                    }
                }
            }
        }break;
        case 6:{
            logTransaction($gatewayParams['name'], $validationData, "Failure");
            if($invoice['status'] != 'Cancelled'){
                $results = localAPI($command, $postData, $adminUsername);
            }
            if(!$async){
                if($confirmation){
                    echo "Fallida: ";
                }else{
                     $returnUrl = $gatewayParams['systemurl'].'modules/gateways/epayco/epayco.php';
                    if(!$confirmation){
                        header("Location: ".$returnUrl.'?ref_payco='.$_GET['ref_payco']);
                    }
                }
            }
        }break;
        case 10:{
            logTransaction($gatewayParams['name'], $validationData, "Failure");
            if($invoice['status'] != 'Cancelled'){
                $results = localAPI($command, $postData, $adminUsername);
            }
            if(!$async){
                if($confirmation){
                    echo "10: ";
                }else{
                     $returnUrl = $gatewayParams['systemurl'].'modules/gateways/epayco/epayco.php';
                    if(!$confirmation){
                        header("Location: ".$returnUrl.'?ref_payco='.$_GET['ref_payco']);
                    }
                }
            }
        }break;
        case 11:{
            logTransaction($gatewayParams['name'], $validationData, "Failure");
            if($invoice['status'] != 'Cancelled'){
                $results = localAPI($command, $postData, $adminUsername);
            }
            if(!$async){
                if($confirmation){
                    echo "11: ";
                }else{
                     $returnUrl = $gatewayParams['systemurl'].'modules/gateways/epayco/epayco.php';
                    if(!$confirmation){
                        header("Location: ".$returnUrl.'?ref_payco='.$_GET['ref_payco']);
                    }
                }
            }
        }break;
    }
}else{
    logTransaction($gatewayParams['name'], $validationData, 'Firma no valida');

    if($invoice['status'] != 'Cancelled'){
        $results_ = localAPI('PendingOrder', $postData, $adminUsername);
        if($results_["result"] != "error"){
        $results = localAPI($command, $postData, $adminUsername);
        }
    }
    if(!$async){
        if($confirmation){
            echo "Firma no valida. ";
        }else{
            header("Location: ".$gatewayParams['systemurl']);
        }
    }
}

if($results["result"] == "error"){
    echo $results["result"].": ".$results["message"];
}else{
   echo $results["result"]; 
}