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
require_once realpath(dirname(__FILE__)) . '/UrlMapper.php';

function writeConfig($line,$handle)
{
    foreach ($line as $keys => $values) {
        fwrite($handle, $keys. '');
        if (is_array($values)) {
            foreach ($values as $key2 => $value2) {
                fwrite($handle,"['" . $key2 . "'] =" . $value2 .  PHP_EOL);
            }
        } else {
            fwrite($handle,' =' . $values);
            fwrite($handle, PHP_EOL);
        }
    }
}

function initialize()
{
    $line = array();

    $handle = @fopen('./cnp_SDK_config.ini', "w");
    if ($handle) {
        print "Welcome to Vantiv Express eCommerce" . PHP_EOL;
        print "Please input your AccountID: ";
        $line['AccountID'] = formatConfigValue(STDIN);
        print "Please input your AccountToken: ";
        $line['AccountToken'] = formatConfigValue(STDIN);
        print "Please input your AcceptorID: ";
        $line['AcceptorID'] = formatConfigValue(STDIN);
        print "Please input your ApplicationID: ";
        $line['ApplicationID'] = formatConfigValue(STDIN);
        print "Please input your ApplicationVersion: ";
        $line['ApplicationVersion'] = formatConfigValue(STDIN);
        print "Please input your ApplicationName: ";
        $line['ApplicationName'] = formatConfigValue(STDIN);
        print "Please input your TerminalID: ";
        $line['TerminalID'] = formatConfigValue(STDIN);
        print "Please input your TerminalCapabilityCode: ";
        $line['TerminalCapabilityCode'] = formatConfigValue(STDIN);
        print "Please input your TerminalCapabilityCode: ";
        $line['TerminalCapabilityCode'] = formatConfigValue(STDIN);
        print "Please input your CardholderPresentCode: ";
        $line['CardholderPresentCode'] = formatConfigValue(STDIN);
        print "Please input your CardInputCode: ";
        $line['CardInputCode'] = formatConfigValue(STDIN);
        print "Please input your CardPresentCode: ";
        $line['CardPresentCode'] = formatConfigValue(STDIN);
        print "Please input your MotoECICode: ";
        $line['MotoECICode'] = formatConfigValue(STDIN);
        print "Please input your CVVPresenceCode: ";
        $line['CVVPresenceCode'] = formatConfigValue(STDIN);
        print "Please choose url from the following list (example: 'sandbox') or directly input another URL: \n" .
            "sandbox => 'https://certtransaction.elementexpress.com/' \n" .
            "transact-express => https://certtransaction.elementexpress.com/ \n" .
        $url = UrlMapper::getUrl(trim(fgets(STDIN)));
        
        if (is_array($url)){
            $line['URL'] = $url[0];
        }else {
            $line['URL'] = $url;
        }

		$line['TransactionSetupMethod'] = formatConfigValue(STDIN);
		$line['DeviceInputCode'] = formatConfigValue(STDIN);
		$line['Device'] = formatConfigValue(STDIN);
		$line['Embedded'] = formatConfigValue(STDIN);
		$line['CVVRequired'] = formatConfigValue(STDIN);
		$line['CompanyName'] = formatConfigValue(STDIN);
		$line['AutoReturn'] = formatConfigValue(STDIN);
		$line['WelcomeMessage'] = formatConfigValue(STDIN);
		$line['AddressEditAllowed'] = formatConfigValue(STDIN);
		$line['MarketCode'] = formatConfigValue(STDIN);
		$line['DuplicateCheckDisableFlag'] = formatConfigValue(STDIN);
        writeConfig($line,$handle);
    }
    fclose($handle);
    print "The Vantiv eCommerce configuration file has been generated, " .
        "the file is located in the lib directory". PHP_EOL;
}

function formatConfigValue($str){
    return "\"" . trim(fgets($str)) . "\"";
}

initialize();
