<?php
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
class Obj2xml
{
    public static function toXml($data, $hash_config, $type, $rootNodeName = 'TransactionSetup')
    {
		$config= Obj2xml::getConfig($hash_config, $type);
		$xml = simplexml_load_string("<$rootNodeName />");
//		$xml->addAttribute('version',CURRENT_XML_VERSION);
		$xml->addAttribute('xmlns:xmlns','https://transaction.elementexpress.com');// does not show up on browser docs
		$credentials = $xml->addChild('Credentials');
		$credentials->addChild('AccountID',$config["AccountID"]);
		$credentials->addChild('AccountToken',$config["AccountToken"]);
		$credentials->addChild('AcceptorID',$config["AcceptorID"]);
	
		$application = $xml->addChild('Application');
		$application->addChild('ApplicationID',$config["ApplicationID"]);
		$application->addChild('ApplicationVersion',$config["ApplicationVersion"]);
		$application->addChild('ApplicationName',$config["ApplicationName"]);
		
		$transactionSetup = $xml->addChild('TransactionSetup');
		$transactionSetup->addChild('TransactionSetupMethod',$config["TransactionSetupMethod"]);
		$transactionSetup->addChild('DeviceInputCode',$config["DeviceInputCode"]);
		$transactionSetup->addChild('Device',$config["Device"]);
		$transactionSetup->addChild('Embedded',$config["Embedded"]);
		$transactionSetup->addChild('CVVRequired',$config["CVVRequired"]);
		$transactionSetup->addChild('CompanyName',$config["CompanyName"]);
		$transactionSetup->addChild('AutoReturn',$config["AutoReturn"]);
		$transactionSetup->addChild('WelcomeMessage',$config["WelcomeMessage"]);
		$transactionSetup->addChild('ReturnURL',$data['ReturnURL']);
		
		$address = $xml->addChild('Address');
		$address->addChild('AddressEditAllowed',$config["AddressEditAllowed"]);
		Obj2xml::iterateChildren($data['Address'], $address);
		
		$terminal = $xml->addChild('Terminal');
		$terminal->addChild('TerminalID',$config["TerminalID"]);
		$terminal->addChild('CardholderPresentCode',$config["CardholderPresentCode"]);
		$terminal->addChild('CardInputCode',$config["CardInputCode"]);
		$terminal->addChild('TerminalCapabilityCode',$config["TerminalCapabilityCode"]);
		$terminal->addChild('TerminalEnvironmentCode',$config["TerminalEnvironmentCode"]);
		$terminal->addChild('CardPresentCode',$config["CardPresentCode"]);
		$terminal->addChild('MotoECICode',$config["MotoECICode"]);
		$terminal->addChild('CVVPresenceCode',$config["CVVPresenceCode"]);
		
		$transaction = $xml->addChild('Transaction');
		Obj2xml::iterateChildren($data['Transaction'],$transaction);
		$transaction->addChild('MarketCode', $config["MarketCode"]);
		$transaction->addChild('DuplicateCheckDisableFlag', $config["DuplicateCheckDisableFlag"]);
		var_dump($xml->asXML());
		return $xml->asXML();
    }



    private static function iterateChildren($data,$transacType)
    {

        foreach ($data as $key => $value) {
            //print $key . " " . $value . "\n";
            if ($value === "REQUIRED") {
                throw new \InvalidArgumentException("Missing Required Field: /$key/");
            } else{
				$node = $transacType->addChild($key);
				Obj2xml::iterateChildren($value,$node);
			}
        }
    }

    public static function getConfig( $data, $type = NULL )
    {
        $config_array = null;

        $ini_file = realpath( dirname( __FILE__ ) ) . '/cnp_SDK_config.ini';
        if ( file_exists( $ini_file ) ) {
            @$config_array = parse_ini_file( 'cnp_SDK_config.ini' );
        }
		if ( empty( $config_array ) ) {
			$config_array = array();
		}
	
		$names = explode( ',', CNP_CONFIG_LIST );
		foreach ( $names as $name ) {
            if ( isset($data[ $name ] ) ) {
                $config[ $name ] = $data[ $name ];
				
            } else {
                if ( $name == 'AccountID' ) {
                    $config['AccountID'] = $config_array['AccountID'];
                } elseif ( $name == 'ApplicationVersion' ) {
                    $config['ApplicationVersion'] = isset( $config_array['ApplicationVersion'] ) ? $config_array['ApplicationVersion'] : CURRENT_XML_VERSION;
                } elseif ( $name == 'URL' ) {
					$config['URL'] = isset( $config_array['URL'] ) ? $config_array['URL'] : CURRENT_XML_VERSION;
				} else {
                    if ( ( ! isset( $config_array[ $name ] ) ) ) {
                        throw new \InvalidArgumentException( "Missing Field /$name/" );
                    }
					$config[ $name ] = $config_array[ $name ];
				}
            }
        }
        return $config;
    }
}
