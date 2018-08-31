<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2018/8/1
 * Time: 下午4:32
 */

namespace Maras0830\PayNowSDK\Modules;


class Store
{
    protected $mem_cid;
    protected $mem_password;

    public function __construct($mem_cid, $mem_password)
    {
        $this->mem_cid = $mem_cid;
        $this->mem_password = $mem_password;
    }

    public function getMemCid()
    {
        return $this->mem_cid;
    }

    public function getMemPassword()
    {
        return $this->mem_password;
    }
}