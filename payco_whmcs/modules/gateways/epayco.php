<?php

use Illuminate\Database\Capsule\Manager as Capsule;

function epayco_MetaData()
{
    return array(
        'DisplayName' => 'ePayco',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function epayco_config(){

    $usersWithApiAccess = epayco_getAdminUserWithApiAccess();

    $usersWithApiAccessArray = array();
    foreach($usersWithApiAccess as $userWithApiAccess){
        $usersWithApiAccessArray[$userWithApiAccess->username] = $userWithApiAccess->username;
    }

    $countryList = epayco_loadCountries();

    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'ePayco',
        ),
        'customerID' => array(
            'FriendlyName' => 'P_CUST_ID_CLIENTE',
            'Type' => 'text',
            'Size' => '32',
            'Default' => '',
            'Description' => '<br/>ID de cliente que lo representa en la plataforma. es Proporcionado en su panel de clientes en la opción configuración.',
        ),
        'publicKey' => array(
            'FriendlyName' => 'PUBLIC_KEY',
            'Type' => 'text',
            'Size' => '32',
            'Default' => '',
            'Description' => '<br/>Corresponde a la llave de autenticación en el API Rest, Proporcionado en su panel de clientes en la opción configuración.',
        ),
        'privateKey' => array(
            'FriendlyName' => 'PRIVATE_KEY',
            'Type' => 'text',
            'Size' => '32',
            'Default' => '',
            'Description' => '<br/>Corresponde a la llave de autenticación en el API Rest, Proporcionado en su panel de clientes en la opción configuración.',
        ),
        'p_key' => array(
            'FriendlyName' => 'P_KEY',
            'Type' => 'text',
            'Size' => '32',
            'Default' => '',
            'Description' => '<br/>Corresponde a la llave transacción de su cuenta, Proporcionado en su panel de clientes en la opción configuración.',
        ),
        'countryCode' => array(
            'FriendlyName' => 'País del comercio',
            'Type' => 'dropdown',
            'Options' => $countryList,
            'Description' => 'País en el cual se encuentra el comercio',
        ),
        'currencyCode' => array(
            'FriendlyName' => 'Moneda',
            'Type' => 'dropdown',
            'Options' => array(
                'default' => 'Moneda del cliente',
                'cop' => 'Peso colombiano (COP)',
                'usd' => 'Dolar estadounidense (USD)'
            ),
            'Description' => '<br/>Moneda de las transacciones.',
        ),
        'lang' => array(
            'FriendlyName' => 'Lenguaje',
            'Type' => 'dropdown',
            'Options' => array(
                'es' => 'Español',
                'en' => 'Ingles'
            ),
            'Description' => '<br/>lenguaje checkout.',
        ),
        'testMode' => array(
            'FriendlyName' => 'Modo de pruebas',
            'Type' => 'yesno',
            'Description' => 'Habilite para activar el modo de pruebas',
        ),
        'externalMode' => array(
            'FriendlyName' => 'Standar checkout',
            'Type' => 'yesno',
            'Description' => 'Redirija a la pasarela de pagos',
        ),
        'WHMCSAdminUser' => array(
            'FriendlyName' => 'Usuario administrador WHMCS',
            'Type' => 'dropdown',
            'Options' => $usersWithApiAccessArray,
            'Description' => 'Usuario administrador de WHMCS con permisos de acceso al API',
        ),
    );
}

function epayco_link($params){

    if(strpos($_SERVER['PHP_SELF'], 'viewinvoice.php') === false){
        return "";
    }

    $countryCode = $params['countryCode'];
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

    $description = epayco_getChargeDescription($invoice['items']['item']);
    if(floatval($invoice["subtotal"]) > 0.0 ){
        $tax=floatval($invoice["tax"]);
        $sub_total = floatval($invoice["subtotal"]);
        $amount = floatval($invoice["total"]);
    }else{
        $tax="0";
        $sub_total = $params["amount"];
        $amount = $params["amount"];
    }

    $confirmationUrl = $params['systemurl'].'/modules/gateways/callback/epayco.php';
    $lang = $params['lang'];
    if ($lang === "en") {
        $epaycoButtonImage = 'https://multimedia.epayco.co/epayco-landing/btns/Boton-epayco-color-Ingles.png';
    }else{
        $epaycoButtonImage = 'https://multimedia.epayco.co/epayco-landing/btns/Boton-epayco-color1.png';
    }
    $ip=getCustomerIp();
    return sprintf('
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
                            }
                        })
                        .catch(error => {
                            error.message;
                        });
                }
                var openChekout = function () {
                    //handler.open(data);
                    openNewChekout()
                }
                var bntPagar = document.getElementById("btn_epayco");
                bntPagar.addEventListener("click", openChekout);
                openChekout()
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
    ',  $amount,
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
        $params['privateKey']
    );
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
        logActivity("Stripe Suscriptions Addon error in method ". __FUNCTION__.' in '. __FILE__."(".__LINE__."): ".$e->getMessage());
    }
    return false;
}

function epayco_getChargeDescription($invoceItems){
    $descriptions = array();
    foreach($invoceItems as $item){
        $descriptions[] = $item['description'];
    }

    return implode(' - ', $descriptions);
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
