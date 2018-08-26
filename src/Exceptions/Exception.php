<?php

namespace Berzel\Paynow\Exceptions;

class Exception extends \Exception
{
    public function __construct($message = null) {
        $message ? : 'An error occured';
        parent::__construct($message);
    }
}
