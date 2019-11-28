<?php

namespace Maras0830\PayNowSDK\Traits;

use Maras0830\PayNowSDK\Exceptions\DecryptException;
use Maras0830\PayNowSDK\Exceptions\EncryptException;

trait Encrypt
{
    protected $encrypt_key;

    protected $encrypt_iv;

    /**
     * @param $data
     * @param int $blocksize
     * @param string $item
     * @return string
     */
    protected static function pad($data, $blocksize = 32, $item = "\x00")
    {
        $pad = $blocksize - (strlen($data) % $blocksize);

        return $data . str_repeat($item, $pad);
    }

    /**
     * @param $text
     * @return mixed
     */
    protected static function unpad($text, $item = "\x00")
    {
        return str_replace($item, '', $text);
    }

    /**
     * @param $data
     * @return string
     * @throws EncryptException
     */
    protected function encrypt($data)
    {
        $data = self::pad($data);

        $iv = $this->encrypt_iv;

        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->encrypt_key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        if ($encrypted === false) {
            throw new EncryptException();
        }

        return base64_encode($encrypted);
    }

    /**
     * @param $hash
     * @return mixed|string
     * @throws DecryptException
     */
    protected function decrypt($hash)
    {
        $hash = urldecode($hash);

        $hash = base64_decode($hash);

        $decrypted = openssl_decrypt($hash, 'AES-256-CBC', $this->encrypt_key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->encrypt_iv);

        if ($decrypted === false) {
            throw new DecryptException();
        }

        $decrypted = self::unpad($decrypted);

        return $decrypted;
    }
}
