<?php


namespace Maras0830\PayNowSDK\Exceptions;

class TransactionException extends PayNowException
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
