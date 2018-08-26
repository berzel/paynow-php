<?php

namespace Berzel\Paynow\Utils;

class PaynowOrder
{
    private $result_url;
    private $return_url;
    private $amount;
    private $reference;
    private $additional_info;
    private $status;
    private $auth_email;

    public static $requiredKeys = ['resulturl','returnurl', 'amount', 'reference', 'info', 'status', 'email'];

    public function __construct ($fields) {
        $this->setResultUrl($fields['resulturl']);
        $this->setReturnUrl($fields['returnurl']);
        $this->setAmount((float)$fields['amount']);
        $this->setReference($fields['reference']);
        $this->setAdditionalInfo($fields['info']);
        $this->setStatus($fields['status']);
        $this->setAuthUserEmail($fields['email']);
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
