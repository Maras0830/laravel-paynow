<?php

namespace Maras0830\PayNowSDK;

use Carbon\Carbon;
use Maras0830\PayNowSDK\Exceptions\CheckoutException;
use Maras0830\PayNowSDK\Exceptions\DecryptException;
use Maras0830\PayNowSDK\Exceptions\PayNowException;
use Maras0830\PayNowSDK\Exceptions\TransactionException;
use Maras0830\PayNowSDK\Exceptions\ValidateException;
use Maras0830\PayNowSDK\Modules\CreditCardInfo;
use Maras0830\PayNowSDK\Modules\Customer;
use Maras0830\PayNowSDK\Modules\Order;
use Maras0830\PayNowSDK\Traits\Encrypt;
use SoapClient;

class CreditCardTransaction extends PayNowSOAP
{
    use Encrypt;

    /** @var Encrypt $encrypt */
    protected $encrypt;
    /** @var Order $order */
    protected $order;
    /** @var CreditCardInfo $creditCard */
    protected $creditCard;
    /** @var Customer $customer */
    protected $customer;

    public function __construct(Carbon $time)
    {
        if (config('paynow.debug_mode') === true) {
            $this->client = new SoapClient("https://test.paynow.com.tw/WS_CardAuthorise_JS.asmx?wsdl", array('encoding' => ' UTF-8', 'soap_version' => SOAP_1_2, 'trace' => true));
        } else {
            $this->client = new SoapClient("https://www.paynow.com.tw/WS_CardAuthorise_JS.asmx?wsdl", array('encoding' => ' UTF-8', 'soap_version' => SOAP_1_2, 'trace' => true));
        }

        $this->time = $time;
    }

    /**
     * @param Carbon $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return $this
     * @throws Exceptions\DecryptException
     * @throws Exceptions\ValidateException
     * @throws PayNowException
     */
    public function setEncrypt()
    {
        $this->encrypt = new PaynowEncrypt($this->time);

        $check_num_res = $this->encrypt
            ->getTransactionCheckNum()
            ->decodeAndValidate();

        $res = $this->encrypt
            ->getTransactionKey($check_num_res)
            ->decodeAndValidate(true);

        $this->encrypt_key = $res['EncryptionKey'];
        $this->encrypt_iv = $res['EncryptionIV'];

        return $this;
    }

    /**
     * @param $id
     * @param $name
     * @param $tel
     * @param $email
     * @param $ip
     * @return $this
     */
    public function setCustomer($id, $name, $tel, $email, $ip)
    {
        $this->customer = new Customer($id, $name, $tel, $email, $ip);

        return $this;
    }


    /**
     * @param $card_number
     * @param $valid_year
     * @param $valid_month
     * @param $safe_code
     * @return $this
     * @throws PayNowException
     */
    public function setCreditCard($card_number, $valid_year, $valid_month, $safe_code)
    {
        if ($this->encrypt_key === null || $this->encrypt_iv === null || $this->encrypt === null) {
            throw new PayNowException('You need to setEncrypt');
        }

        $this->creditCard = new CreditCardInfo($this->encrypt_key, $this->encrypt_iv, $card_number, $valid_year, $valid_month, $safe_code);

        return $this;
    }


    /**
     * @param $info
     * @param $number
     * @param $total
     * @return $this
     * @throws Exceptions\OrderException
     */
    public function setOrder($info, $number, $total)
    {
        $this->order = new Order($info, $number, $total);

        return $this;
    }

    /**
     *
     * @throws PayNowException
     */
    public function checkout()
    {
        if ($this->encrypt === null) {
            throw new PayNowException('You need to setEncrypt');
        }

        if ($this->order === null) {
            throw new PayNowException('You need to setOrder');
        }

        if ($this->customer === null) {
            throw new PayNowException('You need to setCustomer');
        }

        if ($this->creditCard === null) {
            throw new PayNowException('You need to setCreditCard');
        }

        $data = [
            'mem_cid' => config('paynow.web_no'),
            'mem_checkpw' => config('paynow.password'),
            'OrderNo' => $this->order->number,
            'OrderInfo' => $this->order->info,
            'ECPlatform' => config('paynow.ec_name', 'Eric'),
            'ReceiverID' => $this->customer->id,
            'ReceiverEmail' => $this->customer->email,
            'ReceiverName' => $this->customer->name,
            'ReceiverTel' => $this->customer->tel,
            'TotalPrice' => $this->order->total,
            'CardNo' => $this->creditCard->secret_card_number,
            'PassCode' => strtoupper(sha1(config('paynow.web_no') . $this->order->number . $this->order->total . config('paynow.password'))),
            'UserIp' => $this->customer->ip,
        ];

        $json_str = $this->encrypt(json_encode($data));

        $content = [
            'JStr' => urlencode(substr($json_str, 0, strlen($json_str) / 2)),
            'JStr2' => urlencode(substr($json_str, -1 * strlen($json_str) / 2)),
            'mem_cid' => config('paynow.web_no'),
            'TimeStr' => $this->generateTimeStr($this->time),
            'CheckNum' => $this->encrypt->getCheckNum(),
        ];

        try {
            $this->response = $this->getSoapClient()->__soapCall('CardAuthorise_P', [
                $content
            ]);
        } catch (\SoapFault $e) {
            throw new CheckoutException($e->getMessage());
        }


        return $this;
    }

    /**
     * @return mixed
     * @throws DecryptException
     * @throws PayNowException
     * @throws ValidateException
     */
    public function decodeAndValidate()
    {
        $response = $this->getLastResponse()->CardAuthorise_PResult;

        if ($response === '基礎連接已關閉: 接收時發生未預期的錯誤。') {
            throw new PayNowException('paynow service fail.');
        }

        $decrypted = $this->decrypt($response);

        $decode = json_decode($decrypted, true);

        $pass_code = strtoupper(sha1(config('paynow.web_no') . config('paynow.password') . $decode['BuySafeNo'] . $decode['TotalPrice'] . $decode['RespCode'] ));

        if ($decode['PassCode'] !== $pass_code) {
            throw new ValidateException('PassCode check fail.');
        }

        if ($decode['RespCode'] !== '00') {
            throw (new TransactionException('Transaction fail'))->setResponse($decode);
        }

        return $decode;
    }
}
