## Paynow-Php

A simple Paynow library (implementation using PHP) which provides an expressive and fluent interface to the Paynow payments 

gateway. It handles pretty much all of the boilerplate code you dreading writing. 

## Installation

Install the package through [Composer](http://getcomposer.org/). 

Run the Composer require command from the Terminal:

    composer require berzel/paynow-php
    
If you're using Laravel 5.5 or later, this is all there is to do. 

Should you still be on version 5.4 of Laravel, the final steps for you are to add the service provider of the package and 

alias the package. To do this open your `config/app.php` file.

Add a new line to the `providers` array:

	Berzel\Paynow\PaynowServiceProvider::class

And optionally add a new line to the `aliases` array:

	'Paynow' => Berzel\Paynow\Facades\Paynow::class,

Now you're ready to start using the library in your application. Please note that this is not an official liabrary from 

Paynow.

## Overview

Look at one of the following topics to learn more about this library

* [Usage](#usage)
* [Example](#example)

## Usage

This library gives you the following methods to use:

### Paynow::getInstance()

Getting/Creating an instance of the paynow class is really simple, you just use the `getInstance()` method, which accepts 

three parameters. In its most basic form you just specify the id, key and paynow endpoint to initiate the transaction, of 

your paynow integration. You can retrieve your Paynow app id and app key from the Paynow control panel.

```php
$paynow = Paynow::getInstance($appId, $appKey, $initUrl);
```

**The `getInstance()` method will return a Berzel\Paynow\Paynow instance.**


### Paynow::initiateTransaction()

Paynow expects you to supply a couple of values when initiating a transaction. These values are 

    - return url => the url that the user will be redirected to after completing a transaction on paynow
    - result url => the url that paynow will post order updates to
    - amount => the amount that you wish to charge the user 
    - reference => the order reference number/string
    - info => additional information about the order
    - status => the status of the order
    - email => the email address to associate with this order

The `initiateTransaction()` method will attempt to initiate a transaction on the paynow platform, and it accepts a 

PaynowOrder instance which you can create using the `createOrder()` method (documented below). The `initiateTransaction()`

method will throw an Exception if the attempt to initiate a transaction fails. If the request was successful the full Paynow

response array will be returned from the method. The array will contain a 'browserurl' key value which you can use to 

redirect the user to Paynow to complete the payment. The 'pollurl' key value contains the url on paynow that you can use in

future to get status updates about the order (You should save this to you local storage engine for future use). Other keys

that are contained in the result array are the 'status', 'hash', etc, of which you are encouraged to also save to your local

storage for future use.


```php
$fields = [$resultUrl, $returnUrl, $amount, $reference, $info, $status, $email];

$order = $paynow->createOrder($fields);

$result = $paynow->initiateTransaction($order);
```

If an error occurs you can catch the exception and use the `getMessage()` method to get information about why the 

transaction failed to initiate.


### Paynow::createOrder()

To create a paynow order instance use the `createOrder()` method, which retains an instance of the PaynowOrder class which 

is to be passed as an argument to the `initiateTransaction()` method. The method accepts an array as an argument which 

should contain the details of your order. This array should contain these keys resulturl, returnurl, amount, reference, info,

status, and email. All fields are required and if any of the keys is not found the method will throw an exception.

```php
$fields = [
    'resulturl' => $resUrl,
    'returnurl' => $retUrl,
    'amount' => $amt,
    'reference' => $ref,
    'info' => $info,
    'status' => $status,
    'email' => $email
];

$order = $paynow->createOrder($fields);
```

### Paynow::returnFromPaynow and Paynow::updateFromPaynow

When you are returning from paynow after the user has completed payment. You can call the `returnFromPaynow()` or

`updateFromPaynow()` method. This method throws an exception if anything unexpected happens when requesting the Paynow  

platform for updates. If everything goes well it will return the current/latest status(Paid, Cancelled, Awaiting Delivery,

etc) of the order on paynow. Both methods accept an argument which is the poll url returned earlier when trying to initiate

a transaction.

```php
$orderStatus = $paynow->returnFromPaynow($order->pollUrl);
```

## Example

# Initiating a transaction on paynow

```php

use Berzel\Paynow\Paynow;

public function checkout($cart)
{
    // lets grab the id and key and initUrl from config
    $id = config('paynow.id');
    $key = config('paynow.key');
    $initUrl = config('paynow.init_url');

    //begin a database transaction
    $this->db->beginTransaction();

    //create a new order 
    $order = $this->user()->createOrder($cart);

    //order details. Please note I am using laravel's route helper function here but any valid url will do
    $fields = [
        'resulturl' => route('order.result', $order->getId()),
        'returnurl' => route('order.return', $order->getId()),
        'amount' => $order->getTotal(),
        'reference' => $order->referenceNumber(),
        'info' => $order->getAdditionalInfo(),
        'status' => $order->getCurrentStatus(),
        'email' => $order->user()->getEmail()
    ];


    try {
        //create/get a new paynow instance
        $paynow = Paynow::getInstance($id, $key, $initUrl);

        // initiate a transaction on paynow
        $result = $paynow->initiateTransaction($paynow->createOrder($fields));

        // before redirecting the user to paynow lets save the results to local db
        $order->setStatus($result['status']);

        $order->setPaynowPollUrl($result['pollurl']);

        $order->save();

        // commit database changes
        $this->db->commit();
        
        // redirect the user to paynow
        return redirect()->url($result['browserurl']);

    } catch (Exception $e) {
        // rollback database changes
        $this->db->rollBack();

        $err = $e->getMessage();
        // You might want to display this to the user
        return redirect()->route('checkout')->with($err);
    }
}

```

# When returning from paynow

```php

public function getFromPaynow(Order $order)
{
    try {
        $orderStatus = Paynow::getInstance($id, $key, $initUrl)->returnFromPaynow($order->getPollUrl());
        
        // show the user that the order status

    } catch (Exception $e) {
        // show the user the error that occured
    }
}

```

# When just getting an update from paynow

```php

// this could be where you are showing the user his/her order details
public function show(Order $order)
{
    try {
        $orderStatus = Paynow::getInstance($id, $key, $initUrl)->updateFromPaynow($order->pollUrl);

        // show the user the user the status
    } catch (Exception $e) {
        // show the user the error message
    }
}

```

**For now that will be all.**
