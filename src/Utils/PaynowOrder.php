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
        $additional_info = '', 
        $status = '', 
        $auth_email = ''
    ) {
        $this->result_url = $result_url;
        $this->return_url = $return_url;
        $this->amount = (float) $amount;
        $this->reference = $reference;
        $this->additional_info = $additional_info;
        $this->status = $status;
        $this->auth_email = $email;
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
