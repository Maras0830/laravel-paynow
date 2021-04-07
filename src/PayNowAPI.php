<?php


namespace Maras0830\PayNowSDK;


use Maras0830\PayNowSDK\Exceptions\CallbackCheckException;
use Maras0830\PayNowSDK\Exceptions\OrderIsCancelException;
use Maras0830\PayNowSDK\Exceptions\OrderNotFoundException;
use Maras0830\PayNowSDK\Exceptions\TransactionException;
use Maras0830\PayNowSDK\Exceptions\UnKnownException;
use SoapClient;

class PayNowAPI extends PayNowSOAP
{
    const SUCCESS = '1';
    const ERROR = '2';
    const CANCEL = '3';
    const NOTFOUND = '4';

    /**
     * PayNowAPI constructor.
     * @throws \SoapFault
     */
    public function __construct()
    {
        ini_set('default_socket_timeout', config('paynow.timeout', 180));

        if (config('paynow.debug_mode') === true) {
            $this->client = new SoapClient('https://test.paynow.com.tw/PayNowAPI.asmx?wsdl', array('soap_version' => SOAP_1_2, 'trace' => true, "connection_timeout" => config('paynow.connection_timeout', 300)));
        }
        else {
            $this->client = new SoapClient('https://www.paynow.com.tw/PayNowAPI.asmx?wsdl', array('soap_version' => SOAP_1_2, 'trace' => true, "connection_timeout" => config('paynow.connection_timeout', 300)));
        }

        $this->hash = config('paynow.web_no');
    }

    /**
     * @param $order_number
     * @return array
     * @throws OrderNotFoundException
     * @throws TransactionException
     * @throws OrderIsCancelException
     * @throws UnKnownException
     */
    public function transactionCheck($order_number)
    {
        $this->response = $this->getSoapClient()->__soapCall('Sel_PaymentRespCode', [
            [
                'OrderNo' => $order_number,
                'WebNo' => $this->hash,
            ]
        ]);

        $response = $this->getLastResponse()->Sel_PaymentRespCodeResult;

        if ($response === self::NOTFOUND) {
            throw new OrderNotFoundException("order not found");
        }

        $response_arr = explode(',', $response);

        switch ($response_arr[0]) {
            case self::SUCCESS:
                $detail = explode('_', $response_arr[1]);
                return [
                    'order_number' => $detail[0] ?? '',
                    'BuySafeNo' => $detail[0] ?? '',

                    'last4' => $detail[1] ?? '',
                    'last4CardNo' => $detail[1] ?? '',
                ];
                break;
            case self::ERROR:
                $detail = explode('_', $response_arr[1]);
                $code = $detail[2] ?? '0';
                $msg = self::translateErrorMessage($code);
                throw new TransactionException($msg, self::translateCode($code));
                break;
            case self::CANCEL:
                $msg = self::translateCancelMessage($response_arr[1] ?? '');
                throw new OrderIsCancelException($msg);
                break;
            case self::NOTFOUND:
                throw new OrderNotFoundException("order not found");
                break;
            default:
                throw new UnKnownException("unknown exception");
                break;
        }
    }


    public static function translateCode($code)
    {
        $list = [
            '0' => '0',
            '01' => '01',
            '02' => '02',
            '04' => '04',
            '05' => '05',
            '06' => '06',
            '07' => '07',
            '12' => '12',
            '14' => '14',
            '15' => '15',
            '33' => '33',
            '41' => '41',
            '43' => '43',
            '51' => '51',
            '54' => '54',
            '55' => '55',
            '56' => '56',
            '57' => '57',
            '58' => '58',
            '62' => '62',
            '87' => '87',
            '90' => '90',
            '96' => '96',
            '916' => '916',
            '920' => '920',
            '922' => '922',
            '933' => '933',
            '934' => '934',
            '935' => '935',
            '939' => '939',
            'N7' => '1007',
            'P1' => '1011',
            'Q1' => '1021',
        ];

        return $list[$code] ?? 0;
    }

    /**
     * @param $code
     * @return string
     */
    public static function translateErrorMessage($code)
    {
        $list = [
            '0' => 'unknown error',
            '01' => '發卡銀行要求回覆(call bank)',
            '02' => '發卡銀行要求回覆(call bank)',
            '04' => '拒絕交易,請聯絡發卡銀行',
            '05' => '發卡銀行拒絕交易',
            '06' => '發卡銀行拒絕交易',
            '07' => '發卡銀行拒絕交易',
            '12' => '拒絕交易,卡號錯誤',
            '14' => '拒絕交易,卡號錯誤',
            '15' => '無此發卡銀行',
            '33' => '拒絕交易,過期卡',
            '41' => '拒絕交易,請聯絡發卡銀行',
            '43' => '拒絕交易,請聯絡發卡銀行',
            '51' => '信用卡額度不足',
            '54' => '拒絕交易，過期卡',
            '55' => '拒絕交易，密碼錯誤',
            '56' => '發卡銀行拒絕交易',
            '57' => '發卡銀行拒絕交易',
            '58' => '發卡銀行拒絕交易',
            '62' => '發卡銀行拒絕交易',
            '87' => '拒絕交易,請聯絡發卡銀行',
            '90' => '銀行系統結算中',
            '96' => '收單行系統功能異常',
            '916' => '主機斷線',
            '920' => '拒絕交易,卡號錯誤',
            '922' => '密碼錯誤',
            '933' => '訂單編號不存在',
            '934' => '發卡行3D頁面驗證錯誤',
            '935' => '發卡行3D頁面驗證錯誤',
            '939' => 'MID 停用',
            'N7' => '拒絕交易,請聯絡發卡銀行',
            'P1' => '拒絕交易 超過日限額度',
            'Q1' => '發卡銀行拒絕交易',
        ];

        return $list[$code] ?? 'unknown error';
    }

    /**
     * @param $code
     * @return string
     */
    public static function translateCancelMessage($code)
    {
        $list = [
            '0' => '買家申請退貨',
            '1' => '買賣家確認',
            '2' => '銀行退款',
            '3' => '賣家申請',
        ];

        return $list[$code] ?? 'unknown msg';
    }


    /**
     * @param $input
     * @return mixed
     * @throws CallbackCheckException
     */
    public function callbackCheck($input)
    {
        if (empty($input['OrderNo'])) {
            throw new CallbackCheckException('Miss OrderNo.');
        }

        if (empty($input['TotalPrice'])) {
            throw new CallbackCheckException('Miss TotalPrice.');
        }

        if (empty($input['PassCode'])) {
            throw new CallbackCheckException('Miss PassCode.');
        }

        $my_passcode = strtoupper(sha1(config('paynow.web_no') .  $input['OrderNo'] . $input['TotalPrice'] . config('paynow.password')));

        if ($my_passcode !== $input['PassCode']) {
            throw new CallbackCheckException('PassCode check fail.');
        }

        return $input;
    }
}
