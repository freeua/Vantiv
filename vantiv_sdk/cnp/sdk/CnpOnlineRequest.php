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
     * @param $hash_in
     * @return \DOMDocument|\SimpleXMLElement
     * @throws exceptions\cnpSDKException
     */
    public function saleRequest($hash_in)
    {
        $hash_out = array(
            'ReturnURL' =>(XmlFields::returnArrayValue($hash_in,'ReturnURL')),
            'Address'=>XmlFields::addressType(XmlFields::returnArrayValue($hash_in,'Address')),
			'Transaction'=> XmlFields::transactionType(XmlFields::returnArrayValue($hash_in,'Transaction'))
        );

        $choice_hash = array($hash_out['Transaction'],$hash_out['paypal'],$hash_out['token'],$hash_out['paypage'],$hash_out['applepay'],$hash_out['mpos']);
        $choice2_hash= array($hash_out['fraudCheck'],$hash_out['cardholderAuthentication']);
        $saleResponse = $this->processRequest($hash_out,$hash_in,'sale',$choice_hash,$choice2_hash);

        return $saleResponse;
    }
	
	/**
	 * @param $hash_in
	 * @return \DOMDocument|\SimpleXMLElement
	 * @throws exceptions\cnpSDKException
	 */
	public function refund_transaction( $order, $amount = null, $reason = '', $config = array() )
	{
		$request = Obj2xml::toXmlRefund( $order, $amount, $reason, $config );
		
		$cnpOnlineResponse = $this->newXML->request($request,$config,$this->useSimpleXml);
		
		return $cnpOnlineResponse;
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

		$cnpOnlineResponse = $this->newXML->request($request,$hash_config,$this->useSimpleXml);

        return $cnpOnlineResponse;
    }
    
}
