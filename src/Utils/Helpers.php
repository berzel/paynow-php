<?php

namespace App\Utils;

trait Helpers
{
    /**
     * Format the cURL post values into one string and urlencode the string
     * 
     * @param array $values. An array of valkues to be converted
     * @return string
     */
    public function createMsg($values)
    {
    	$fields = array();

    	foreach ($values as $key => $value) {
    		// Urlencode values
    		$fields[$key] = urlencode($value);
    	}

    	// Urlencode the hash
    	$fields['hash'] = urlencode($this->createHash($values));

    	// Format the fields array to one url
    	$fields_string = $this->convertUrl($fields);

    	return $fields_string;
    }

    /**
     * Create a string of key - value pairs in the form of key=value&key=value
     * 
     * @param Array $values. An array of values to be converted to a url
     * @return String
     */
    public function convertUrl($values)
    {
    	// String delimiter
    	$delim = '';

    	// Fields string
    	$fields_string = '';

    	foreach ($values as $key => $value) {
    		$fields_string .= $delim . $key . '=' .$value;
    		$delim = '&';
    	}

    	return $fields_string;
    }

    /**
     * Create a hash value sent in any HTTP POST between the site and paynow
     * 
     * @param Array $values. An array of values to be hashed.
     * @return String
     */
    public function createHash($values)
    {
    	$string = '';

    	foreach ($values as $key => $value) 
        {
    		if (strtoupper($key) != 'HASH') 
            {
    			$string .= $value;
    		}
    	}

    	$string .= $this->getPaynowKey();

    	// hash the string
    	$hash = hash('sha512', $string);

    	return strtoupper($hash);
    }

    /**
     * Convert a url string into an assoc array
     * 
     * @param String to be converted to array
     * @return Array
     */
    public function parseMsg($message)
    {
    	// convert string to array
    	$parts = explode('&', $message);

    	$result = array();

    	foreach ($parts as $key => $value) {
    		$bits = explode('=', $value, 2);
    		$result[$bits[0]] = urldecode($bits[1]);
    	}

    	return $result;
    }
}
