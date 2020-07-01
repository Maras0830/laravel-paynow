<?php
namespace Maras0830\PayNowSDK;

use SoapClient;

class PayNowInvoiceSoap
{

    private $client;

    /**
     * PayNowSoap constructor.
     */
    public function __construct()
    {
        ini_set('default_socket_timeout', config('paynow.timeout', 180));

        if (config('paynow.debug_mode') === true)
            $this->client = new SoapClient("https://testinvoice.paynow.com.tw/PayNowEInvoice.asmx?wsdl", array('soap_version' => SOAP_1_2, 'trace' => true, "connection_timeout" => config('paynow.connection_timeout', 300)));
        else
            $this->client = new SoapClient("https://invoice.paynow.com.tw/PayNowEInvoice.asmx?wsdl", array('soap_version' => SOAP_1_2, 'trace' => true, "connection_timeout" => config('paynow.connection_timeout', 300)));
    }

    /**
     * @return SoapClient
     */
    public function getSoapClient()
    {
        return $this->client;
    }
}
