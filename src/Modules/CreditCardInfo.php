<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2018/8/1
 * Time: 下午5:05
 */

namespace Maras0830\PayNowSDK\Modules;


use Maras0830\PayNowSDK\PayNowSoap;

class CreditCardInfo extends PayNowSoap
{
    public $secret_card_number;
    private $hash;

    /**
     * PayNowCreditCard constructor.
     * @param $card_number
     * @param $valid_year
     * @param $valid_month
     * @param $safe_code
     */
    public function __construct($card_number, $valid_year, $valid_month, $safe_code)
    {
        $this->hash = str_pad(config('paynow.password'),8,"0");

        $this->encrypt($card_number, $valid_year, $valid_month, $safe_code);
    }

    private function encrypt($card_number, $valid_year, $valid_month, $safe_code)
    {
        if (config('paynow.debug_mode') === true)
            $card_info = "402595001111222299912/12";
        else
            $card_info = $card_number . $safe_code . $valid_month . '/' . $valid_year;

        $this->hash = '1234567890' . $this->hash . '123456';

        $this->secret_card_number = base64_encode(openssl_encrypt($card_info, 'DES-EDE3', $this->hash, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING));
    }

    public function decrypt($data)
    {
        $data = base64_decode($data);

        $this->hash = '1234567890' . $this->hash . '123456';

        $decData = openssl_decrypt($data, 'DES-EDE3', $this->hash, OPENSSL_RAW_DATA);

        return $decData;
    }
}