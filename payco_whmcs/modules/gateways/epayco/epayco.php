<?php
use Illuminate\Database\Capsule\Manager as Capsule;
require_once(__DIR__ . "/idioma.php");
class EpaycoConfig
{ 
    public $nombreModulo;
    public $modulo;
    public function __construct($nombreModulo = "epayco",$modulo = "epayco")
    {
        $this->nombreModulo = $nombreModulo;
        $this->modulo = $modulo;
    }
    function crearTablaCustomTransacciones()
    {
        $nombreTabla = "bapp_epayco";
        if (!WHMCS\Database\Capsule::schema()->hasTable($nombreTabla)) {
            try {
                WHMCS\Database\Capsule::schema()->create($nombreTabla, function ($table) {
                    $table->increments("id");
                    $table->string("transaccion")->unique();
                    $table->dateTime("momento");
                    $table->string("gateway");
                });
                return true;
            } catch(\Exception $ex) {
                throw new Exception("No se pudo crear la tabla de transacciones: " . $ex->getMessage());
            }
        }
        return false;
    }
    function eliminarTablaCustomTransacciones()
    {
        $nombreTabla = "bapp_epayco";
        if (WHMCS\Database\Capsule::schema()->hasTable($nombreTabla)) {
            try {
                WHMCS\Database\Capsule::schema()->dropIfExists($nombreTabla);
                return true;
            } catch(\Exception $ex) {
                throw new Exception("No se pudo eliminar la tabla de transacciones: " . $ex->getMessage());
            }
        }
        return false;
    }
    function checkIdioma()
    {
        $resultado = Capsule::table("tbladdonmodules")->where("module", "=", "epayco")->where("setting", "=", "idioma")->get();
        $resultado = $resultado[0]->value;
        if (empty($resultado)) {
            $resultado = "es";
        }
        return $resultado;
    }    
    
    function getPreferenciaPago($accesstoken, $datos_mp, $prueba = false)
    {
        $userid = substr(strrchr($accesstoken, "-"), 1);
        $uri = "https://api.mercadopago.com/checkout/preferences/";
        $data = array("additional_info" => "", "auto_return" => $datos_mp["retorno"], "back_urls" => array("failure" => $datos_mp["url_fallo"], "pending" => $datos_mp["url_pendiente"], "success" => $datos_mp["url_exito"]), "binary_mode" => true, "merchant_account_id" => $datos_mp["merchant_account_id"], "processing_modes" => array($datos_mp["processing"]), "processing_mode" => $datos_mp["processing"], "external_reference" => $datos_mp["referencia"], "items" => array(array("id" => "", "currency_id" => $datos_mp["item_moneda"], "title" => $datos_mp["item_titulo"], "picture_url" => $datos_mp["item_imagen"], "description" => $datos_mp["item_descripcion"], "category_id" => "services", "quantity" => 1, "unit_price" => $datos_mp["item_precio"])), "notification_url" => $datos_mp["notification_url"], "payer" => array("phone" => array("area_code" => $datos_mp["comprador_telefono_codigodearea"], "number" => $datos_mp["comprador_telefono_numero"]), "address" => array("zip_code" => $datos_mp["comprador_domicilio_codigopostal"], "street_name" => $datos_mp["comprador_domicilio_calle"], "street_number" => $datos_mp["comprador_domicilio_numero"]), "identification" => array("number" => $datos_mp["comprador_documento_numero"], "type" => $datos_mp["comprador_documento_tipo"]), "email" => $datos_mp["comprador_email"], "name" => $datos_mp["comprador_nombre"], "surname" => $datos_mp["comprador_apellido"]), "payment_methods" => array("excluded_payment_types" => $datos_mp["exclusiones"]));
        $url = "https://api.mercadopago.com/checkout/preferences/?access_token=" . $accesstoken;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Length: " . strlen(json_encode($data))));
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result, true);
        return $response;
    }
    function getConfigModulo()
    {
        $modulo = $this->modulo;
        $nombre = $this->nombreModulo;
        $idioma = $this->checkIdioma();
        $configHeader = array(
                "FriendlyName" => array(
                    "Type" => "System", 
                    "Value" => $nombre
                ), 
                'customerID' => array(
                    'FriendlyName' => 'P_CUST_ID_CLIENTE',
                    'Type' => 'text',
                    'Size' => '32',
                    'Default' => '',
                    'Description' => '<br/>'.traduccionEpayco($idioma, "epconfig_1"),
                ),
                'publicKey' => array(
                    'FriendlyName' => 'PUBLIC_KEY',
                    'Type' => 'text',
                    'Size' => '32',
                    'Default' => '',
                    'Description' => '<br/>'.traduccionEpayco($idioma, "epconfig_2"),
                ),
                'privateKey' => array(
                    'FriendlyName' => 'PRIVATE_KEY',
                    'Type' => 'text',
                    'Size' => '32',
                    'Default' => '',
                    'Description' => '<br/>'.traduccionEpayco($idioma, "epconfig_2"),
                ),
                'p_key' => array(
                    'FriendlyName' => 'P_KEY',
                    'Type' => 'text',
                    'Size' => '32',
                    'Default' => '',
                    'Description' => '<br/>'.traduccionEpayco($idioma, "epconfig_3"),
                ),
                /*'countryCode' => array(
                    'FriendlyName' => traduccionEpayco($idioma, "epconfig_4"),
                    'Type' => 'dropdown',
                    'Options' => $this->epayco_loadCountries(),
                    'Description' => traduccionEpayco($idioma, "epconfig_5"),
                ),*/
                'currencyCode' => array(
                    'FriendlyName' => traduccionEpayco($idioma, "epconfig_6"),
                    'Type' => 'dropdown',
                    'Options' => array(
                        'default' => traduccionEpayco($idioma, "epconfig_7"),
                        'cop' => traduccionEpayco($idioma, "epconfig_8"),
                        'usd' => traduccionEpayco($idioma, "epconfig_9")
                    ),
                    'Description' => '<br/>'.traduccionEpayco($idioma, "epconfig_10"),
                ),
                'lang' => array(
                    'FriendlyName' => traduccionEpayco($idioma, "epconfig_11"),
                    'Type' => 'dropdown',
                    'Options' => array(
                        'es' => traduccionEpayco($idioma, "epconfig_12"),
                        'en' => traduccionEpayco($idioma, "epconfig_13")
                    ),
                    'Description' => '<br/>'.traduccionEpayco($idioma, "epconfig_14"),
                ),
                'testMode' => array(
                    'FriendlyName' => traduccionEpayco($idioma, "epconfig_15"),
                    'Type' => 'yesno',
                    'Description' => traduccionEpayco($idioma, "epconfig_16"),
                ),
                'externalMode' => array(
                    'FriendlyName' => 'Standar checkout',
                    'Type' => 'yesno',
                    'Description' => traduccionEpayco($idioma, "epconfig_17"),
                ),
                "bh_texto" => array(
                    "FriendlyName" => "" . traduccionEpayco($idioma, "epconfig_22") . "", 
                    "Type" => "text", 
                    "Value" => traduccionEpayco($idioma, "epconfig_23")
                ),
                "bh_nota" => array(
                    "FriendlyName" => traduccionEpayco($idioma, "epconfig_20") . ":", 
                    "Type" => "text", 
                    "Size" => "100", 
                    "Description" => "<br>" . traduccionEpayco($idioma, "epconfig_21")
                ),
                "color" => array(
                    "FriendlyName" => traduccionEpayco($idioma, "epconfig_24") . ":", 
                    "Type" => "dropdown", 
                    "Options" => array(
                        "primary" => traduccionEpayco($idioma, "epconfig_25"), 
                        "secondary" => traduccionEpayco($idioma, "epconfig_26"), 
                        "success" => traduccionEpayco($idioma, "epconfig_27"), 
                        "danger" => traduccionEpayco($idioma, "epconfig_28"), 
                        "warning" => traduccionEpayco($idioma, "epconfig_29"), 
                        "info" => traduccionEpayco($idioma, "epconfig_30"), 
                        "light" => traduccionEpayco($idioma, "epconfig_31"), 
                        "dark" => traduccionEpayco($idioma, "epconfig_32"), 
                        "link" => traduccionEpayco($idioma, "epconfig_33")
                    ), "Description" => traduccionEpayco($idioma, "epconfig_34")
                  ),
                  "bh_modocolaprocesamiento" => array(
                    "FriendlyName" => traduccionEpayco($idioma, "epconfig_35") . ":", 
                    "Type" => "yesno", 
                    "Description" => traduccionEpayco($idioma, "epconfig_36")
                )
            );
        return $configHeader;
    }
    
    function getLinkPago($params)
    {
        $companyname = $params["companyname"];
        $systemurl = $params["systemurl"];
        $bh_texto = $params["bh_texto"];
        $bh_nota = $params["bh_nota"];
        $color = $params["color"];
        $nota = "";
        if (!empty($bh_nota)) {
            $nota = "<p><div class='small-text'>" . $bh_nota . "</div></p>";
        }

        if ($params["testMode"] == "on") {
            $mododeprueba = true;
        } else {
            $mododeprueba = false;
        }
      

        $bh_success = $params["bh_success"];
        $bh_pending = $params["bh_pending"];
        $bh_failure = $params["bh_failure"];
        $bh_error_mp = $params["bh_error_mp"];
        if (empty($bh_success)) {
            $bh_success = $systemurl . "viewinvoice.php?id=" . $params["invoiceid"];
        }
        if (empty($bh_pending)) {
            $bh_pending = $systemurl . "viewinvoice.php?id=" . $params["invoiceid"];
        }
        if (empty($bh_failure)) {
            $bh_failure = $systemurl . "viewinvoice.php?id=" . $params["invoiceid"];
        }

        
        $countryCode = 'CO';
        $firstname = $params['clientdetails']['firstname'];
        $lastname = $params['clientdetails']['lastname'];
        $email = $params['clientdetails']['email'];
        $address1 = $params['clientdetails']['address1'];
        $returnUrl = $params['returnurl'];
        $billing_name = $firstname." ".$lastname;
        if($params['currencyCode'] == 'default'){
            $clientDetails = localAPI("getclientsdetails", ["clientid" => $params['clientdetails']['userid'], "responsetype" => "json"], $params['WHMCSAdminUser']);
            $currencyCode = strtolower($clientDetails['currency_code']);
        }else {
            $currencyCode = $params['currencyCode'];
        }
    
        $testMode = $params['testMode'] == 'on' ? 'true' : 'false';
    
        $externalMode = $params['externalMode'] == 'on' ? 'true' : 'false';
    
        $invoice = localAPI("getinvoice", array('invoiceid' => $params['invoiceid']), $params['WHMCSAdminUser']);
        $invoiceData = Capsule::table('tblorders')
            ->select('tblorders.id')
            ->where('tblorders.invoiceid', '=', $params['invoiceid'])
            ->get();
    
        $description = $this->epayco_getChargeDescription($invoice['items']['item']);
        if(floatval($invoice["subtotal"]) > 0.0 ){
            $tax=floatval($invoice["tax"]);
            $sub_total = floatval($invoice["subtotal"]);
            $amount = floatval($invoice["total"]);
        }else{
            $tax="0";
            $sub_total = $params["amount"];
            $amount = $params["amount"];
        }
    
        $confirmationUrl = $systemurl . "modules/gateways/callback/" . $params["paymentmethod"] . "_ipn.php?source_news=webhooks";
        $lang = $params['lang'];
        if ($lang === "en") {
            $epaycoButtonImage = 'https://multimedia.epayco.co/epayco-landing/btns/Boton-epayco-color-Ingles.png';
        }else{
            $epaycoButtonImage = 'https://multimedia.epayco.co/epayco-landing/btns/Boton-epayco-color1.png';
        }
        $ip=$this->getCustomerIp(); 
        $logo = $params['systemurl'].'/modules/gateways/epayco/logo.png';
        $code = "<script src='https://epayco-checkout-testing.s3.amazonaws.com/checkout.preprod.js'></script> <img src=" . $logo . " /><br><a href='" . $enlace . "' class='btn btn-" . $color . "'>" . $bh_texto . "</a>" . $nota;
        $code = sprintf('
            <p>       
                <center>
                <a id="btn_epayco" href="#">
                    <img src="'.$epaycoButtonImage.'">
                </a>
                </center> 
            </p>
            <script
                src="https://checkout.epayco.co/checkout.js">
            </script>
            <script>
                var handler = ePayco.checkout.configure({
                        key: "%s",
                        test: "%s"
                    })
                var data = {
                    amount: "%s".toString(),
                    tax_base: "%s".toString(),
                    tax: "%s".toString(),
                    name: "%s",
                    description: "%s",
                    currency: "%s",
                    test: "%s".toString(),
                    invoice: "%s",
                    country: "%s",
                    response: "%s",
                    confirmation: "%s",
                    external: "%s",
                    email_billing: "%s",
                    name_billing: "%s",
                    address_billing: "%s",
                    extra1: "%s",
                    extra2: "%s",
                    lang: "%s",
                    ip: "%s",
                    taxIco: "0".toString(),
                    autoclick: "true",
                    extras_epayco:{extra5:"P34"}
                }
                const apiKey = "%s";
                const privateKey = "%s";
                var openNewChekout = function () {
                    if(localStorage.getItem("invoicePayment") == null){
                        localStorage.setItem("invoicePayment", data.invoice);
                        makePayment(privateKey,apiKey,data, data.external == "true"?true:false)
                    }else{
                        if(localStorage.getItem("invoicePayment") != data.invoice){
                            localStorage.removeItem("invoicePayment");
                            localStorage.setItem("invoicePayment", data.invoice);
                            makePayment(privateKey,apiKey,data, data.external == "true"?true:false)
                        }else{
                            makePayment(privateKey,apiKey,data, data.external == "true"?true:false)
                        }
                    }
                }
                var makePayment = function (privatekey, apikey, info, external) {
                    const headers = { "Content-Type": "application/json" } ;
                    headers["privatekey"] = privatekey;
                    headers["apikey"] = apikey;
                    var payment =   function (){
                        return  fetch("https://cms.epayco.co/checkout/payment/session", {
                            method: "POST",
                            body: JSON.stringify(info),
                            headers
                        })
                            .then(res =>  res.json())
                            .catch(err => err);
                    }
                    payment()
                        .then(session => {
                            if(session.data.sessionId != undefined){
                                localStorage.removeItem("sessionPayment");
                                localStorage.setItem("sessionPayment", session.data.sessionId);
                                const handlerNew = window.ePayco.checkout.configure({
                                    sessionId: session.data.sessionId,
                                    external: external,
                                });
                                handlerNew.openNew()
                            }else{
                                handler.open(data);
                            }
                        })
                        .catch(error => {
                            error.message;
                        });
                }
                var openChekout = function () {
                    //handler.open(data);
                    //openNewChekout()
                    console.log(data)
                }
                var bntPagar = document.getElementById("btn_epayco");
                bntPagar.addEventListener("click", openChekout);
                //openChekout()
                window.onload = function() {
                    document.addEventListener("contextmenu", function(e){
                        e.preventDefault();
                    }, false);
                } 
                $(document).keydown(function (event) {
                    if (event.keyCode == 123) {
                        return false;
                    } else if (event.ctrlKey && event.shiftKey && event.keyCode == 73) {        
                        return false;
                    }
                });
            </script>
        </form>
        %s
    ',  
        $params['publicKey'],
        $testMode,
        $amount,
        $sub_total,
        $tax,
        $description, 
        $description,
        strtolower($currencyCode), 
        $testMode, 
        $params['invoiceid'], 
        $countryCode, 
        $confirmationUrl, 
        $confirmationUrl, 
        $externalMode, 
        $email, 
        $billing_name, 
        $address1,
        $params['invoiceid'],
        $invoiceData[0]->id,
        $lang,
        $ip,
        $params['publicKey'],
        $params['privateKey'],
        $nota
    );
        return $code;
    }
    function epayco_getChargeDescription($invoceItems){
    $descriptions = array();
    foreach($invoceItems as $item){
        $descriptions[] = $item['description'];
    }

    return implode(' - ', $descriptions);
}

    function epaycoIPN($gatewayOBJ)
    {
        $gatewayModule = $this->modulo;
        $informe = json_decode(file_get_contents("php://input"), true);
        $informe_cobro = $informe["data"]["id"];
        $informe_action = $informe["action"];
        $informe_id = $informe["id"];
        $informe_type = $informe["type"];
        $email = $gatewayOBJ["email"];
        $modoProcesamientoPorColas = $gatewayOBJ["bh_modocolaprocesamiento"] == "on";
        $admin = $gatewayOBJ["useradmin"];
        if (!empty($admin)) {
            $adminUsername = $gatewayOBJ["useradmin"];
        }
        if (!empty($email)) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $verificamail = true;
            } else {
                $verificamail = false;
            }
        }
        if ($verificamail) {
            mail($email, $informe_id . " - Start", print_r($informe, true));
        }
        $command = "GetTransactions";
        $postData = array("transid" => $informe_cobro);
        $arr_transacciones = localAPI($command, $postData, $adminUsername);
        if ($arr_transacciones["totalresults"] == 0) {
            Capsule::table("bapp_epayco")->insert(array("transaccion" => $informe_cobro, "momento" => date("Y-m-d H:i:s"), "gateway" => $gatewayModule));
        }
        if (!$modoProcesamientoPorColas)
        {
            $this->callbackEpayco($informe_cobro);
        }        
        $retorno = "200";
        return $retorno;
    }
    function getPaymentEpayco($transaccion,$accessToken)
    {        
        $url = "https://api.mercadopago.com/v1/payments/" . $transaccion;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
        $result = curl_exec($ch);
        curl_close($ch);
        $respuestaParseada = json_decode($result, true);
        return $respuestaParseada;
    }
    function callbackEpayco($idtrans = "")
    {
        if (!empty($idtrans)) {
            $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $idtrans)->get();
            $mp_id = $resultado[0]->id;
            $mp_transaccion = $resultado[0]->transaccion;
            $mp_momento = $resultado[0]->momento;
            $mp_gateway = $resultado[0]->gateway;
        } else {
            $resultado = Capsule::table("bapp_epayco")->first();
            $mp_id = $resultado->id;
            $mp_transaccion = $resultado->transaccion;
            $mp_momento = $resultado->momento;
            $mp_gateway = $resultado->gateway;
        }
        if (!empty($mp_id)) {
            $log = "ID: " . $mp_id . "<br>Tran: " . $mp_transaccion . "<br>Time: " . $mp_momento . "<br>Gat: " . $mp_gateway;
            $GATEWAY = getGatewayVariables($mp_gateway);
            $admin = $GATEWAY["useradmin"];
            if (!empty($admin)) {
                $adminUsername = $GATEWAY["useradmin"];
            }
            $command = "GetTransactions";
            $postData = array("transid" => $mp_transaccion);
            $arr_transacciones = localAPI($command, $postData, $adminUsername);
            if ($arr_transacciones["totalresults"] == 0) {
                $email = $GATEWAY["email"];
                if (!empty($email)) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $verificamail = true;
                    } else {
                        $verificamail = false;
                    }
                }
                if ($verificamail) {
                    mail($email, "IPN Trans: " . $mp_transaccion, $log);
                }
                $datosdelpago = $this->getPaymentEpayco($mp_transaccion,$GATEWAY["bh_Access_Token"]);
                if ($verificamail) {
                    mail($email, "Search Trans. " . $mp_transaccion, print_r($datosdelpago, true));
                }
                $status = $datosdelpago["status"];
                $idioma = $this->checkIdioma();
                if ($status == "approved") {
                    $nrofactura = $datosdelpago["external_reference"];
                    if (!empty($nrofactura)) {
                        $moneda_de_cobro = $datosdelpago["currency_id"];
                        $comision = $datosdelpago["transaction_details"]["total_paid_amount"] - $datosdelpago["transaction_details"]["net_received_amount"];
                        $command = "GetInvoice";
                        $postData = array("invoiceid" => $nrofactura);
                        $arr_datos_factura = localAPI($command, $postData, $adminUsername);
                        $datos_factura = print_r($arr_datos_factura, true);
                        $usuario_id = $arr_datos_factura["userid"];
                        $balance = $arr_datos_factura["balance"];
                        if ($GATEWAY["bh_comportamiento"] != "normal") {
                            $importe_pagado = $balance;
                        } else {
                            $importe_pagado = $datosdelpago["transaction_details"]["total_paid_amount"];
                            $command = "GetClientsDetails";
                            $postData = array("clientid" => $usuario_id);
                            $arr_datos_cliente = localAPI($command, $postData, $adminUsername);
                            $moneda_code_usuario = $arr_datos_cliente["currency_code"];
                            if ($moneda_de_cobro != $moneda_code_usuario) {
                                $command = "GetCurrencies";
                                $postData = array();
                                $arr_listademonedas = localAPI($command, $postData, $adminUsername);
                                $monedero = $arr_listademonedas["currencies"]["currency"];
                                foreach ($monedero as $datosmoneda) {
                                    $moneda_code = $datosmoneda["code"];
                                    $monedasporcode[$moneda_code] = $datosmoneda["rate"];
                                }
                                $tasadeconversion = $monedasporcode[$moneda_code_usuario];
                                $ximporte_pagado = round($importe_pagado * $tasadeconversion, 2);
                                $xcomision = round($comision * $tasadeconversion, 2);
                                $importe_pagado = $ximporte_pagado;
                                $comision = $xcomision;
                                $conversionlog = traduccionEpayco($idioma, "mpconfig_73") . ": " . $moneda_code_usuario . "\r\n                            " . traduccionEpayco($idioma, "mpconfig_74") . ": " . $importe_pagado . "\r\n                            " . traduccionEpayco($idioma, "mpconfig_75") . ": " . $comision;
                            }
                        }
                        $command = "AddInvoicePayment";
                        $postData = array("gateway" => $GATEWAY["paymentmethod"], "invoiceid" => $nrofactura, "transid" => $mp_transaccion, "amount" => $importe_pagado, "fees" => $comision);
                        $results = localAPI($command, $postData, $adminUsername);
                        $texto_log = "\r\n                    " . traduccionEpayco($idioma, "mpconfig_56") . ": " . $nrofactura . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_57") . ": " . $GATEWAY["name"] . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_58") . ": " . $mp_transaccion . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_60") . ": " . $datosdelpago["authorization_code"] . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_61") . ": " . $datosdelpago["date_approved"] . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_62") . ": " . $datosdelpago["payment_type_id"] . " - " . $datosdelpago["payment_method_id"] . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_63") . ": " . $datosdelpago["currency_id"] . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_64") . ": " . $datosdelpago["transaction_details"]["total_paid_amount"] . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_65") . ": " . $datosdelpago["transaction_details"]["net_received_amount"] . "\r\n                    " . $conversionlog;
                        logTransaction($GATEWAY["name"], $texto_log, traduccionEpayco($idioma, "mpconfig_66") . " [" . $nrofactura . "]");
                        $resultado = Capsule::table("tbltransaction_history")->where("transaction_id", "=", $mp_transaccion)->delete();
                    }
                    $resultado = Capsule::table("bapp_epayco")->where("id", "=", $mp_id)->delete();
                } else {
                    if ($status == "pending") {
                        $command = "UpdateInvoice";
                        $postData = array("invoiceid" => $datosdelpago["external_reference"], "notes" => date("Y-m-d H:i:s") . ": " . traduccionEpayco($idioma, "mpconfig_76") . "\r\n                        [" . ucwords($datosdelpago["payment_type_id"]) . " - " . ucwords($datosdelpago["payment_method_id"]) . "]");
                        $results = localAPI($command, $postData, $adminUsername);
                        $texto_log = "\r\n                    " . traduccionEpayco($idioma, "mpconfig_56") . ": " . $datosdelpago["external_reference"] . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_57") . ": " . $GATEWAY["name"] . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_58") . ": " . $mp_transaccion . "\r\n                    " . traduccionEpayco($idioma, "mpconfig_62") . ": " . $datosdelpago["payment_type_id"] . " - " . $datosdelpago["payment_method_id"];
                        logTransaction($GATEWAY["name"], $texto_log, traduccionEpayco($idioma, "mpconfig_76") . " [" . $datosdelpago["external_reference"] . "]");
                        Capsule::table("tbltransaction_history")->insert(array("invoice_id" => $datosdelpago["external_reference"], "gateway" => $GATEWAY["name"], "updated_at" => date("Y-m-d H:i:s"), "transaction_id" => $mp_transaccion, "remote_status" => traduccionEpayco($idioma, "mpconfig_76"), "description" => $datosdelpago["payment_type_id"] . " - " . $datosdelpago["payment_method_id"]));
                    }
                    $resultado = Capsule::table("bapp_epayco")->where("id", "=", $mp_id)->delete();
                }
            }
            else
            {
                //BORRAR PORQUE YA FUE INGRESADA LA TRANSACCION
                $resultado = Capsule::table("bapp_epayco")->where("id", "=", $mp_id)->delete();
            }
        }
        return true;
    }
    function procesarTodosRegistrosCallback($limiteRegistros = 10)
    {
        $resultado = Capsule::table("bapp_epayco")->limit($limiteRegistros)->get();
        foreach ($resultado as $registro) {
            $this->callbackEpayco($registro->transaccion);
        }
    }
    function epayco_loadCountries()
    {
        $countriesJsonString = file_get_contents(__DIR__.'/../../resources/country/dist.countries.json');
        $countriesJson = json_decode($countriesJsonString);
        $countries = array();
        foreach($countriesJson as $code => $country){
            $countries[$code] = $country->name;
        }
    
        if(file_exists(__DIR__.'/../../resources/country/countries.json')){
            $customCountriesJsonString = file_get_contents(__DIR__.'/../../resources/country/countries.json');
            $customCountriesJson = json_decode($customCountriesJsonString);
            foreach($customCountriesJson as $code => $country){
    
                if($country === false){
                    unset($countries[$code]);
                    break;
                }
    
                $countries[$code] = $country->name;
            }
        }
    
        return $countries;
    }
    function getCustomerIp(){
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}
?>
