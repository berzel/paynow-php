<?php

namespace Berzel\Paynow;

use Berzel\Paynow\Utils\Helpers;
use Berzel\Paynow\Utils\PaynowOrder;
use Berzel\Paynow\Exceptions\Exception;

class Paynow
{
    use Helpers;

    /**
     * The paynow instance.
     *
     * @var string
     */
    private static $instance;

    /**
     * The URL that is used to initiate a transaction on paynow.
     *
     * @var string
     */
    private $initiate_transaction_url = 'https://www.paynow.co.zw/interface/initiatetransaction';

    /**
     * The unique paynow app intergration key
     *
     * @var string
     */
    private $integration_key;

    /**
     * The unique paynow app id.
     *
     * @var int
     */
    private $id;

    /**
     * Create paynow instance.
     *
     * @var string
     */
    private function __construct($id, $key)
    {
        $this->id = $id;
        $this->integration_key = $key;
    }

    /**
     * Get an instance of the paynow object
     * 
     * @param $config An instance of the paynowconfig class
     * 
     * @return $instance New paynow instance
     */
    public static function getInstance ($id, $key)
    {
        return self::$instance ? : new Paynow($id, $key);
    }

    /**
     * Initiate a new transaction with paynow
     * 
     * @return array $order_info Returns an array containing the order information
     * @throws Exception $err Throws an exception if the attempt to initiate the transaction was not successful
     */
    public function initiateTransaction (PaynowOrder $order)
    {
        // open cURL connection
    	$result = $this->curlToPaynowInit($this->createMsg($this->makeValues($order)));
        
        if ($result) {
            return $this->parsePaynowResult($result);
        }
    }

    private function parsePaynowResult ($result)
    {
        $msgArr = $this->parseMsg($result);
            
        if ($msgArr['status'] == 'Error') {
            throw new Exception("Error occured while initiating transaction", 1);
        } elseif ($msgArr['status'] == 'Ok') {
            if ($this->validateHash($msgArr)) {
                return $msgArr;
            }
        } else {
            throw new Exception("Unknown error occured", 1);
        }
    }

    private function validateHash ($msgArr)
    {
        $validHash = $this->createHash($msgArr);
        $paynow_hash = $msgArr['hash'];

        if ($validHash != $paynow_hash) {
            throw new Exception("Hash mismatch", 1);
        } else {
            return true;
        }
    }

    private function curlToPaynowInit ($fields_string)
    {
        $ch = curl_init($this->getInitUrl());

    	// Set cURL options
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    	// execute curl 
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new Exception("Error Processing Request : $err", 1);
        }

        return $result;
    }

    // this is excatly how paynow requires you to have your keys
    private function makeValues ($order)
    {
        $values = [
            'resulturl'      => $order->getResultUrl(),
    		'returnurl'      => $order->getReturnUrl(),
    		'reference'      => $order->getReference(),
    		'amount'         => $order->getAmount(),
    		'id'             => $this->getPaynowId(),
    		'additionalinfo' => $order->getAdditionalInfo(),
    		'authemail'      => $order->getAuthUserEmail(),
    		'status'         => $order->getStatus()
        ];

        return $values;
    }

    // called when getting from paynow
    public function returnFromPaynow ($pollUrl)
    {
        return $this->getUpdateFromPaynow($pollUrl);
    }

    // called when getting an update from paynow
    public function getUpdateFromPaynow ($pollUrl)
    {
        $result = $this->getOrderUpdate($pollUrl);

        if ($result) {
            $msgArr = $this->parseMsg($result);
           
            if ($this->validateHash($msgArr)) {
                return $msgArr;
            }
        }
    }

    // curl to paynow
    private function getOrderUpdate ($pollUrl)
    {
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $pollUrl);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new Exception("Error Processing Request: $err", 1);
        }

        return $result;
    }

    // get the id of the integration
    public function getPaynowId ()
    {
        return $this->id;
    }

    // get the integration key
    public function getPaynowKey ()
    {
        return $this->integration_key;
    }

    // get the init url 
    public function getInitUrl ()
    {
        return $this->initiate_transaction_url;
    }

    // create a new paynow order
    public function createOrder (array $fields)
    {
        if ($this->arrayHasAllRequiredKeys($fields)) {
            return new PaynowOrder($fields);
        }
    }

    private function arrayHasAllRequiredKeys ($fields)
    {
        foreach (PaynowOrder::$requiredKeys as $key) {
            if (!array_key_exists($key, $fields)) {
                throw new Exception("Key $key not found in field values", 1);
            }
        }

        return true;
    }
}
