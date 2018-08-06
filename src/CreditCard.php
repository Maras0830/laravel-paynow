<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2018/8/2
 * Time: 下午1:01
 */

namespace Maras0830\PayNowSDK;

use Maras0830\PayNowSDK\Exceptions\PayNowException;
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

        if (is_null(config('paynow'))) {
            throw new PayNowException("You need to publish config file command: php artisan vendor:publish --provider=Maras0830\PayNowSDK\Providers\PayNowServiceProvider");
        }

    }

    /**
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->response;
    }

    /**
     * @param $info
     * @param $number
     * @param $total
     * @return $this
     */
    public function setOrder($info, $number, $total)
    {
        $this->order = new Order($info, $number, $total);

        return $this;
    }

    /**
     * @param $name
     * @param $tel
     * @param $email
     * @param $ip
     * @return $this
     */
    public function setCustomer($name, $tel, $email, $ip)
    {
        $this->customer = new Customer($name, $tel, $email, $ip);

        return $this;
    }

    /**
     * @param $CIFID
     * @param $CIFPW
     * @return $this
     * @throws PayNowException
     */
    public function setCustomerCIF($CIFID, $CIFPW)
    {
        if (is_null($this->customer))
            throw new PayNowException('You need to setCustomer first.');

        $this->customer->setCIFID($CIFID);

        $this->customer->setCIFPW($CIFPW);

        return $this;
    }

    /**
     * @param $card_number
     * @param $valid_year
     * @param $valid_month
     * @param $safe_code
     * @return $this
     */
    public function setCreditCard($card_number, $valid_year, $valid_month, $safe_code)
    {
        $this->creditCard = new CreditCardInfo($card_number, $valid_year, $valid_month, $safe_code);

        return $this;
    }

    /**
     * @return mixed
     * @throws PayNowException
     */
    public function checkout()
    {
        if (is_null($this->order))
            throw new PayNowException('You need to setOrder');

        if (is_null($this->customer))
            throw new PayNowException('You need to setCustomer');

        if (is_null($this->creditCard))
            throw new PayNowException('You need to setCreditCard');

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

    /**
     * @param int $times
     * @return mixed
     * @throws PayNowException
     */
    public function autoPay($times = 1)
    {
        if (is_null($this->order))
            throw new PayNowException('You need to setOrder');

        if (is_null($this->customer))
            throw new PayNowException('You need to setCustomer');

        if (is_null($this->creditCard))
            throw new PayNowException('You need to setCreditCard');

        if (is_null($this->customer->getCIFID()))
            throw new PayNowException('You need to set CIF ID');

        if (is_null($this->customer->getCIFPW()))
            throw new PayNowException('You need to set CIF Password');

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

    public function installment($times)
    {

        if (is_null($this->order))
            throw new PayNowException('You need to setOrder');

        if (is_null($this->customer))
            throw new PayNowException('You need to setCustomer');

        if (is_null($this->creditCard))
            throw new PayNowException('You need to setCreditCard');

        if (is_null($this->customer->getCIFID()))
            throw new PayNowException('You need to set CIF ID');

        if (is_null($this->customer->getCIFPW()))
            throw new PayNowException('You need to set CIF Password');

        $content = [
            'WebNo' => config('paynow.web_no'),
            'mem_password' => config('paynow.password'),
            'CardNo' => $this->creditCard->secret_card_number,
            'TotalPrice' => $this->order->total,

            'OrderInfo' => $this->order->info,
            'OrderNo' => $this->order->number,

            'ReceiverID' => $this->customer->id,
            'ReceiverTel' => $this->customer->tel,
            'ReceiverName' => $this->customer->name,
            'ReceiverEmail' => $this->customer->email,

            'ECPlatform' => config('paynow.ec_name', 'Owlting'),
            'installment' => $times
        ];

        $this->response = $this->getSoapClient()->__soapCall('Add_CardAuthorise_Installment ', [
            $content
        ]);

        return $this->response;
    }

    /**
     * @param $CIF_SN
     * @param $CIF_ID
     * @param $CIF_PASSWORD
     * @param $credit_card_secret_code
     * @return mixed
     * @throws PayNowException
     */
    public function checkoutByCIFAndSecretCode($CIF_SN, $CIF_ID, $CIF_PASSWORD, $credit_card_secret_code)
    {
        if (is_null($this->order))
            throw new PayNowException('You need to setOrder');

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