<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2018/8/2
 * Time: 下午1:01
 */

namespace Maras0830\PayNowSDK;


use Maras0830\PayNowSDK\Modules\CreditCardInfo;
use Maras0830\PayNowSDK\Modules\Customer;
use Maras0830\PayNowSDK\Modules\Order;

class CreditCard extends PayNowSoap
{
    protected $order;
    protected $creditCard;
    protected $customer;
    private $response;

    /**
     * CreditCard constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getLastResponse()
    {
        return $this->response;
    }

    public function setOrder($info, $number, $total)
    {
        $this->order = new Order($info, $number, $total);

        return $this;
    }

    public function setCustomer($name, $tel, $email, $ip)
    {
        $this->customer = new Customer($name, $tel, $email, $ip);

        return $this;
    }

    public function setCustomerCIF($CIFID, $CIFPW)
    {
        if (is_null($this->customer))
            throw new \Exception('You need to setCustomer first.');

        $this->customer->setCIFID($CIFID);

        $this->customer->setCIFPW($CIFPW);

        return $this;
    }

    public function setCreditCard($card_number, $valid_year, $valid_month, $safe_code)
    {
        $this->creditCard = new CreditCardInfo($card_number, $valid_year, $valid_month, $safe_code);

        return $this;
    }

    public function checkout()
    {
        if (is_null($this->order))
            throw new \Exception('You need to setOrder');

        if (is_null($this->customer))
            throw new \Exception('You need to setCustomer');

        if (is_null($this->creditCard))
            throw new \Exception('You need to setCreditCard');

        $content = [
            'userIP' => $this->customer->ip,
            'WebNo' => config('paynow.web_no', '53092632'),
            'TotalPrice' => $this->order->total,
            'OrderInfo' => $this->order->info,
            'OrderNo' => $this->order->number,
            'ReceiverName' => $this->customer->name,
            'ReceiverTel' => $this->customer->tel,
            'ReceiverEmail' => $this->customer->email,
            'CardNo' => $this->creditCard->secret_card_number,
            'ECPlatform' => config('paynow.ec_name', 'Owlting')
        ];

        $this->response = $this->getSoapClient()->__soapCall('Add_CardAuthorise', [
            $content
        ]);

        return $this->response;
    }

    public function autoPay($times = 1)
    {
        if (is_null($this->order))
            throw new \Exception('You need to setOrder');

        if (is_null($this->customer))
            throw new \Exception('You need to setCustomer');

        if (is_null($this->creditCard))
            throw new \Exception('You need to setCreditCard');

        if (is_null($this->customer->getCIFID()))
            throw new \Exception('You need to set CIF ID');

        if (is_null($this->customer->getCIFPW()))
            throw new \Exception('You need to set CIF Password');

        $content = [
            'userIP' => $this->customer->ip,
            'WebNo' => config('paynow.web_no'),
            'TotalPrice' => $this->order->total,
            'OrderInfo' => $this->order->info,
            'OrderNo' => $this->order->number,
            'ReceiverName' => $this->customer->name,
            'ReceiverTel' => $this->customer->tel,
            'ReceiverEmail' => $this->customer->email,

            'CIFID' => $this->customer->getCIFID(),
            'CIFPW' => $this->customer->getCIFPW(),
            'CardNo' => $this->creditCard->secret_card_number,

            'ECPlatform' => config('paynow.ec_name', 'Owlting'),

            'installment' => $times
        ];

        $this->response = $this->getSoapClient()->__soapCall('Add_CardAuthorise_ULoadUserCID_autoPay', [
            $content
        ]);

        return $this->response;
    }

    public function getCreditCard($CIF_SN, $CIF_id, $CIF_password, $credit_card_secret_code)
    {
        if (is_null($this->order))
            throw new \Exception('You need to setOrder');

//        if (is_null($this->customer->getCIFID()))
//            throw new \Exception('You need to set CIF ID');
//
//        if (is_null($this->customer->getCIFPW()))
//            throw new \Exception('You need to set CIF Password');

        $content = [
            'WebNo' => config('paynow.web_no'),
            'mem_password' => config('paynow.password'),
            'TotalPrice' => $this->order->total,
            'OrderInfo' => $this->order->info,
            'OrderNo' => $this->order->number,

            'CIFID' => $CIF_id,
            'CIFPW' => $CIF_password,

            'CardlastNo' => $credit_card_secret_code,
            'ECPlatform' => config('paynow.ec_name', 'Owlting'),
            'CIFID_SN' => $CIF_SN
        ];

        $this->response = $this->getSoapClient()->__soapCall('Add_CardAuthorise_DLoadUserCID', [
            $content
        ]);

        return $this->response;
    }
}