<?php
return [
    // debug mode
    // true => 測試環境WSDL： https://test.paynow.com.tw/Ws_CardAuthorise.asmx
    // false => 正式環境WSDL： https://www.paynow.com.tw/Ws_CardAuthorise.asmx
    'debug_mode' => env('PAYNOW_DEBUG_MODE', true),

    'web_no' => env('PAYNOW_WEB_NO', '12345678'),

    'password' => env('PAYNOW_PASSWORD', '1234'),

    'ec_name' => env('PAYNOW_EC_NAME', 'ECName'),

    'encrypt_key' => env('PAYNOW_ENCRYPT_KEY'),

    'iv' => env('PAYNOW_IV'),

    'cardinal' => env('PAYNOW_CARDINAL'),
];
