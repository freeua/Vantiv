<?php
require_once realpath(dirname(__FILE__)).'/vendor/autoload.php';
#sale
$sale_info = array(
             'id' => '42',
             'orderId' => '1',
             'amount'  => '10010',
             'orderSource' => 'ecommerce',
             'billToAddress' => array(
             'name' => 'John Smith' ,
             'addressLine1' => ' 1 Main St.',
             'city' => 'Burlington' ,
             'state' => 'MA' ,
             'zip' => '0183-3747',
             'country' => 'US'),
             'card' => array(
             'number' => '5112010000000003',
             'expDate' => '0112',
             'cardValidationNum' => '349',
             'type' => 'MC' )
            );
$initialize = new cnp\sdk\CnpOnlineRequest();
$saleResponse =$initialize->saleRequest($sale_info);
#display results
echo ("Response: " . (cnp\sdk\XmlParser::getNode($saleResponse,'response')) . "<br>");
echo ("Message: " . cnp\sdk\XmlParser::getNode($saleResponse,'message') . "<br>");
echo ("Vantiv eCommerce Transaction ID: " . cnp\sdk\XmlParser::getNode($saleResponse,'cnpTxnId'));