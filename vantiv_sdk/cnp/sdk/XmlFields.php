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
#require_once realpath(dirname(__FILE__)) . "/CnpOnline.php";

class XmlFields
{
    public static function returnArrayValue($hash_in, $key, $maxlength = null)
    {
        $retVal = array_key_exists($key, $hash_in)? $hash_in[$key] : null;
        if ($maxlength && !is_null($retVal)) {
            $retVal = mb_substr($retVal, 0, $maxlength);
        }

        return $retVal;
    }

    public static function addressType($hash_in)
    {
        if (isset($hash_in)) {
            $hash_out = array(
				"BillingZipcode"=>XmlFields::returnArrayValue($hash_in, "BillingZipcode", 20),
				"BillingName"=>XmlFields::returnArrayValue($hash_in, "BillingName", 100),
				"BillingAddress1"=>XmlFields::returnArrayValue($hash_in, "BillingAddress1", 35),
				"BillingCity"=>XmlFields::returnArrayValue($hash_in, "BillingCity", 35),
				"BillingState"=>XmlFields::returnArrayValue($hash_in, "BillingState", 30)
//				"BillingCountry"=>XmlFields::returnArrayValue($hash_in, "BillingCountry", 3)
            );

            return $hash_out;
        }

    }
    
    public static function cardType($hash_in)
    {
        if (isset($hash_in)) {
            $hash_out= 	array(
                        "CardNumber"=>XmlFields::returnArrayValue($hash_in, "CardNumber"),
                        "ExpirationMonth"=>XmlFields::returnArrayValue($hash_in, "ExpirationMonth"),
                        "ExpirationYear"=>XmlFields::returnArrayValue($hash_in, "ExpirationYear"),
                        "CVV"=>XmlFields::returnArrayValue($hash_in, "CVV")
            );

            return $hash_out;
        }
    }
	
	public static function transactionType($hash_in)
	{
		if (isset($hash_in)) {
			$hash_out= 	array(
				"TransactionAmount"=>XmlFields::returnArrayValue($hash_in, "TransactionAmount"),
				"ReferenceNumber"=>XmlFields::returnArrayValue($hash_in, "ReferenceNumber")
			);
			
			return $hash_out;
		}
	}

 

}
