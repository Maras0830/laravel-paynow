<?php
namespace Maras0830\PayNowSDK;

use Maras0830\PayNowSDK\Modules\Store;
use Maras0830\PayNowSDK\Modules\InvoiceDetail;

class Invoice extends PayNowInvoiceSoap
{
    protected $order;
    protected $store;
    protected $invoice;
    protected $invoices;

    /**
     * CreditCard constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (is_null(config('paynow'))) {
            throw new PayNowException("You need to publish config file command: php artisan vendor:publish --provider=Maras0830\PayNowSDK\Providers\PayNowServiceProvider");
        }

        $this->store = new Store(config('paynow.web_no'), config('paynow.password'));
    }

    /**
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->response;
    }

    public function parseResponse()
    {
        $result = [];

        if ($this->response != null) {

            foreach ($this->response as $response)
            {
                $response_ary = explode(',', $response);

                if (count($response_ary) > 1) {

                    for ($i = 1; $i < count($response_ary); $i++) {
                        $parse = explode('_', $response_ary[$i]);

                        $result[] = [
                            'order_id' => $parse[0],
                            'invoice_number' => $parse[1],
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $order_no 依據訂單編號確認是否為同一張發票
     * @param $buyer_id 買方統編(可為空)
     * @param $buyer_name 購買人姓名
     * @param $buyer_add 地址(可為空) 若輸入則代表要寄給使用者
     * @param $buyer_phone 手機號碼(可為空) 若輸入則代表要傳使用者
     * @param $buyer_email 電子郵件(可為空) 若輸入則代表要傳使用者
     * @param $currier_type 載具類型(可為空) (悠遊卡:1K0001 通用載具:3J0002 自然人憑證:CQ0001 若為統編發票僅能使用通用載具)
     * @param $carrier_id_1 載具明碼(可為空) (悠遊卡:免填 通用載具:通用載具號碼(手機條碼) 自然人憑證:憑證號碼)
     * @param $carrier_id_2 載具隱碼(可為空) (悠遊卡:悠遊卡隱碼 通用載具:通用載具號碼(手機條碼) 自然人憑證:憑證號碼)
     * @param $love_code 愛心碼(可為空)
     * @param $description 明細敘述
     * @param $quantity 數量
     * @param $unit_price 單價
     * @param $amount 小計
     * @param $remark 備註(可為空)
     * @param $item_tax_type 1: 應稅, 2: 零稅率, 3: 免稅
     * @param $is_pass_customs 是否經海關
     * @return $this
     */
    public function addInvoice(
        $order_no,
        $buyer_id,
        $buyer_name,
        $buyer_add,
        $buyer_phone,
        $buyer_email,
        $currier_type,
        $carrier_id_1,
        $carrier_id_2,
        $love_code,
        $description,
        $quantity,
        $unit_price,
        $amount,
        $remark,
        int $item_tax_type,
        bool $is_pass_customs)
    {
        $this->invoices[] = new InvoiceDetail(
            $order_no,
            $buyer_id,
            $buyer_name,
            $buyer_add,
            $buyer_phone,
            $buyer_email,
            $currier_type,
            $carrier_id_1,
            $carrier_id_2,
            $love_code,
            $description,
            $quantity,
            $unit_price,
            $amount,
            $remark,
            $item_tax_type,
            $is_pass_customs ? 2 : 1
        );

        return $this;
    }

    public function submit()
    {
        $invoice_string = '';

        foreach ($this->invoices as $invoice) {
            if (strlen($invoice_string) != 0)
                $invoice_string .= "\n";

            $invoice_string .= $invoice->toString();
        }

        if (strlen($invoice_string) != 0) {

            $content = [
                'mem_cid' => $this->store->getMemCid(),
                'mem_password' => $this->store->getMemPassword(),
                'csvStr' => urlencode(base64_encode($invoice_string))
            ];

            $this->response = $this->getSoapClient()->__soapCall('UploadInvoice_Patch', [
                $content
            ]);
        }
    }

    public function getInvoices()
    {
        return $this->invoices;
    }

}