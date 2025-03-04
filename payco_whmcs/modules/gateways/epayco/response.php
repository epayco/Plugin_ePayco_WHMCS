<?php
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
echo sprintf('
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
  <title>Formulario Pruebas Respuesta</title>
  <!-- Bootstrap -->
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>

<body>
  <header id="main-header" style="margin-top:20px">
    <div class="row">
      <div class="col-lg-12 franja">
      </div>
    </div>
  </header>
  <div class="container">
    <div class="row" style="margin-top:20px">
      <div class="col-lg-8 col-lg-offset-2 ">
        <h4 style="text-align:left"> Respuesta de la Transacción </h4>
        <hr>
      </div>
      <div class="col-lg-8 col-lg-offset-2 ">
        <div class="table-responsive">
          <table class="table table-bordered">
            <tbody>
              <tr>
                <td>Referencia</td>
                <td id="referencia"></td>
              </tr>
              <tr>
                <td class="bold">Fecha</td>
                <td id="fecha" class=""></td>
              </tr>
              <tr>
                <td>Respuesta</td>
                <td id="respuesta"></td>
              </tr>
              <tr>
                <td>Motivo</td>
                <td id="motivo"></td>
              </tr>
              <tr>
                <td class="bold">Banco</td>
                <td class="" id="banco">
              </tr>
              <tr>
                <td class="bold">Recibo</td>
                <td id="recibo"></td>
              </tr>
              <tr>
                <td class="bold">Total</td>
                <td class="" id="total">
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-lg-8 col-lg-offset-2 ">
        <a class="btn btn-primary" id="inicio">Inicio</a>
      </div>
    </div>
  </div>
  <footer>
    <div class="row">
      <div class="container">
        <div class="col-lg-8 col-lg-offset-2">
       <img src="./logo.png" alt="medios de pago" style="margin-left:-14px; margin-top:-20px; width:850px">
        </div>
      </div>
    </div>
  </footer>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script>
    function getQueryParam(param) {
      location.search.substr(1)
        .split("&")
        .some(function(item) { // returns first occurence and stops
          return item.split("=")[0] == param && (param = item.split("=")[1])
        })
      return param
    }
    $(document).ready(function() {
      var location_ = location.href;
      var inicio = location_.replace("/modules/gateways/epayco/response.php", "");
      var inicio_ = document.getElementById("inicio");
      inicio_.href = inicio;
      var ref_payco = getQueryParam("ref_payco");
      var urlapp = "https://secure.epayco.io/validation/v1/reference/" + ref_payco;

      $.get(urlapp, function(response) {
        if (response.success) {
          if (response.data.x_cod_response == 1) {
            //Codigo personalizado
            alert("Transaccion Aprobada");
            console.log("transacción aceptada");
          }
          //Transaccion Rechazada
          if (response.data.x_cod_response == 2) {
            console.log("transacción rechazada");
          }
          //Transaccion Pendiente
          if (response.data.x_cod_response == 3) {
            console.log("transacción pendiente");
          }
          //Transaccion Fallida
          if (response.data.x_cod_response == 4) {
            console.log("transacción fallida");
          }

          $("#fecha").html(response.data.x_transaction_date);
          $("#respuesta").html(response.data.x_response);
          $("#referencia").text(response.data.x_id_invoice);
          $("#motivo").text(response.data.x_response_reason_text);
          $("#recibo").text(response.data.x_transaction_id);
          $("#banco").text(response.data.x_bank_name);
          $("#autorizacion").text(response.data.x_approval_code);
          $("#total").text(response.data.x_amount + " " + response.data.x_currency_code);

        } else {
          alert("Error consultando la información");
        }
      });

    });
  </script>
</body>

</html>    
');