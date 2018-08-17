<?php

namespace App\Utils;

class PaynowOrder
{
    private $result_url;
    private $return_url;
    private $amount;
    private $reference;
    private $additional_info;
    private $status;
    private $auth_email;

    public function __construct (
        $result_url, 
        $return_url, 
        $amount, 
        $reference = '', 
        $info = '', 
        $status = '', 
        $auth_email = ''
    ) {
        $this->setResultUrl($result_url);
        $this->setReturnUrl($return_url);
        $this->setAmount((float)$amount);
        $this->setReference($reference);
        $this->setAdditionalInfo($info);
        $this->setStatus($status);
        $this->setAuthUserEmail($auth_email);
    }

    public function setAuthUserEmail ($email)
    {
        $this->auth_email = $email;
    }

    public function setStatus ($status)
    {
        $this->status = $status;
    }

    public function setAdditionalInfo ($info)
    {
        $this->additional_info = $info;
    }

    public function setReference ($reference)
    {
        $this->reference = $reference;
    }

    public function setAmount (float $amount)
    {
        $this->amount = $amount;
    }

    public function setReturnUrl (string $url)
    {
        $this->return_url = $url;
    }

    public function setResultUrl (string $url)
    {
        $this->result_url = $url;
    }

    public function getResultUrl ()
    {
        return $this->result_url;
    }

    public function getReturnUrl ()
    {
        return $this->return_url;
    }

    public function getAmount ()
    {
        return (float) $this->amount;
    }

    public function getReference ()
    {
        return $this->reference;
    }

    public function getAdditionalInfo ()
    {
        return $this->additional_info;
    }

    public function getStatus ()
    {
        return $this->status;
    }

    public function getAuthUserEmail ()
    {
        return $this->auth_email;
    }
}
