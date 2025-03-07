<?php
function traduccionEpayco($idioma, $texto)
{
    $textos["es"]["epayco_config_1"] = "Licencia de uso para los módulos de pago ePayco";
    $textos["es"]["epayco_config_2"] = "Licencia";
    $textos["es"]["epayco_config_3"] = "Verificador Local";
    $textos["es"]["epayco_config_4"] = "Código local de verificación de licencia - NO MODIFICAR!";
    $textos["es"]["epayco_config_5"] = "Idioma";
    $textos["es"]["epayco_config_6"] = "Seleccione el idioma de configuración del módulo.";
    $textos["es"]["epayco_activate_1"] = "Activación exitosa: Haga clic en el botón de configuración para modificar las opciones del módulo.";
    $textos["es"]["epayco_deactivate_1"] = "El módulo ePayco se desactivó correctamente.";
    $textos["es"]["epconfig_1"] = "ID de cliente que lo representa en la plataforma. es Proporcionado en su panel de clientes en la opción configuración.";
    $textos["es"]["epconfig_2"] = "Corresponde a la llave de autenticación en el API Rest, Proporcionado en su panel de clientes en la opción configuración.";
    $textos["es"]["epconfig_3"] = "Corresponde a la llave transacción de su cuenta, Proporcionado en su panel de clientes en la opción configuración.";
    $textos["es"]["epconfig_4"] = "País del comercio";
    $textos["es"]["epconfig_5"] = "País en el cual se encuentra el comercio";
    $textos["es"]["epconfig_6"] = "Moneda";
    $textos["es"]["epconfig_7"] = "Moneda del cliente";
    $textos["es"]["epconfig_8"] = "Peso colombiano (COP)";
    $textos["es"]["epconfig_9"] = "Dolar estadounidense (USD)";
    $textos["es"]["epconfig_10"] = "Moneda de las transacciones.";
    $textos["es"]["epconfig_11"] = "Lenguaje";
    $textos["es"]["epconfig_12"] = "Español";
    $textos["es"]["epconfig_13"] = "Ingles";
    $textos["es"]["epconfig_14"] = "lenguaje checkout.";
    $textos["es"]["epconfig_15"] = "Modo de pruebas";
    $textos["es"]["epconfig_16"] = "Habilite para activar el modo de pruebas";
    $textos["es"]["epconfig_17"] = "Redirija a la pasarela de pagos";
    $textos["es"]["epconfig_18"] = "Texto del botón de pago";
    $textos["es"]["epconfig_19"] = "Color del botón";
    $textos["es"]["epconfig_20"] = "Nota bajo botón";
    $textos["es"]["epconfig_21"] = "Esta nota aparecerá bajo el botón de cobro. [Opcional]";
    
    $textos["es"]["epconfig_22"] = "Texto del botón de pago";
    $textos["es"]["epconfig_23"] = "Pagar Ahora";
    $textos["es"]["epconfig_24"] = "Color del botón"; 
    $textos["es"]["epconfig_25"] = "Celeste";
    $textos["es"]["epconfig_26"] = "Gris";
    $textos["es"]["epconfig_27"] = "Verde";
    $textos["es"]["epconfig_28"] = "Rojo";
    $textos["es"]["epconfig_29"] = "Amarillo";
    $textos["es"]["epconfig_30"] = "Teal";
    $textos["es"]["epconfig_31"] = "Blanco";
    $textos["es"]["epconfig_32"] = "Negro";
    $textos["es"]["epconfig_33"] = "Solo link";
    $textos["es"]["epconfig_34"] = "Seleccione el color del botón de pago";
    $textos["es"]["epconfig_35"] = "Procesamiento de transacciones en cola";
    $textos["es"]["epconfig_36"] = "Para evitar transacciones duplicadas, se encolan las transacciones recibidas de ePayco y se procesan una a una con el System Cron de WHMCS (cada 5 minutos)";

    
    $textos["en"]["epayco_config_1"] = "Use license for MercadoPago payment modules";
    $textos["en"]["epayco_config_2"] = "License";
    $textos["en"]["epayco_config_3"] = "Local Verifier";
    $textos["en"]["epayco_config_4"] = "Local License Verification Code - DO NOT MODIFY!";
    $textos["en"]["epayco_config_5"] = "Language";
    $textos["en"]["epayco_config_6"] = "Select the language for module configuration.";
    $textos["en"]["epayco_activate_1"] = "Successful activation: Click the configuration button to modify the module options.";
    $textos["en"]["epayco_deactivate_1"] = "The MercadoPago module was successfully disabled.";
    $textos["en"]["epconfig_1"] = "Client ID that represents you on the platform. It is provided in your customer panel in the configuration option.";
    $textos["en"]["epconfig_2"] = "It corresponds to the authentication key in the Rest API, provided in your customer panel in the configuration option.";
    $textos["en"]["epconfig_3"] = "It corresponds to the transaction key of your account, provided in your customer panel in the configuration option.";
    $textos["en"]["epconfig_4"] = "Store country";
    $textos["en"]["epconfig_5"] = "Country in which the store is located";
    $textos["en"]["epconfig_6"] = "Currency";
    $textos["en"]["epconfig_7"] = "Client currency";
    $textos["en"]["epconfig_8"] = "Colombian peso (COP)";
    $textos["en"]["epconfig_9"] = "Dolar estadounidense (USD)";
    $textos["en"]["epconfig_10"] = "Transaction currency.";
    $textos["en"]["epconfig_11"] = "Language";
    $textos["en"]["epconfig_12"] = "Spanish";
    $textos["en"]["epconfig_13"] = "English";
    $textos["en"]["epconfig_14"] = "checkout language.";
    $textos["en"]["epconfig_15"] = "Test mode";
    $textos["en"]["epconfig_16"] = "Enable to activate testing mode";
    $textos["en"]["epconfig_17"] = "Redirect to payment gateway";
    $textos["en"]["epconfig_18"] = "Payment button text";
    $textos["en"]["epconfig_19"] = "Button color";
    $textos["en"]["epconfig_20"] = "Note under button";
    $textos["en"]["epconfig_21"] = "This note will appear under the payment button. [Optional]";
    $textos["en"]["epconfig_35"] = "Queued transaction processing";
    $textos["en"]["epconfig_36"] = "To avoid duplicated transactions, transactions received from ePayco are queued and processed one by one with the WHMCS System Cron (every 5 minutes)";
    
    
    $textos["en"]["epconfig_22"] = "Payment button text";
    $textos["en"]["epconfig_23"] = "Pay Now";
    $textos["en"]["epconfig_24"] = "Button color"; 
    $textos["en"]["epconfig_25"] = "Light blue";
    $textos["en"]["epconfig_26"] = "Gray";
    $textos["en"]["epconfig_27"] = "Green";
    $textos["en"]["epconfig_28"] = "Red";
    $textos["en"]["epconfig_29"] = "Yellow";
    $textos["en"]["epconfig_30"] = "Teal";
    $textos["en"]["epconfig_31"] = "White";
    $textos["en"]["epconfig_32"] = "Black";
    $textos["en"]["epconfig_33"] = "Only link";
    $textos["en"]["epconfig_34"] = "Select the color of the payment button";
    
    return $textos[$idioma][$texto];
}
    