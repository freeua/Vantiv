<?php /** @noinspection ALL */

/*
 * Copyright (c) 2011 Vantiv eCommerce Inc.
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
namespace cnp\sdk;
require_once realpath(dirname(__FILE__)) . '/CnpOnline.php';

class CnpOnlineRequest
{
    private $useSimpleXml = false;

    public function __construct($treeResponse=false)
    {
        $this->useSimpleXml = $treeResponse;
        $this->newXML = new CnpXmlMapper();
    }

    /**
     * @param $code
     * @return mixed|string
     */
    public static function getAddressResponse($code)
    {
        $codes = array("00" => "5-Digit zip and address match",
                       "01" => "9-Digit zip and address match",
                       "02" => "Postal code and address match",
                       "10" => "5-Digit zip matches, address does not match",
                       "11" => "9-Digit zip matches, address does not match",
                       "12" => "Zip does not match, address matches",
                       "13" => "Postal code does not match, address matches",
                       "14" => "Postal code matches, address not verified",
                       "20" => "Neither zip nor address match",
                       "30" => "AVS service not supported by issuer",
                       "31" => "AVS system not available",
                       "32" => "Address unavailable",
                       "33" => "General error",
                       "34" => "AVS not performed",
                       "40" => "Address failed Vantiv eCommerce Inc. edit checks");

        return (isset($codes[$code]) ? $codes[$code] : "Unknown Address Response");
    }



    /**
     * @param $hash_in
     * @return \DOMDocument|\SimpleXMLElement
     * @throws exceptions\cnpSDKException
     */
    public function authorizationRequest($hash_in)
    {
        if (isset($hash_in['cnpTxnId'])) {
            $hash_out = array('cnpTxnId'=> (XmlFields::returnArrayValue($hash_in,'cnpTxnId')));
        } else {
            $hash_out = array(
            'orderId'=> XmlFields::returnArrayValue($hash_in,'orderId'),
            'amount'=>XmlFields::returnArrayValue($hash_in,'amount'),
            'id'=>XmlFields::returnArrayValue($hash_in,'id'),
            'secondaryAmount'=>XmlFields::returnArrayValue($hash_in,'secondaryAmount'),
            'surchargeAmount' =>XmlFields::returnArrayValue($hash_in,'surchargeAmount'),
            'orderSource'=>XmlFields::returnArrayValue($hash_in,'orderSource'),
            'customerInfo'=>(XmlFields::customerInfo(XmlFields::returnArrayValue($hash_in,'customerInfo'))),
            'billToAddress'=>(XmlFields::contact(XmlFields::returnArrayValue($hash_in,'billToAddress'))),
            'shipToAddress'=>(XmlFields::contact(XmlFields::returnArrayValue($hash_in,'shipToAddress'))),
            'card'=> (XmlFields::cardType(XmlFields::returnArrayValue($hash_in,'card'))),
            'paypal'=>(XmlFields::payPal(XmlFields::returnArrayValue($hash_in,'paypal'))),
            'token'=>(XmlFields::cardTokenType(XmlFields::returnArrayValue($hash_in,'token'))),
            'paypage'=>(XmlFields::cardPaypageType(XmlFields::returnArrayValue($hash_in,'paypage'))),
            'applepay'=>(XmlFields::applepayType(XmlFields::returnArrayValue($hash_in,'applepay'))),
            'mpos'=>(XmlFields::mposType(XmlFields::returnArrayValue($hash_in,'mpos'))),
            'billMeLaterRequest'=>(XmlFields::billMeLaterRequest(XmlFields::returnArrayValue($hash_in,'billMeLaterRequest'))),
            'cardholderAuthentication'=>(XmlFields::fraudCheckType(XmlFields::returnArrayValue($hash_in,'cardholderAuthentication'))),
            'processingInstructions'=>(XmlFields::processingInstructions(XmlFields::returnArrayValue($hash_in,'processingInstructions'))),
            'pos'=>(XmlFields::pos(XmlFields::returnArrayValue($hash_in,'pos'))),
            'customBilling'=>(XmlFields::customBilling(XmlFields::returnArrayValue($hash_in,'customBilling'))),
            'taxBilling'=>(XmlFields::taxBilling(XmlFields::returnArrayValue($hash_in,'taxBilling'))),
            'enhancedData'=>(XmlFields::enhancedData(XmlFields::returnArrayValue($hash_in,'enhancedData'))),
            'amexAggregatorData'=>(XmlFields::amexAggregatorData(XmlFields::returnArrayValue($hash_in,'amexAggregatorData'))),
            'allowPartialAuth'=>XmlFields::returnArrayValue($hash_in,'allowPartialAuth'),
            'healthcareIIAS'=>(XmlFields::healthcareIIAS(XmlFields::returnArrayValue($hash_in,'healthcareIIAS'))),
            'lodgingInfo'=>XmlFields::lodgingInfo(XmlFields::returnArrayValue($hash_in,'lodgingInfo')),
            'filtering'=>(XmlFields::filteringType(XmlFields::returnArrayValue($hash_in,'filtering'))),
            'merchantData'=>(XmlFields::merchantData(XmlFields::returnArrayValue($hash_in,'merchantData'))),
            'recyclingRequest'=>(XmlFields::recyclingRequestType(XmlFields::returnArrayValue($hash_in,'recyclingRequest'))),
            'fraudFilterOverride'=> XmlFields::returnArrayValue($hash_in,'fraudFilterOverride'),
            'recurringRequest'=>XmlFields::recurringRequestType(XmlFields::returnArrayValue($hash_in,'recurringRequest')),
            'debtRepayment' => XmlFields::returnArrayValue ( $hash_in, 'debtRepayment' ),
			'advancedFraudChecks' => XmlFields::advancedFraudChecksType ( XmlFields::returnArrayValue ( $hash_in, 'advancedFraudChecks' ) ),
			'processingType' => XmlFields::returnArrayValue ( $hash_in, 'processingType' ),
			'originalNetworkTransactionId' => XmlFields::returnArrayValue ( $hash_in, 'originalNetworkTransactionId' ),
			'originalTransactionAmount' => XmlFields::returnArrayValue ( $hash_in, 'originalTransactionAmount' ),
            'skipRealtimeAU'=> XmlFields::returnArrayValue ( $hash_in, 'skipRealtimeAU'),
                'lodgingInfo' => XmlFields::lodgingInfo(XmlFields::returnArrayValue($hash_in, 'lodgingInfo')),
                'merchantCategoryCode'=>XmlFields::returnArrayValue($hash_in,'merchantCategoryCode')
            );
        }
        $choice_hash = array(XmlFields::returnArrayValue($hash_out,'card'),XmlFields::returnArrayValue($hash_out,'paypal'),XmlFields::returnArrayValue($hash_out,'token'),XmlFields::returnArrayValue($hash_out,'paypage'),XmlFields::returnArrayValue($hash_out,'applepay'),XmlFields::returnArrayValue($hash_out,'mpos'));
        $authorizationResponse = $this->processRequest($hash_out,$hash_in,'authorization',$choice_hash);

        return $authorizationResponse;
    }

    /**
     * @param $hash_in
     * @return \DOMDocument|\SimpleXMLElement
     * @throws exceptions\cnpSDKException
     */
    public function saleRequest($hash_in)
    {
        $hash_out = array(
            'cnpTxnId' => XmlFields::returnArrayValue($hash_in,'cnpTxnId'),
            'orderId' =>(XmlFields::returnArrayValue($hash_in,'orderId')),
            'ReturnURL' =>(XmlFields::returnArrayValue($hash_in,'ReturnURL')),
            'amount' =>(XmlFields::returnArrayValue($hash_in,'amount')),
        	'id'=>XmlFields::returnArrayValue($hash_in,'id'),
            'secondaryAmount'=>XmlFields::returnArrayValue($hash_in,'secondaryAmount'),
            'surchargeAmount' =>XmlFields::returnArrayValue($hash_in,'surchargeAmount'),
            'orderSource'=>(XmlFields::returnArrayValue($hash_in,'orderSource')),
            'Address'=>XmlFields::addressType(XmlFields::returnArrayValue($hash_in,'Address')),
            'shipToAddress'=>XmlFields::contact(XmlFields::returnArrayValue($hash_in,'shipToAddress')),
			'Transaction'=> XmlFields::transactionType(XmlFields::returnArrayValue($hash_in,'Transaction'))
        );

        $choice_hash = array($hash_out['Transaction'],$hash_out['paypal'],$hash_out['token'],$hash_out['paypage'],$hash_out['applepay'],$hash_out['mpos']);
        $choice2_hash= array($hash_out['fraudCheck'],$hash_out['cardholderAuthentication']);
        $saleResponse = $this->processRequest($hash_out,$hash_in,'sale',$choice_hash,$choice2_hash);

        return $saleResponse;
    }

    /**
     * @param $hash_in
     * @return array
     */
    private static function overrideConfig($hash_in)
    {
        $hash_config = array();
        $names = explode(',', CNP_CONFIG_LIST);

        foreach ($names as $name) {
            if (array_key_exists($name, $hash_in)) {
                $hash_config[$name] = XmlFields::returnArrayValue($hash_in, $name);
            }
        }

        return $hash_config;
    }

    /**
     * @param $hash_in
     * @param $hash_out
     * @return mixed
     */
    private static function getOptionalAttributes($hash_in,$hash_out)
    {
        if (isset($hash_in['merchantSdk'])) {
            $hash_out['merchantSdk'] = XmlFields::returnArrayValue($hash_in,'merchantSdk');
        } else {
            $hash_out['merchantSdk'] = CURRENT_SDK_VERSION;
        }
        if (isset($hash_in['id'])) {
            $hash_out['id'] = XmlFields::returnArrayValue($hash_in,'id');
        }
        if (isset($hash_in['customerId'])) {
            $hash_out['customerId'] = XmlFields::returnArrayValue($hash_in,'customerId');
        }
        if (isset($hash_in['loggedInUser'])) {
            $hash_out['loggedInUser'] = XmlFields::returnArrayValue($hash_in,'loggedInUser');
        }

        return $hash_out;
    }

    /**
     * @param $hash_out
     * @param $hash_in
     * @param $type
     * @param null $choice1
     * @param null $choice2
     * @return \DOMDocument|\SimpleXMLElement
     * @throws exceptions\cnpSDKException
     */
    private function processRequest($hash_out, $hash_in, $type, $choice1 = null, $choice2 = null)
    {
        $hash_config = CnpOnlineRequest::overrideConfig($hash_in);
        $hash = CnpOnlineRequest::getOptionalAttributes($hash_in,$hash_out);
        $request = Obj2xml::toXml($hash,$hash_config, $type);


//        if(Checker::validateXML($request)){
//            $request = str_replace ("submerchantDebitCtx","submerchantDebit",$request);
//            $request = str_replace ("submerchantCreditCtx","submerchantCredit",$request);
//            $request = str_replace ("vendorCreditCtx","vendorCredit",$request);
//            $request = str_replace ("vendorDebitCtx","vendorDebit",$request);

            $cnpOnlineResponse = $this->newXML->request($request,$hash_config,$this->useSimpleXml);
//        }

        return $cnpOnlineResponse;
    }
    
}
