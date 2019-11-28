<?php

namespace Maras0830\PayNowSDK;

use SoapClient;
use Carbon\Carbon;

abstract class PayNowSOAP
{
    /** @var SoapClient */
    protected $client;

    protected $response;

    /**
     * @var Carbon
     */
    protected $time;

    /**
     * @return SoapClient
     */
    public function getSoapClient()
    {
        return $this->client;
    }

    public function getLastResponse()
    {
        return $this->response;
    }

    /**
     * @param Carbon $time
     * @return string
     */
    public function generateTimeStr(Carbon $time)
    {
        return substr($time->format('y'), -1) . $time->diffInDays($time->copy()->startOfYear()->subDay()) . $time->format('His');
    }
}
