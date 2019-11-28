<?php

namespace Maras0830\PayNowSDK;

use Maras0830\PayNowSDK\Traits\Encrypt;
use SoapClient;
use Carbon\Carbon;
use Maras0830\PayNowSDK\Exceptions\DecryptException;
use Maras0830\PayNowSDK\Exceptions\PaynowException;
use Maras0830\PayNowSDK\Exceptions\ValidateException;

class PayNowEncrypt extends PayNowSOAP
{
    use Encrypt;

    /**
     * @var string
     */
    private $hash;
    /**
     * @var string
     */
    private $cardinal;

    private $check_num;

    public function __construct(Carbon $time)
    {
        if (config('paynow.debug_mode') === true) {
            $this->client = new SoapClient("https://test.paynow.com.tw/WS_PayNowEncrytp.asmx?wsdl", array('encoding' => ' UTF-8', 'soap_version' => SOAP_1_2, 'trace' => true));
        } else {
            $this->client = new SoapClient("https://www.paynow.com.tw/WS_PayNowEncrytp.asmx?wsdl", array('encoding' => ' UTF-8', 'soap_version' => SOAP_1_2, 'trace' => true));
        }

        $this->encrypt_key = 'paynowencryptpaynowcomtw28229955';

        $this->encrypt_iv = 'encrypt282299550';

        $this->cardinal = '93193193193193193193193';

        $this->hash = config('paynow.web_no');

        $this->time = $time;
    }

    /**
     * @return $this
     * @throws Exceptions\EncryptException
     * @throws PaynowException
     */
    public function getTransactionCheckNum()
    {
        $time_str = $this->generateTimeStr($this->time);

        $check_code = $this->generateCheckCode($time_str, 'GP');

        $pass_code = $this->generatePassCode($this->hash, $check_code);

        $j_str = [
            'mem_cid' => $this->hash,
            'PassCode' => $pass_code,
            'TimeStr' => $time_str,
        ];

        $content = [
            'OP' => 'GP',
            'JStr' => $this->encrypt(json_encode($j_str))
        ];

        try {
            $this->response = $this->getSoapClient()->__soapCall('GetEncryptionStr', [
                $content
            ]);
        } catch (\Exception $e) {
            throw new PaynowException($this->getSoapClient()->__getLastResponse());
        }

        return $this;
    }

    /**
     * @param bool $is_get_key
     * @return mixed
     * @throws DecryptException
     * @throws PaynowException
     * @throws ValidateException
     */
    public function decodeAndValidate($is_get_key = false)
    {
        $response = $this->getLastResponse()->GetEncryptionStrResult;

        if ($response === '基礎連接已關閉: 接收時發生未預期的錯誤。') {
            throw new PaynowException('paynow service fail.');
        }

        $time_str = $this->generateTimeStr($this->time);

        $decrypted = $this->decrypt($response);

        $decode = json_decode($decrypted, true);

        $check_code = $this->generateCheckCode($time_str, $is_get_key ? 'GP' : 'GK');

        $pass_code = $this->generatePassCode($this->hash, $check_code, $decode['CheckNum'] ?? null);

        if ($decode['PassCode'] !== $pass_code) {
            throw new ValidateException();
        }

        if (!empty($decode['CheckNum'])) {
            $this->check_num = $decode['CheckNum'];
        }

        return $decode;
    }

    /**
     * @param $res
     * @return $this
     * @throws Exceptions\EncryptException
     * @throws PaynowException
     */
    public function getTransactionKey($res)
    {
        $j_str = $res;

        $content = [
            'OP' => 'GK',
            'JStr' => $this->encrypt(json_encode($j_str))
        ];

        try {
            $this->response = $this->getSoapClient()->__soapCall('GetEncryptionStr', [
                $content
            ]);
        } catch (\Exception $e) {
            throw new PaynowException($this->getSoapClient()->__getLastResponse());
        }

        return $this;
    }

    /**
     * @param $time_str
     * @param $type
     * @return string
     * @throws PaynowException
     */
    private function generateCheckCode($time_str, $type)
    {
        $hash = str_pad(config('paynow.web_no'), 9, "0", STR_PAD_LEFT);

        switch ($type) {
            case 'GP':
                $no = substr($hash, 0, 5);
                $key = $no . $time_str . substr($time_str, 0, 4) . substr($hash, -4);
                break;
            case 'GK':
                $no = substr($hash, -5);
                $key = $no . $time_str . substr($time_str, 0, 4) . substr($hash, 0, 4);
                break;
            default:
                throw new PaynowException('generate CheckCode fail, type is null');
        }

        $op = 0;

        for ($i = 0; $i < 23; $i++) {
            $op += ($key[$i] * $this->cardinal[$i]) % 10;
        }

        $op = 10 - ($op % 10);

        return $no . $time_str . $op;
    }

    private function generatePassCode($mem_cid, $check_code, $key = null)
    {
        if (empty($key)) {
            $hash = hash('sha256', $mem_cid . $check_code);
        } else {
            $hash = hash_hmac('sha256', $mem_cid . $check_code, $key);
        }

        return strtoupper($hash);
    }

    /**
     * @return mixed
     */
    public function getCheckNum()
    {
        return $this->check_num;
    }
}
