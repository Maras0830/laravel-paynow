<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2018/8/2
 * Time: 下午1:11
 */
namespace Maras0830\PayNowSDK;

use SoapClient;

class PayNowSoap
{
    private $client;

    /**
     * PayNowSoap constructor.
     */
    public function __construct()
    {
        $this->client = new SoapClient("https://test.paynow.com.tw/Ws_CardAuthorise.asmx?wsdl", array('soap_version' => SOAP_1_2, 'trace' => true));
    }

    public function getSoapClient()
    {
        return $this->client;
    }
}