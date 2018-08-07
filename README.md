# Paynow SDK
https://github.com/Maras0830/laravel-paynow

## Installation

Enable php extension soap
http://php.net/manual/en/book.soap.php

composer require
```bash
composer require "Maras0830/laravel-paynow=^v0.1"
```

add to ```config/app.php```
```
'providers' => [
   ...,
   Maras0830\PayNowSDK\Providers\PayNowServiceProvider::class
 ],
```

publish config file to *config/paynow.php*
```
$ php artisan vendor:publish --provider=Maras0830\PayNowSDK\Providers\PayNowServiceProvider
```

in .env
```
PAYNOW_DEBUG_MODE=true
PAYNOW_WEB_NO=
PAYNOW_PASSWORD=
PAYNOW_EC_NAME=
```

### Example

測試卡號： 4025950011112222
有效年月： 12/12
安全碼：999

1. save card (儲存卡片，並且刷卡)
```php
    $creditcard = new Maras0830\PayNowSDK\CreditCard();

    $creditcard
        ->setOrder('訂購', 'ORD201800000001', 30)
        ->setCustomer(1, 'Maras', '0912345678', 'maraschen@codingweb.tw', '127.0.0.1')
        ->setCustomerCIF('1234', '1234')
        ->setCreditCard('4025950011112222', '12', '12', '999')
        ->autoPay(1);

    $response = $creditcard->getLastResponse();
```

2. auto_checkout (根據 CIF 資訊結帳)
```php
    $creditcard = new Maras0830\PayNowSDK\CreditCard();

    $creditcard
        ->setOrder('訂購', 'ORD201800000001', 30)
        ->checkoutByCIFAndSecretCode( '1', '1234','1234','999');
	
    $response = $creditcard->getLastResponse();
```

3. installment($times=1)
```php

    $creditcard = new Maras0830\PayNowSDK\CreditCard();

    $creditcard
        ->setOrder('訂購', 'ORD201800000001', 30)
        ->setCustomer(1, 'Maras Chen', '0912345678', 'maraschen@codingweb.tw', '127.0.0.1')
        ->setCustomerCIF('5566', '5566')
        ->setCreditCard('4025950011112222', '12', '12', '999')
        ->installment(3);

    $response = $creditcard->getLastResponse();
```


2. refund
```php
    $payment = new Maras0830\PayNowSDK\Payment();

    $payment->refund('800123123123123', '退款');

    $response = $creditcard->getLastResponse();
```
