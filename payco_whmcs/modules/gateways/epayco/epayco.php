<?php
use Illuminate\Database\Capsule\Manager as Capsule;
require_once(__DIR__ . "/idioma.php");
use WHMCS\Exception;
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
                    $table->string("refPayco");
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
        $usersWithApiAccess = $this->epayco_getAdminUserWithApiAccess();

        $usersWithApiAccessArray = array();
        foreach($usersWithApiAccess as $userWithApiAccess){
            $usersWithApiAccessArray[$userWithApiAccess->username] = $userWithApiAccess->username;
        }
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
                /*"bh_texto" => array(
                    "FriendlyName" => "" . traduccionEpayco($idioma, "epconfig_22") . "", 
                    "Type" => "text", 
                    "Value" => traduccionEpayco($idioma, "epconfig_23")
                ),*/
                "bh_nota" => array(
                    "FriendlyName" => traduccionEpayco($idioma, "epconfig_20") . ":", 
                    "Type" => "text", 
                    "Size" => "100", 
                    "Description" => "<br>" . traduccionEpayco($idioma, "epconfig_21")
                ),
                /*"color" => array(
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
                ),*/
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
    
        $confirmationUrl = $systemurl . "modules/gateways/callback/" . $params["paymentmethod"] . ".php?source_news=webhooks";
        $lang = $params['lang'];
        if ($lang === "en") {
            $epaycoButtonImage = 'https://multimedia.epayco.co/epayco-landing/btns/Boton-epayco-color-Ingles.png';
        }else{
            $epaycoButtonImage = 'https://multimedia.epayco.co/epayco-landing/btns/Boton-epayco-color1.png';
        }
        $ip=$this->getCustomerIp(); 
        $logo = $params['systemurl'].'/modules/gateways/epayco/logo.png';
        $code = "<img src=" . $logo . " /><br><a href='" . $enlace . "' class='btn btn-" . $color . "'>" . $bh_texto . "</a>" . $nota;
        $code = sprintf('
            <p>       
                <center>
                <a id="btn_epayco" href="#">
                    <img src="'.$epaycoButtonImage.'">
                </a>
                </center> 
            </p>
            <script
                src="https://epayco-checkout-testing.s3.amazonaws.com/checkout.preprod.js">
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
                    extras_epayco:{extra5:"P34"},
                    method_confirmation: "POST"
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
                        return  fetch("https://cms.epayco.io/checkout/payment/session", {
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
                    openNewChekout()
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

    function epaycoConfirmation($gatewayOBJ,$informe,$confirmation=false)
    {
        $gatewayModule = $this->modulo;
        //$informes = json_decode(file_get_contents("php://input"), true);
        $informe_cobro = $informe['x_extra1'];
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
            //mail($email, $informe_id . " - Start", print_r($informe, true));
        }
        $command = "GetInvoice";
        $postData = array("invoiceid" => (string)$informe_cobro);
        $arr_transacciones = localAPI($command, $postData, $adminUsername);
        $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $informe['x_extra1'])->get();

        if ($arr_transacciones["result"] !== 'success' || count($resultado) == 0) {
            Capsule::table("bapp_epayco")->insert(array("transaccion" => $informe_cobro, "momento" => date("Y-m-d H:i:s"), "gateway" => $gatewayModule, "refPayco" => $informe['x_ref_payco']));
        }

        $this->callbackEpayco($informe,$confirmation);
        $retorno = "200";
        return $retorno;
    }
    function getPaymentEpayco($gateway, $transaccion)
    {        
       $publicKey = $gateway['publicKey'];
       $url = "https://apify.epayco.co/login";
       $data = array(
            'public_key' => $gateway['publicKey'],
            'private_key' => $gateway['privateKey']
        );
        $json =$this->makeRequest($gateway,$data, $url,true);
        
        if(is_null($json)) {
          throw new Exception("Error get bearer_token.");
        } 
        
        $bearer_token = false;
        if(isset($json->bearer_token)) {
          $bearer_token=$json->bearer_token;
        }else if(isset($json->token)){
          $bearer_token= $json->token;
        }
        
        if(!$bearer_token) {
          $msj = isset($json->message) ? $json->message : "Error get bearer_token";
          if($msj == "Error get bearer_token" && isset($json->error)){
              $msj = $json->error;
          }
          throw new Exception($msj);
        }
          
        $publicKey = $gateway['publicKey'];
        $url = "https://secure.epayco.io/transaction/response.json?ref_payco=".$transaccion."&&public_key=".$publicKey;
        return $this->makeRequest($gateway,[], $url, $bearer_token);
        
    }
    
    function makeRequest($gateway,$data,$url,$bearerToken = false){
        $headers["Content-Type"] = 'application/json';
        if(!$bearerToken){
            $headers["Accept"] = 'application/json';
            $headers["Type"] = 'sdk-jwt';
            $headers["lang"] = 'PHP';
            $headers["Authorization"] = "Bearer ".$bearerToken;
        }else{
            $token = base64_encode($gateway['publicKey'].":".$gateway['privateKey']);
            $headers["Authorization"] = "Basic ".$token;
        }
            try {
                $jsonData = json_encode($data);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                  CURLOPT_URL => $url,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                   CURLOPT_POSTFIELDS => $jsonData,
                  CURLOPT_HTTPHEADER => $headers,
                ));
                $resp = curl_exec($curl);
                if ($resp === false) {
                    return;
                }
                curl_close($curl);
                return json_decode($resp);
            } catch(\Exception $ex) {
                throw new Exception("No se pudo consultar la transaccion: " . $ex->getMessage());
            }


    }
    function callbackEpayco($idtrans,$confirmation)
    {

        if($confirmation){
            if (!empty($idtrans['x_extra1'])) {
                $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $idtrans['x_extra1'])->get();
                $mp_id = $resultado[0]->id;
                $mp_transaccion = $resultado[0]->transaccion;
                $mp_momento = $resultado[0]->momento;
                $mp_gateway = $resultado[0]->gateway;
                $ref_payco = $resultado[0]->refPayco;
            } else {
                $resultado = Capsule::table("bapp_epayco")->first();
                $mp_id = $resultado->id;
                $mp_transaccion = $resultado->transaccion;
                $mp_momento = $resultado->momento;
                $mp_gateway = $resultado->gateway;
                $ref_payco = $resultado->refPayco;
            }
            
        }else{
            $mp_transaccion = $idtrans['x_extra1'];
            $mp_gateway = 'epayco';
            $ref_payco = $idtrans['x_ref_payco'];
        }
        $GATEWAY = getGatewayVariables($mp_gateway);
 
        if (!empty($mp_transaccion)) {

            $admin = $GATEWAY["useradmin"];
            if (!empty($admin)) {
                $adminUsername = $GATEWAY["useradmin"];
            }

            $command = "GetInvoice";
            $postData = array("invoiceid" => $mp_transaccion);
            $invoice = localAPI($command, $postData, $adminUsername);
   
            if ($invoice["result"] == 'success') {
           
                if($confirmation){
                     //$validationData = $this->getPaymentEpayco($GATEWAY,$ref_payco);
                     $validationData = $idtrans;
                }else{
                    $validationData = $idtrans;
                }
                $signature = hash('sha256',
                 trim($GATEWAY['customerID']).'^'
                 .trim($GATEWAY['p_key']).'^'
                 .$idtrans['x_ref_payco'].'^'
                 .$idtrans['x_transaction_id'].'^'
                 .$idtrans['x_amount'].'^'
                 .$idtrans['x_currency_code']
                );
                $invoiceData = Capsule::table('tblorders')
                    ->select('tblorders.amount')
                    ->where('tblorders.invoiceid', '=', $validationData['x_extra1'])
                    ->get();
                $invoiceAmount = $invoiceData[0]->amount;
                $x_amount= $validationData['x_amount'];
                if(floatval($invoiceAmount) === floatval($x_amount)){
                        $validation = true;
                }else{
                    $validation = false;
                }
                if($signature == $validationData['x_signature'] && $validation){
                switch ((int)$validationData['x_cod_response']) {
                    case 1:{
                        if($invoice['status'] != 'Paid' && $invoice['status'] != 'Cancelled'){
                            addInvoicePayment(
                                $invoice['invoiceid'],
                                $validationData['x_ref_payco'],
                                $invoice['total'],
                                null,
                                $GATEWAY['paymentmethod']
                            );
                            logTransaction($GATEWAY['name'], $validationData, "Aceptada");
                            $results = localAPI('AcceptOrder', $postData, $adminUsername);
                            $command = "AddInvoicePayment";
                            $postData = array("gateway" => $GATEWAY["paymentmethod"], "invoiceid" => $mp_transaccion, "amount" => $validationData['x_amount']);
                            $results = localAPI($command, $postData, $adminUsername);
                            $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $mp_transaccion)->delete();
                        }else{
                            if($invoice['status'] == 'Cancelled'){
                                
                                $productsOrder = Capsule::table('tblinvoiceitems')
                                    ->select('tblinvoiceitems.description')
                                    ->where('tblinvoiceitems.invoiceid', '=', $validationData['x_extra2'])
                                    ->where('tblinvoiceitems.type', '=', 'Hosting')
                                    ->get();
                                foreach ($productsOrder as $productOrder )
                                {
                                    $explodProduct = explode(' - ', $productOrder->description, 2);
                                    $productInfo[] = $explodProduct[0]; 
                                }
                                
                                $products = Capsule::table('tblproducts')
                                    ->whereIn('name', $productInfo)
                                    ->get(['name', 'qty'])
                                    ->all();
                                
                                for($i=0; $i<count($products); $i++ ){
                                    $productData[$i]["name"] = $products[$i]->name;
                                    $productData[$i]["qty"] =  $products[$i]->qty-1;
                                } 
                                 
                                for($j=0; $j<count($productData); $j++ ){
                                   $connection = Capsule::table('tblproducts')
                                    ->where('name',"=", $productData[$j]["name"])
                                    ->update(['qty'=> $productData[$j]["qty"]]); 
                                } 
                
                                $results = localAPI('PendingOrder', $postData, $adminUsername);
                                    addInvoicePayment(
                                    $invoice['invoiceid'],
                                    $validationData['x_ref_payco'],
                                    $invoice['total'],
                                    null,
                                    $GATEWAY['paymentmethod']
                                );
                                 logTransaction($GATEWAY['name'], $validationData, "Aceptada");
                                $results = localAPI('AcceptOrder', $postData, $adminUsername);
                                $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $mp_transaccion)->delete();
                                $command = "AddInvoicePayment";
                                $postData = array("gateway" => $GATEWAY["paymentmethod"], "invoiceid" => $mp_transaccion, "amount" => $validationData['x_amount']);
                                $results = localAPI($command, $postData, $adminUsername);
                                $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $mp_transaccion)->delete();
                            }
       
   
                        }
                    }break;
                    case 2:{
                        logTransaction($GATEWAY['name'], $validationData, "Cancelled");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                    case 3:{
                        if($invoice['status'] == 'Cancelled'){
                            $productsOrder = Capsule::table('tblinvoiceitems')
                                ->select('tblinvoiceitems.description')
                                ->where('tblinvoiceitems.invoiceid', '=', $validationData['x_extra2'])
                                ->where('tblinvoiceitems.type', '=', 'Hosting')
                                ->get();
                            foreach ($productsOrder as $productOrder )
                            {
                                $explodProduct = explode(' - ', $productOrder->description, 2);
                                $productInfo[] = $explodProduct[0]; 
                            }
                            
                            $products = Capsule::table('tblproducts')
                                ->whereIn('name', $productInfo)
                                ->get(['name', 'qty'])
                                ->all();
                            
                            for($i=0; $i<count($products); $i++ ){
                                $productData[$i]["name"] = $products[$i]->name;
                                $productData[$i]["qty"] =  $products[$i]->qty-1;
                            } 
                            
                            for($j=0; $j<count($productData); $j++ ){
                               Capsule::table('tblproducts')
                                ->where('name',"=", $productData[$j]["name"])
                                ->update(['qty'=> $productData[$j]["qty"]]);
                            } 
                        }
                    }break;
                    case 4:{
                        logTransaction($GATEWAY['name'], $validationData, "Failure");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                    case 6:{
                        logTransaction($GATEWAY['name'], $validationData, "Failure");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                    case 10:{
                        logTransaction($GATEWAY['name'], $validationData, "Failure");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                    case 11:{
                        logTransaction($GATEWAY['name'], $validationData, "Failure");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                }
                }else{
                    echo "Firma no valida";
                }
            }
            else
            {
                //BORRAR PORQUE YA FUE INGRESADA LA TRANSACCION
                $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $mp_transaccion)->delete();
            }
        }
        return true;
    }
    function procesarTodosRegistrosCallback($limiteRegistros = 10)
    {
        $resultado = Capsule::table("bapp_epayco")->limit($limiteRegistros)->get();
        foreach ($resultado as $registro) {
            $this->callbackEpayco($registro->transaccion, true);
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
    function epayco_getAdminUserWithApiAccess(){
    try {
        return Capsule::table('tbladmins')
            ->join('tbladminroles', 'tbladmins.roleid', '=', 'tbladmins.roleid')
            ->join('tbladminperms', 'tbladminroles.id', '=', 'tbladminperms.roleid')
            ->select('tbladmins.username')
            ->where('tbladmins.disabled', '=', 0)
            ->where('tbladminperms.permid', '=', 81)
            ->get();
    }catch (\Exception $e){
        logActivity("ePayco Suscriptions Addon error in method ". __FUNCTION__.' in '. __FILE__."(".__LINE__."): ".$e->getMessage());
    }
    return false;
}
}
?>
