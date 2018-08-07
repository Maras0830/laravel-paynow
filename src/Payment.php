<?php
namespace Maras0830\PayNowSDK;

use Carbon\Carbon;

class Payment extends PayNowPaymentSoap
{
    private $response;

    /**
     * CreditCard constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (is_null(config('paynow'))) {
            throw new PayNowException("You need to publish config file command: php artisan vendor:publish --provider=Maras0830\PayNowSDK\Providers\PayNowServiceProvider");
        }

    }

    /**
     * @param $order_number
     * @param $refundvalue
     * @param int $refundmode [1:發動退貨 2:確認退貨 3:退貨取消]
     * @param null $mem_bankaccno
     * @param null $accountbankno
     * @param null $mem_bankaccount
     * @param null $idRegist
     */
    public function refund($order_number,
                           $refundvalue,
                           $refundmode = 1,
                           $mem_bankaccno = null,
                           $accountbankno = null,
                           $mem_bankaccount = null,
                           $idRegist = null)
    {
        $content = [
            'mem_type' => 3,
            'buysafeno' => $order_number,
            'UserID' => config('paynow.web_no'),
            'mem_password' => config('paynow.password'),
            'mem_bankaccno' => $mem_bankaccno, // refund creditcard nullable
            'accountbankno' => $accountbankno, // refund creditcard nullable
            'mem_bankaccount' => $mem_bankaccount,
            'refundvalue' => $refundvalue,
            'refunddate' => Carbon::today()->format('Ymd'),
            'refundmode' => $refundmode,
            'idRegist' => null
        ];

        $this->response = $this->getSoapClient()->__soapCall('Refund_C', [
            $content
        ]);
    }

    /**
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->response;
    }

}