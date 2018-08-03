# Paynow SDK
https://github.com/Maras0830/laravel-paynow

## Install
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

1. checkout()
	

### Example
```php
    $sdk = new Maras0830\PayNowSDK\CreditCard();

    $sdk
        ->setOrder('GSX-R1000', 'ODR20180802000005', 30)
        ->setCustomer('Maras Chen', '0912345678', 'maraschen@codingweb.tw', '127.0.0.1')
        ->setCustomerCIF('1234', '1234')
        ->setCreditCard('4025950011112222', '12', '12', '999')
        ->checkout();

    dd($sdk->getLastResponse());
```

2. installment($times = 1)
