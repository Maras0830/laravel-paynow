<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2018/8/7
 * Time: 下午3:38
 */
namespace Maras0830\PayNowSDK;

use SoapClient;

class PayNowPaymentSoap
{
    private $client;

    /**
     * PayNowSoap constructor.
     */
    public function __construct()
    {
        if (config('paynow.debug_mode') === true)
            $this->client = new SoapClient("http://test.paynow.com.tw/PaymentCheck.asmx?wsdl", array('soap_version' => SOAP_1_2, 'trace' => true));
        else
            $this->client = new SoapClient("http://www.paynow.com.tw/PaymentCheck.asmx?wsdl", array('soap_version' => SOAP_1_2, 'trace' => true));
    }

    /**
     * @return SoapClient
     */
    public function getSoapClient()
    {
        return $this->client;
    }
}