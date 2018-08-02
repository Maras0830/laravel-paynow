<?php
/**
 * Created by PhpStorm.
 * User: Maras
 * Date: 2018/8/1
 * Time: 下午4:32
 */

namespace Maras0830\PayNowSDK\Modules;


class Customer
{
    public $name;

    public $tel;

    public $email;

    public $ip;

    protected $CIFID;

    protected $CIFPW;

    /**
     * PayNowCustomer constructor.
     * @param $name
     * @param $tel
     * @param $email
     * @param $ip
     * @param array $CIF
     */
    public function __construct($name, $tel, $email, $ip)
    {
        $this->name = $name;
        $this->email = $email;
        $this->tel = $tel;
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getCIFID()
    {
        return $this->CIFID;
    }

    /**
     * @param mixed $CIFID
     */
    public function setCIFID($CIFID)
    {
        $this->CIFID = $CIFID;
    }

    /**
     * @return mixed
     */
    public function getCIFPW()
    {
        return $this->CIFPW;
    }

    /**
     * @param mixed $CIFPW
     */
    public function setCIFPW($CIFPW)
    {
        $this->CIFPW = $CIFPW;
    }
}