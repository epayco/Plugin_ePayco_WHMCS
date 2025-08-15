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
        $uri = "https://api.epayco.com/checkout/preferences/";
        $data = array("additional_info" => "", "auto_return" => $datos_mp["retorno"], "back_urls" => array("failure" => $datos_mp["url_fallo"], "pending" => $datos_mp["url_pendiente"], "success" => $datos_mp["url_exito"]), "binary_mode" => true, "merchant_account_id" => $datos_mp["merchant_account_id"], "processing_modes" => array($datos_mp["processing"]), "processing_mode" => $datos_mp["processing"], "external_reference" => $datos_mp["referencia"], "items" => array(array("id" => "", "currency_id" => $datos_mp["item_moneda"], "title" => $datos_mp["item_titulo"], "picture_url" => $datos_mp["item_imagen"], "description" => $datos_mp["item_descripcion"], "category_id" => "services", "quantity" => 1, "unit_price" => $datos_mp["item_precio"])), "notification_url" => $datos_mp["notification_url"], "payer" => array("phone" => array("area_code" => $datos_mp["comprador_telefono_codigodearea"], "number" => $datos_mp["comprador_telefono_numero"]), "address" => array("zip_code" => $datos_mp["comprador_domicilio_codigopostal"], "street_name" => $datos_mp["comprador_domicilio_calle"], "street_number" => $datos_mp["comprador_domicilio_numero"]), "identification" => array("number" => $datos_mp["comprador_documento_numero"], "type" => $datos_mp["comprador_documento_tipo"]), "email" => $datos_mp["comprador_email"], "name" => $datos_mp["comprador_nombre"], "surname" => $datos_mp["comprador_apellido"]), "payment_methods" => array("excluded_payment_types" => $datos_mp["exclusiones"]));
        $url = "https://api.epayco.com/checkout/preferences/?access_token=" . $accesstoken;
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
                ),
                "bh_modocolaprocesamiento" => array(
                    "FriendlyName" => traduccionEpayco($idioma, "epconfig_35") . ":", 
                    "Type" => "yesno", 
                    "Description" => traduccionEpayco($idioma, "epconfig_36")
                )
                */
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
        $sub_total = $amount - $tax;
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
        $payload = [
            "amount"=> (string)$amount,
            "tax_base"=>  (string)$sub_total,
            "tax"=> (string)$tax,
            "name"=> substr($description, 0, 50), 
            "description"=> substr($description, 0, 50), 
            "currency"=> strtolower($currencyCode), 
            "test"=> $testMode,
            "invoice"=> (string)$params['invoiceid'],
            "country"=> $countryCode,
            "response"=> $confirmationUrl,
            "confirmation"=> $confirmationUrl,
            "external"=> $externalMode,
            "email_billing"=>$email,
            "name_billing"=> $billing_name,
            "address_billing"=> $address1,
            "extra1"=> (string)$params['invoiceid'],
            "extra2"=> (string)$invoiceData[0]->id,
            "lang"=> $lang,
            "ip"=> (string)$ip,
            "taxIco"=> "0",
            "autoclick"=> "true",
            "extras_epayco"=>["extra5"=>"P34"],
            "method_confirmation"=> "POST",
            "checkout_version"=>"2"    
        ];
        $payload['extra3'] = base64_encode(json_encode($payload));
        //$paymentSession = $this->epaycoSessionPayment($params, $payload);
        $tokenBearer =$this->ePaycoToken($params);
        $checkout =  base64_encode(json_encode($payload));  
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
            const publicKey = "%s";
            const privateKey = "%s";
                var handler = ePayco.checkout.configure({
                    key: publicKey,
                    test: "%s"
                })
                var bntPagar = document.getElementById("btn_epayco");
                const params = JSON.parse(atob("%s"));
                const bearerToken = "%s";
                let {
                    amount,
                    tax_base,
                    tax,
                    name,
                    description,
                    currency,
                    test,
                    invoice,
                    country,
                    response,
                    confirmation,
                    external,
                    email_billing,
                    name_billing,
                    address_billing,
                    extra1,
                    extra2,
                    extra3,
                    lang,
                    ip,
                    taxIco,
                    autoclick,
                    extras_epayco,
                    method_confirmation,
                    checkout_version
                } = params;
                var data = {
                    amount,
                    tax_base,
                    tax,
                    name,
                    description,
                    currency,
                    test,
                    invoice,
                    country,
                    response,
                    confirmation,
                    external,
                    email_billing,
                    name_billing,
                    address_billing,
                    extra1,
                    extra2,
                    lang,
                    ip,
                    taxIco,
                    autoclick,
                    extras_epayco,
                    method_confirmation,
                    checkout_version
                }
                var openNewChekout = function () {
                    makePayment(privateKey,publicKey,data, data.external == "true"?true:false)
                }
                var makePayment = function (privatekey, apikey, info, external) {
                    const headers = { 
                        "Content-Type": "application/json",
                        "Authorization": "Bearer " + bearerToken
                    };
                    var payment =   function (){
                        return  fetch("https://apify.epayco.co/payment/session/create", {
                            method: "POST",
                            body: JSON.stringify(info),
                            headers
                        })
                        .then(res =>  res.json())
                        .catch(err => {
                            console.log(err.message);
                            bntPagar.style.pointerEvents = "auto";
                            bntPagar.style.opacity = "1";
                        });
                    }
                    payment()
                        .then(session => {
                            bntPagar.style.pointerEvents = "all";
                            if(session.data.sessionId != undefined){
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
                            console.log("Depuración: Error en la creación de sesión:", error.message);
                            bntPagar.style.pointerEvents = "auto";
                            bntPagar.style.opacity = "1";
                        });
                }
                var openChekout = function () {
                    //handler.open(data);
                    openNewChekout()
                    bntPagar.style.pointerEvents = "none";
                    bntPagar.style.opacity = "0.5";
                    console.log(data)
                }
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
        $params['privateKey'],
        $testMode,
        $checkout,
        $tokenBearer,
        $nota
    );
        return $code;
    }
    function epayco_getChargeDescription($invoceItems){
        $descriptions = array();
        foreach($invoceItems as $item){
            $clearData = str_replace('_', ' ', $this->string_sanitize($item['description']));
            $descriptions[] = $clearData;
        }
        return implode(' - ', $descriptions);
    }

    function epaycoConfirmation($gatewayOBJ,$informe,$confirmation=false)
    {
        $gatewayModule = $this->modulo;
        //$informes = json_decode(file_get_contents("php://input"), true);
        $informe_cobro = $informe['x_extra1'];
        $email = $gatewayOBJ["email"];
        //$modoProcesamientoPorColas = $gatewayOBJ["bh_modocolaprocesamiento"] == "on";
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

        return $this->callbackEpayco($informe,$confirmation);
    }
    function getPaymentEpayco($gateway, $transaccion)
    {        
       $bearer_token = $this->ePaycoToken($gateway);
        $publicKey = $gateway['publicKey'];
        $url = "https://secure.payco.co/transaction/response.json?ref_payco=".$transaccion."&&public_key=".$publicKey;
        return $this->makeRequest($gateway,[], $url, "Bearer ".$bearer_token);
    }
    function makeRequest($gateway,$data,$url,$bearerToken = false){
        $headers["Content-Type"] = 'application/json';
        if(!$bearerToken){
            $bearerToken = 'Bearer '.$token;
        }else{
            $token = base64_encode($gateway['publicKey'].":".$gateway['privateKey']);
            $bearerToken = 'Basic '.$token;
        }
            try {
                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: '.$bearerToken
                  );
                if(!empty($data)){
                    $jsonData = json_encode($data);
                }else{
                    $jsonData = null;
                }
                

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
                /*if(floatval($invoiceAmount) === floatval($x_amount)){
                        $validation = true;
                }else{
                    $validation = false;
                }*/
                $validation = true;
                if(($signature == $validationData['x_signature'] || $validationData['x_signature'] == 'Authorized')  && $validation){
                switch ((int)$validationData['x_cod_response']) {
                    case 1:{
                        $message = 'AcceptOrder';
                        if($invoice['status'] != 'Paid' && $invoice['status'] != 'Cancelled'){
                            addInvoicePayment(
                                $invoice['invoiceid'],
                                $validationData['x_ref_payco'],
                                $invoice['total'],
                                null,
                                $GATEWAY['paymentmethod']
                            );
                            logTransaction($GATEWAY['name'], $validationData, "Aceptada");
                            $results = localAPI($message, $postData, $adminUsername);
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
                                $results = localAPI($message, $postData, $adminUsername);
                                $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $mp_transaccion)->delete();
                                $command = "AddInvoicePayment";
                                $postData = array("gateway" => $GATEWAY["paymentmethod"], "invoiceid" => $mp_transaccion, "amount" => $validationData['x_amount']);
                                $results = localAPI($command, $postData, $adminUsername);
                                $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $mp_transaccion)->delete();
                            }
       
   
                        }
                    }break;
                    case 2:{
                        $message = 'CancelledOrder';
                        logTransaction($GATEWAY['name'], $validationData, "Cancelled");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                    case 3:{
                        $message = 'PendingOrder';
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
                        $message = 'PendingOrder';
                        logTransaction($GATEWAY['name'], $validationData, "Failure");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                    case 6:{
                        $message = 'PendingOrder';
                        logTransaction($GATEWAY['name'], $validationData, "Failure");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                    case 10:{
                        $message = 'PendingOrder';
                        logTransaction($GATEWAY['name'], $validationData, "Failure");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                    case 11:{
                        $message = 'PendingOrder';
                        logTransaction($GATEWAY['name'], $validationData, "Failure");
                        if($invoice['status'] != 'Cancelled'){
                            $results = localAPI($command, $postData, $adminUsername);
                        }
                    }break;
                }
                }else{
                    $message = "Firma no valida";
                }
            }
            else
            {
                $message = "UndefinedOrder";
                //BORRAR PORQUE YA FUE INGRESADA LA TRANSACCION
                $resultado = Capsule::table("bapp_epayco")->where("transaccion", "=", $mp_transaccion)->delete();
            }
        }
        return $message;
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
    function ePaycoToken($gateway){
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
        error_log(json_encode($json));
        if(!$bearer_token) {
          $msj = isset($json->message) ? $json->message : "Error get bearer_token";
          if($msj == "Error get bearer_token" && isset($json->error)){
              $msj = $json->error;
          }
          throw new Exception($msj);
        }
        return $bearer_token;
    }
    function epaycoSessionPayment($gateway,$data){
        $bearer_token = $this->ePaycoToken($gateway);
        $url = "https://apify.epayco.co/payment/session/create";
        return $this->makeRequest($gateway, $data, $url, "Bearer ".$bearer_token);
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
    function string_sanitize($string, $force_lowercase = true, $anal = false)
    {

        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]", "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;", "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "_", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
        return $clean;
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