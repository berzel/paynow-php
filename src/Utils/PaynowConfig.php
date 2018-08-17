<?php

namespace App\Utils;

use Exception;

class PaynowConfig
{
    // paynow config instance
    private static $instance;

    // configuration data array
    private $data;

    // create a new config instance
    private function __construct(string $configFilePath) 
    {
        $this->data = json_decode(file_get_contents($configFilePath), true);
    }

    // get a new config instance
    public static function getInstance(string $configFilePath = __DIR__ . '/../../config/paynow.json')
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }else{
            self::$instance = new Config($configFilePath);
            return self::$instance;
        }
    }

    // get the value of the specified key
    public function get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }else{
            throw new Exception("Key $key not found in config", 1);
        }
    }
}
