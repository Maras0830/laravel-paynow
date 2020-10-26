# Paynow SDK
https://github.com/Maras0830/laravel-paynow

## ToDo

- [x] CreditCard Backend Single Transaction
- [x] Transaction Check

## Installation

Enable php extension soap
http://php.net/manual/en/book.soap.php

composer require
```bash
$ composer require maras0830/laravel-paynow"
```

add to `config/app.php`
```php
'providers' => [
   // ....
   Maras0830\PayNowSDK\Providers\PayNowServiceProvider::class
],
```

publish config file to *config/paynow.php*
```
$ php artisan vendor:publish --provider=Maras0830\PayNowSDK\Providers\PayNowServiceProvider
```

in `.env`
```dotenv
PAYNOW_DEBUG_MODE=true
PAYNOW_WEB_NO=
PAYNOW_PASSWORD=
PAYNOW_EC_NAME=
PAYNOW_ENCRYPT_KEY=
PAYNOW_IV=
PAYNOW_CARDINAL=
```

### Usage

測試卡號： 4025950011112222
有效年月： 12/12
安全碼：999

1. CreditCard backend transaction

```php
$now = \Carbon\Carbon::now('Asia/Taipei');
$transaction = new Maras0830\PayNowSDK\CreditCardTransaction($now);

$card_number = '4023730207292803';
$valid_year = '20';
$valid_month = '05';
$safe_code = '685';
    
// $res is transaction response.
$res = $transaction
    ->setEncrypt()
    ->setOrder('測試交易', 'OWLTEST1000111002', 100) // orderinfo strlen > 3
    ->setCreditCard($card_number, $valid_year, $valid_month, $safe_code)
    ->setCustomer(1, 'Eric', '09121212121212', 'test@test.com', '127.0.0.1')
    ->checkout()
    ->decodeAndValidate();
```

2. Transaction Check

```php
$my_order_number = 'TEST10001';

$sdk = new Maras0830\PayNowSDK\PayNowAPI();

/**
 * @throws Maras0830\PayNowSDK\Exceptions\OrderNotFoundException
 * @throws Maras0830\PayNowSDK\Exceptions\TransactionException
 * @throws Maras0830\PayNowSDK\Exceptions\OrderIsCancelException
 * @throws Maras0830\PayNowSDK\Exceptions\UnKnownException
 */
$res = $sdk->transactionCheck($my_order_number);

/**
$res => [
    'order_number' => 'xxxxx' // Paynow Order number
    'last4' => 'xxxx'         // CreditCard last4 code
];
**/
```
