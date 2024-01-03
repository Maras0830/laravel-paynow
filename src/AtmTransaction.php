<?php

namespace Maras0830\PayNowSDK;

use GuzzleHttp\Client;
use Maras0830\PayNowSDK\Exceptions\CheckoutException;
use Maras0830\PayNowSDK\Exceptions\PayNowException;
use Maras0830\PayNowSDK\Exceptions\ValidateException;
use Maras0830\PayNowSDK\Modules\Customer;
use Maras0830\PayNowSDK\Modules\Order;
use DOMDocument;
use DOMXpath;
use Carbon\Carbon;

class AtmTransaction
{
    protected $url;
    protected $order;
    protected $customer;
    protected $deadLine;
    protected $response;


    public function __construct()
    {
        if (config('paynow.debug_mode') === true) {
            $this->url = 'https://test.paynow.com.tw/service/etopm.aspx';
        } else {
            $this->url = 'https://www.paynow.com.tw/service/etopm.aspx';
        }
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

    public function setDeadLine(int $deadLine)
    {
        $this->deadLine = $deadLine;

        return $this;
    }


    /**
     *
     * @throws PayNowException
     */
    public function checkout()
    {
        if ($this->order === null) {
            throw new PayNowException('You need to setOrder');
        }

        if ($this->customer === null) {
            throw new PayNowException('You need to setCustomer');
        }

        $data = [
            'WebNo' => config('paynow.web_no'),
            'PassCode' => strtoupper(sha1(config('paynow.web_no') . $this->order->number . $this->order->total . config('paynow.password'))),
            'ReceiverName' => $this->customer->name,
            'ReceiverID' => $this->customer->email,  // 不知道爲什麼 user id 不會過
            'ReceiverTel' => $this->customer->tel,
            'ReceiverEmail' => $this->customer->email,
            'OrderNo' => $this->order->number,
            'ECPlatform' => config('paynow.ec_name', 'Eric'),
            'TotalPrice' => $this->order->total,
            'OrderInfo' => $this->order->info,
            'PayType' => '03', //虛擬帳號
            'AtmRespost' => 1,
            'DeadLine' => (int) $this->deadLine ?? 1,
            'PayEN' => 0,
            'EPT' => 1
        ];

        $client = new Client();

        try {
            $this->response = $client->post($this->url, ['timeout' => 30, 'form_params' => $data]);
        } catch (\Exception $e) {
            throw new CheckoutException($e->getMessage());
        }

        if(str_contains((string)$this->response->getBody(), '該商家交易訂單已存在，請與商家聯絡並重新產生交易')) {
            throw new CheckoutException('該商家交易訂單已存在，請與商家聯絡並重新產生交易');
        }

        return $this;
    }

    public function decode()
    {
        $dom = new DOMDocument();
        $dom->loadHTML((string)$this->response->getBody());
        $xp = new DOMXpath($dom);
        $nodes = $xp->query('//input');

        $result = [];
        foreach ($nodes as $el) {
            $result += [$el->getAttribute('name') => $el->getAttribute('value')];
        }

        $result['NewDate'] = Carbon::parse(str_replace('%2f', '/', $result['NewDate']))->toDateTimeString();
        $result['DueDate'] = Carbon::parse(str_replace(['%2f', '%3a', '+'], ['/', ':', ' '], $result['DueDate']))->toDateTimeString();
        return $result;
    }

    /**
     * 驗證交易
     * @return mixed
     * @throws PayNowException
     * @throws ValidateException
     */
    public function decodeAndValidate()
    {
        $decode = $this->decode();

        $pass_code = strtoupper(sha1(config('paynow.web_no') . $this->order->number . $this->order->total . config('paynow.password')));

        try {
            if ($decode['PassCode'] !== $pass_code) {
                throw new ValidateException($decode['ErrorMessage'] ?? 'PassCode check fail.');
            }
        } catch (PayNowException $exception) {
            throw $exception->setResponse($decode ?? []);
        }

        return $decode;
    }
}
