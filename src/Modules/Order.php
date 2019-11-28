<?php

namespace Maras0830\PayNowSDK\Modules;

use Maras0830\PayNowSDK\Exceptions\OrderException;

class Order
{
    public $info;

    public $number;

    public $total;

    /**
     * Order constructor.
     * @param $info
     * @param $number
     * @param $total
     * @throws OrderException
     */
    public function __construct($info, $number, $total)
    {
        if (mb_strlen($info) < 3) {
            throw new OrderException("OrderInfo len must over 3");
        }

        $this->info = $info;
        $this->number = $number;
        $this->total = $total;
    }
}
