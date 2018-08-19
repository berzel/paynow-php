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
    private $initiate_transaction_url;

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
    private function __construct($id, $key, $init_url)
    {
        $this->id = ($id ? : config('paynow.app_id'));
        $this->integration_key = ($key ? : config('paynow.app_key'));
        $this->initiate_transaction_url = ($init_url ? : config('paynow.init_url'));
    }

    /**
     * Get an instance of the paynow object
     * 
     * @param $config An instance of the paynowconfig class
     * 
     * @return $instance New paynow instance
     */
    public static function getInstance ($id = null, $key = null, $init_url = null)
    {
        if (self::$instance == null) {
            self::$instance = new Paynow($id, $key, $init_url);
        }

        return self::$instance;
    }

    /**
     * Initiate a new transaction with paynow
     * 
     * @return array $order_info Returns an array containing the order information
     * @throws Exception $err Throws an exception if the attempt to initiate the transaction was not successful
     */
    public function initiateTransaction (PaynowOrder $order)
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

        $fields_string = $this->createMsg($values);

        // open cURL connection
    	$ch = curl_init($this->getInitUrl());

    	// Set cURL options
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    	// execute curl 
        $result = curl_exec($ch);
        
        if ($result) {
            $msgArr = $this->parseMsg($result);
            
            if ($msgArr['status'] == 'Error') {
                $err = 'Error Occured While Initiating Transaction';
            } elseif ($msgArr['status'] == 'Ok') {
                // then validate hashes
                $validateHash = $this->createHash($msgArr);
                $paynow_hash = $msgArr['hash'];

                if ($validateHash != $paynow_hash) {
                    $err = 'Hash mismatch';
                } else {
                    $order_info = $msgArr;
                }
            } else {
                $err = 'Unknown status from paynow';
            }

        } else {
            $err = curl_error($ch);
        }

        curl_close($ch);

        if (isset($err)) {
            throw new Exception("Failed to initiate transaction. Reason : $err", 1);
        } else {
            return $order_info;
        }

        return null;
    }

    public function returnFromPaynow ($pollUrl)
    {
        return $this->getUpdateFromPaynow();
    }

    public function getUpdateFromPaynow ($pollUrl)
    {
        $result = $this->getOrderUpdate($pollUrl);

        if ($result) {
            $msgArr = $this->parseMsg($result);
            $validateHash = $this->createHash($msgArr);

            if ($validateHash != $msgArr['hash']) {
                $err = 'Hash mismatch';
            } else {
                $status = $msgArr['status'];
            }

        } else {
            $err = curl_error($ch);
        }

        curl_close($ch);

        if (isset($err)) {
            throw new Exception("An error occured. Description : $err", 1);
        } else {
            return $status;
        }

        return null;
    }

    private function getOrderUpdate ($pollUrl)
    {
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $pollUrl);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        return ($result = curl_exec($ch));
    }

    public function getPaynowId ()
    {
        return $this->id;
    }

    public function getPaynowKey ()
    {
        return $this->intergration_key;
    }

    public function getInitUrl ()
    {
        return $this->initiate_transaction_url;
    }

    public function createOrder (array $fields)
    {
        $result_url = $fields['resulturl'];
        $return_url = $fields['returnurl'];
        $amount = $fields['amount'];
        $reference = $fields['reference'];
        $info = $fields['info'];
        $status = $fields['status'];
        $auth_email = $fields['email'];
        
        return new PaynowOrder($result_url, $return_url, $amount, $reference, $info, $status, $auth_email);
    }
}
