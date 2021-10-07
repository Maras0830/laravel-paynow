<?php
namespace Maras0830\PayNowSDK\Exceptions;

use Exception;

class PayNowException extends Exception
{
    private $response;

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }
}
