<?php
namespace Maras0830\PayNowSDK;

use GuzzleHttp\Client;
use Maras0830\PayNowSDK\Traits\TripleDESEncrypt;

class PaymentRefund
{
    use TripleDESEncrypt;

    /**
     * @var Client;
     */
    private $client;
    /**
     * @var string
     */
    private $url;

    private $response;

    /**
     * CreditCard constructor.
     */
    public function __construct()
    {
        ini_set('default_socket_timeout', config('paynow.timeout', 180));

        $this->client = app(Client::class);

        if (config('paynow.debug_mode') == true) {
            $this->url = 'https://test.paynow.com.tw/service/PayNowAPI_JS.aspx';
        }
        else {
            $this->url = 'https://www.paynow.com.tw/service/PayNowAPI_JS.aspx';
        }

        $this->encrypt_key = config('paynow.refund_encrypt_key');
    }

    public function refund(
        $order_no,
        $buy_safe_no,
        $refund_price,
        $refundvalue,
        $refundmode = 1,
        $mem_bankaccno = '',
        $accountbankno = '',
        $mem_bankaccount = '',
        $buyerid = '',
        $buyername = '',
        $buyeremail = ''
    )
    {
        $passcode = $this->generatePassCode(
            config('paynow.web_no'),
            $order_no,
            $refund_price,
            config('paynow.password')
        );

        $data = [
            "mem_type" => 2,
            "buysafeno" => $buy_safe_no,
            "mem_cid" => config('paynow.web_no'),
            "passcode" => $passcode,
            "mem_bankaccno" => $mem_bankaccno,
            "accountbankno" => $accountbankno,
            "mem_bankaccount" => $mem_bankaccount,
            "refundvalue" => $refundvalue,
            "refundmode" => $refundmode,
            "buyerid" => $buyerid,
            "buyername" => $buyername,
            "buyeremail" => $buyeremail,
            "refundprice" => $refund_price
        ];

        $encrypt = $this->encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));

        try {
            $result = $this->client->post($this->url, [
                'form_params' => [
                    'OP' => 'R',
                    'JStr' => $encrypt
                ]
            ]);

            $this->response = $this->responseDecode($result->getBody()->getContents());
        } catch (\Exception $e) {
            $this->response = [
                'status' => 'F',
                'message' => $e->getMessage(),
                'passcode' => ''
            ];
        }

        return $this;
    }


    private function generatePassCode($mem_cid, $order_no, $refund_price, $password)
    {
        $hash = hash('sha1', $mem_cid . $order_no . $refund_price . $password);

        return strtoupper($hash);
    }

    /**
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->response;
    }

    public function responseDecode(string $response)
    {
        $response_exploded = explode('_', $response);

        $status = $response_exploded[0] ?? 'F';

        if (!in_array($status, ['S', 'F'])) {
            $status = 'F';
        }

        $result_arr = explode(',', $response_exploded[1] ?? '');

        $message = $result_arr[0] ?? '';

        $passcode = $result_arr[1] ?? '';

        return compact('status', 'message', 'passcode');
    }
}
