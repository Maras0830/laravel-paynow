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

1. CreditCard backend transaction

```php
$now = Carbon\Carbon::now('Asia/Taipei');
$transaction = new Owlting\PayNow\Transaction($now);

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
