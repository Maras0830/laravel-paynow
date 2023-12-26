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

class ApplePayTransaction extends PayNowSOAP
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
    protected $paymentToken;
    protected $appleMerchantID;
    
    public function __construct(Carbon $time)
    {
        ini_set('default_socket_timeout', config('paynow.timeout', 180));

        if (config('paynow.debug_mode') === true) {
            $this->client = new SoapClient("https://test.paynow.com.tw/WS_CardAuthorise_JS.asmx?wsdl", array('encoding' => ' UTF-8', 'soap_version' => SOAP_1_2, 'trace' => true, "connection_timeout" => config('paynow.connection_timeout', 300)));
        } else {
            $this->client = new SoapClient("https://www.paynow.com.tw/WS_CardAuthorise_JS.asmx?wsdl", array('encoding' => ' UTF-8', 'soap_version' => SOAP_1_2, 'trace' => true, "connection_timeout" => config('paynow.connection_timeout', 300)));
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
        $this->encrypt = new PayNowEncrypt($this->time);

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
     * @param null $account
     * @param null $password
     * @return $this
     */
    public function setCustomer($id, $name, $tel, $email, $ip, $account = null, $password = null)
    {
        $this->customer = new Customer($id, $name, $tel, $email, $ip);
        $this->customer->setCIFID($account);
        $this->customer->setCIFPW($password);

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

 
    public function setPaymentToken($paymentToken)
    {
        $this->paymentToken = $paymentToken;

        return $this;
    }

    public function setAppleMerchantID($appleMerchantID)
    {
        $this->appleMerchantID = $appleMerchantID;

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

        if ($this->paymentToken === null || $this->appleMerchantID === null) {
            throw new PayNowException('You need to set paymentToken and appleMerchantID');
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
            'PassCode' => strtoupper(sha1(config('paynow.web_no') . $this->order->number . $this->order->total . config('paynow.password'))),
            'UserIp' => $this->customer->ip,
            'paymentToken' => $this->paymentToken ,
            'appleMerchantID' => $this->appleMerchantID,

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
     * 驗證交易
     * @param bool $is_3d
     * @return mixed
     * @throws PayNowException
     * @throws TransactionException
     * @throws ValidateException
     */
    public function decodeAndValidate($is_3d = false)
    {
        $decode = $this->decode();

        $pass_code = strtoupper(sha1(config('paynow.web_no') . config('paynow.password') . $decode['BuySafeNo'] . $decode['TotalPrice'] . $decode['RespCode']));

        try {
            if ($decode['PassCode'] !== $pass_code) {
                throw new ValidateException($decode['ErrorMessage'] ?? 'PassCode check fail.');
            }

            if (!empty($decode['ErrorMessage'])) {
                throw new TransactionException($decode['ErrorMessage'] ?? 'Transaction fail');
            }

            if ($is_3d) {
                return $decode;
            }

            if ($decode['RespCode'] !== '00') {
                throw new TransactionException($decode['ErrorMessage'] ?? 'Transaction fail');
            }
        } catch (PayNowException $exception) {
            throw $exception->setResponse($decode ?? []);
        }

        return $decode;
    }

    /**
     * @throws PayNowException
     * @throws DecryptException
     */
    public function decode()
    {
        try {
            $response_name = key($this->getLastResponse());

            $response = $this->getLastResponse()->$response_name;

            if ($response === '基礎連接已關閉: 接收時發生未預期的錯誤。') {
                throw new PayNowException('paynow service fail.');
            }

            $decrypted = $this->decrypt($response);
        } catch (PayNowException $exception) {
            throw $exception->setResponse($response ?? []);
        }

        return json_decode($decrypted, true);
    }
}
