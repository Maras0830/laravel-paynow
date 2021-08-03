# Paynow SDK
https://github.com/Maras0830/laravel-paynow

## ToDo

- [x] CreditCard Backend Single Transaction
- [x] Transaction Check
- [x] Subscription
- [ ] Installment
- [X] 3-Domain Secure
- [X] Callback Check

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

3. Subscription

> First Transaction

```php
$now = Carbon\Carbon::now('Asia/Taipei');
$transaction = new Maras0830\PayNowSDK\CreditCardTransaction($now);

$card_number = '4023730207292803';
$valid_year = '22';
$valid_month = '05';
$safe_code = '685';

$account = 'account';
$password = 'password';

/**
 * @throws Maras0830\PayNowSDK\Exceptions\PayNowException
 * @throws Maras0830\PayNowSDK\Exceptions\ValidateException
 * @throws Maras0830\PayNowSDK\Exceptions\TransactionException
 */
$res = $transaction
    ->setEncrypt()
    ->setOrder('測試交易', 'TEST123' . time(), 100) // orderinfo strlen > 3
    ->setCreditCard($card_number, $valid_year, $valid_month, $safe_code)
    ->setCustomer(1, 'Eric', '09121212121212', 'test@test.com', '127.0.0.1', $account, $password)
    ->checkoutAndSaveCard()
    ->decodeAndValidate();

/**
$res = [
    "WebNo" => "70828783"
    "TotalPrice" => 100
    "OrderNo" => "TEST1231593574222"
    "ECPlatform" => null
    "BuySafeNo" => "8000002007014585819"
    "TranStatus" => "S"
    "PassCode" => "FDEA87E80373A00FAD48C89AD5BA32A954675678"
    "RespCode" => "00"
    "ResponseMSG" => ""
    "ApproveCode" => "A00001"
    "last4CardNo" => "2803"
    "CheckNo" => null
    "InvoiceNo" => null
    "batchNo" => null
    "InvoiceStatus" => null
    "Result3D" => ""
    "CIFID_SN" => "1"  // 該卡 token, 請保存
    "ReturnURL" => null
    "ErrorMessage" => ""
]
*/
```

> Others Transaction

```php
$now = Carbon\Carbon::now('Asia/Taipei');
$transaction = new Maras0830\PayNowSDK\CreditCardTransaction($now);

$account = 'account';
$password = 'password';
$card_sn = '123'; // CIFID_SN

$res = $transaction
    ->setEncrypt()
    ->setOrder('測試交易', 'TEST123' . time(), 100) // orderinfo strlen > 3
    ->setCustomer(1, 'Eric', '09121212121212', 'test@test.com', '127.0.0.1', $account, $password)
    ->checkoutBySN($card_sn) // safe_code can use 'XXX'
    ->decodeAndValidate();

/**
$res = [
    "WebNo" => "70828783"
    "TotalPrice" => 100
    "OrderNo" => "TEST1231593574222"
    "ECPlatform" => null
    "BuySafeNo" => "8000002007014585819"
    "TranStatus" => "S"
    "PassCode" => "FDEA87E80373A00FAD48C89AD5BA32A954675678"
    "RespCode" => "00"
    "ResponseMSG" => ""
    "ApproveCode" => "A00001"
    "last4CardNo" => "2803"
    "CheckNo" => null
    "InvoiceNo" => null
    "batchNo" => null
    "InvoiceStatus" => null
    "Result3D" => ""
    "CIFID_SN" => "1"  // 該卡 token, 請保存
    "ReturnURL" => null
    "ErrorMessage" => ""
]
*/

```

> Refund Order

```php

$payment_refund = new Maras0830\PayNowSDK\PaymentRefund();

$res = $payment_refund->refund(
    '8000001910145799460',
    '860.0000',
    '退款測試',
    1,
    '',
    '',
    '',
    'harley@gs8899.com.tw',
    'Harley',
    'harley@gs8899.com.tw'
);

```

## 交易錯誤碼

| PayNow Code | Exception Code | Error Msg |
| ----------- | -------------- | --------- |
|0 | 0 | unknown error
| 01 | 01 | 發卡銀行要求回覆(call bank)
| 02 | 02 | 發卡銀行要求回覆(call bank)
| 04 | 04 | 拒絕交易,請聯絡發卡銀行
| 05 | 05 | 發卡銀行拒絕交易
| 06 | 06 | 發卡銀行拒絕交易
| 07 | 07 | 發卡銀行拒絕交易
| 12 | 12 | 拒絕交易,卡號錯誤
| 14 | 14 | 拒絕交易,卡號錯誤
| 15 | 15 | 無此發卡銀行
| 33 | 33 | 拒絕交易,過期卡
| 41 | 41 | 拒絕交易,請聯絡發卡銀行
| 43 | 43 | 拒絕交易,請聯絡發卡銀行
| 51 | 51 | 信用卡額度不足
| 54 | 54 | 拒絕交易，過期卡
| 55 | 55 | 拒絕交易，密碼錯誤
| 56 | 56 | 發卡銀行拒絕交易
| 57 | 57 | 發卡銀行拒絕交易
| 58 | 58 | 發卡銀行拒絕交易
| 62 | 62 | 發卡銀行拒絕交易
| 87 | 87 | 拒絕交易,請聯絡發卡銀行
| 90 | 90 | 銀行系統結算中
| 96 | 96 | 收單行系統功能異常
| 916 | 916 | 主機斷線
| 920 | 920 | 拒絕交易,卡號錯誤
| 922 | 922 | 密碼錯誤
| 933 | 933 | 訂單編號不存在
| 934 | 934 | 發卡行3D頁面驗證錯誤
| 935 | 935 | 發卡行3D頁面驗證錯誤
| 939 | 939 | MID 停用
| N7 | 1007 | 拒絕交易,請聯絡發卡銀行
| P1 | 1011 | 拒絕交易 超過日限額度
| Q1 | 1021 | 發卡銀行拒絕交易

## 取消狀態

| Code | status |
| ---- | ----- |
| 0    | 買家申請退貨 |
| 1    | 買賣家確認 |
| 2    | 銀行退款 |
| 3    | 賣家申請退款 |
