<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2018/8/1
 * Time: 下午4:29
 */

namespace Maras0830\PayNowSDK\Modules;

class Order
{
    public $info;

    public $number;

    public $total;

    public $deduct_count  = 1;

    /**
     * PayNowOrder constructor.
     * @param $info "transaction content"
     * @param $number "order number"
     * @param $total "order total"
     */
    public function __construct($info, $number, $total)
    {
        $this->info = $info;
        $this->number = $number;
        $this->total = $total;
    }

    /**
     * @param int $deduct_count
     * @return $this
     */
    public function setDeductCount(int $deduct_count)
    {
        $this->deduct_count = $deduct_count;

        return $this;
    }
}