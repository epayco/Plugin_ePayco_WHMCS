<?php
  
function payco_config() {
    
     $configarray = array(
         
     "FriendlyName"      => array("Type"   => "System", 
                                   "Value" => "Payco"
                                  ),       
         
     "p_cust_id_cliente" => array("FriendlyName"  => "p_cust_id_cliente", 
                                   "Type"         => "text",
                                   "Size"         => "20",
                                   "Description"  => "Ej. <b>0001</b> Se encuentra en el men&uacute; derecho superior en la opci&oacute;n <b>llave secreta</b>"
                                   ),             
         
     "p_key"             => array("FriendlyName"  => "p_key", 
                                   "Type"         => "text", 
                                   "Size"         => "50",
                                   "Description"  => "Ej. <b>8ce71c3cb1732788</b> Se encuentra en el men&uacute; derecho superior en la opci&oacute;n <b>llave secreta</b>"
                                  ),
         
      "p_cliente"        => array("FriendlyName"  => "p_cliente", 
                                   "Type"         => "text", 
                                   "Size"         => "20",
                                   "Description"  => "Corresponde al documento registrado en el men&uacute; derecho superior en la opci&oacute;n <b>Mis Datos</b>."                                 
                                   ),
                    
     "p_test_request"    => array("FriendlyName"  => "p_test_request (Modo de Pruebas)",
                                   "Type"         => "yesno",
                                   "Description"  => "Al estar habilitado todas las transacciones ser&aacute;n procesadas en modo de pruebas."
                                 ),
   
    );
   
	
    return $configarray;
    
}

function payco_link($params) {
        
	# Gateway Specific Variables
	$p_cust_id_cliente  = $params['p_cust_id_cliente'];
	$p_cliente          = $params['p_cliente'];
	$p_key              = $params['p_key'];
	$x_key              = sha1($p_key.$p_cust_id_cliente);
	$p_test_request     = $params['p_test_request'];	
        	 
	# Invoice Variables
	$invoiceid   = $params['invoiceid'];	
        $amount      = $params['amount']; # Format: ##.##
        $currency    = $params['currency']; # Currency Code
 
	# Client Variables
	$userID    = $params['clientdetails']["userid"];
	$firstname = $params['clientdetails']['firstname'];
	$lastname  = $params['clientdetails']['lastname'];
	$email     = $params['clientdetails']['email'];
	$address1  = $params['clientdetails']['address1'];	
	$city      = $params['clientdetails']['city'];
	$state     = $params['clientdetails']['state'];
	$postcode  = $params['clientdetails']['postcode'];
	$country   = $params['clientdetails']['country'];
	$phone     = $params['clientdetails']['phonenumber'];
	
	# System Variables
	$companyname       = $params['companyname'];
	$systemurl         = $params['systemurl'];	
  $p_url_respuesta   = $systemurl.'/modules/gateways/callback/payco.php';
  $actionform        = 'https://secure.payco.co/payment.php';
                
	# Form Api 
        
  if ($p_test_request=='on') {      
      $p_test_request = 'TRUE';      
  }else {      
      $p_test_request = 'FALSE';
  }
                  
  $ip_cliente = $_SERVER['REMOTE_ADDR'];

   //consultar la orden con el id de la factura
   $o_table   = "tblorders";
   $o_fields  = "id";
   $o_where   = array("invoiceid" => $invoiceid);
   $o_result  = select_query($o_table, $o_fields, $o_where);
   $o_data      = mysql_fetch_array($o_result);
   $orderid   = $o_data['id'];

   //consultar items de la factura         
   $f_table   = "tblinvoiceitems";
   $f_fields  = "description";
   $f_where   = array("invoiceid" => $invoiceid);         
   $f_result  = select_query($f_table, $f_fields, $f_where);
   
   $descrip   = "";
   
   while ($f_data = mysql_fetch_array($f_result)) {
      
      $descrip .= $f_data['description'].", ";
     
   }

    $description = substr($descrip, 0 , -2);

    $code = '<form name="frmpago" action="'.$actionform.'" method="POST">
              <input name="p_cust_id_cliente" type="hidden" value="'.$p_cust_id_cliente.'" />               
              <input name="p_key" type="hidden" value="'.$x_key.'" />
              <input name="p_cliente" type="hidden" value="'.$p_cliente.'" />
              <input name="p_test_request" type="hidden" value="'.$p_test_request.'" />
              <input name="p_description" type="hidden" value="'.$description.'" />
              <input name="p_amount" type="hidden" value="'.$amount.'" />
              <input name="p_id_factura" type="hidden" value="Orden de Compra Nro:'.$orderid.' / Fact Nro:'.$invoiceid.'" />
              <input name="p_amount_base" type="hidden" value="0" />
              <input name="p_tax" type="hidden" value="0" />
              <input name="p_billing_name" type="hidden" value="'.$firstname.'" />
              <input name="p_billing_lastname" type="hidden" value="'.$lastname.'" />
              <input name="p_billing_address" type="hidden" value="'.$address1.'" />
              <input name="p_billing_country" type="hidden" value="'.$country.'" />
              <input name="p_billing_state" type="hidden" value="'.$state.'" />
              <input name="p_billing_postcode" type="hidden" value="'.$postcode.'" />
              <input name="p_billing_city" type="hidden" value="'.$city.'" />
              <input name="p_billing_email" type="hidden" value="'.$email.'" />
              <input name="p_billing_phone" type="hidden" value="'.$phone.'" />
              <input name="p_currency_code" type="hidden" value="'.$currency.'" />
              <input name="p_customer_ip" type="hidden" value="'.$ip_cliente.'" />
              <input name="p_url_respuesta" type="hidden" value="'.$p_url_respuesta.'" />
              <input name="p_url_confirmacion" type="hidden" value="'.$p_url_respuesta.'" />
              <input name="p_company" type="hidden" value="'.$companyname.'" />
              <input name="p_systemurl" type="hidden" value="'.$systemurl.'" />
              <input name="p_extra1" type="hidden" value="" />
              <input name="p_extra2" type="hidden" value="" />
              <input name="p_extra3" type="hidden" value="" />   
              <input type="image" name="imageField" id="imageField" src="https://payco.co/img/btnpago.png"/>
      </form> <br>';

return $code;
}
?>
