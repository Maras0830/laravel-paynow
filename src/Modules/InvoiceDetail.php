<?php
namespace Maras0830\PayNowSDK\Modules;


class InvoiceDetail
{
    public $order_no;
    public $buyer_id;
    public $buyer_name;
    public $buyer_add;
    public $buyer_phone;
    public $buyer_email;
    public $currier_type;
    public $carrier_id_1;
    public $carrier_id_2;
    public $love_code;
    public $description;
    public $quantity;
    public $unit_price;
    public $amount;
    public $remark;
    public $item_tax_type;
    public $is_pass_customs;

    /**
     * InvoiceDetail constructor.
     */
    public function __construct(
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
        $is_pass_customs)
    {
        $this->order_no = $order_no;
        $this->buyer_id = $buyer_id;
        $this->buyer_name = $buyer_name;
        $this->buyer_add = $buyer_add;
        $this->buyer_phone = $buyer_phone;
        $this->buyer_email = $buyer_email;
        $this->currier_type = $currier_type;
        $this->carrier_id_1 = $carrier_id_1;
        $this->carrier_id_2 = $carrier_id_2;
        $this->love_code = $love_code;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unit_price = $unit_price;
        $this->amount = $amount;
        $this->remark = $remark;
        $this->item_tax_type = $item_tax_type;
        $this->is_pass_customs = $is_pass_customs;
    }

    public function toString()
    {
        $str = 
            "'$this->order_no,'$this->buyer_id,'$this->buyer_name,'$this->buyer_add,'$this->buyer_phone,'$this->buyer_email,'$this->currier_type,'$this->carrier_id_1,'$this->carrier_id_2,'$this->love_code,'$this->description,'$this->quantity,'$this->unit_price,'$this->amount,'$this->remark,'$this->item_tax_type,'$this->is_pass_customs";

        return $str;
    }
}