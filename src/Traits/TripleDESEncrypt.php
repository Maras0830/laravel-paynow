<?php

namespace Maras0830\PayNowSDK\Traits;

use Illuminate\Support\Facades\Hash;
use Maras0830\PayNowSDK\Exceptions\DecryptException;
use Maras0830\PayNowSDK\Exceptions\EncryptException;

const BLOCK_SIZE_TRIPLE_DES = 8;

trait TripleDESEncrypt
{
    protected $encrypt_key;

    protected $encrypt_iv;

    /**
     * @param $data
     * @return string
     * @throws EncryptException
     */
    protected function encrypt($data)
    {
        $value = self::pad($data);

        $result = openssl_encrypt(
            $value,
            'DES-EDE3',
            $this->encrypt_key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        );

        if ($result === false) {
            throw new EncryptException('data encrypt error.');
        }

        return base64_encode($result);
    }

    /**
     * @param $hash
     * @return mixed|string
     * @throws DecryptException
     */
    protected function decrypt($hash)
    {
        $hash = base64_decode($hash);
        $decrypted = openssl_decrypt(
            $hash,
            'DES-EDE3',
            $this->encrypt_key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        );

        if ($decrypted === false) {
            throw new DecryptException();
        }

        return $decrypted;
    }

    /**
     * At least as early as Aug 2016, Openssl declared the following weak: RC2, RC4, DES, 3DES, MD5 based
     * https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
     * @param $data
     * @param int $blocksize
     * @param string $item
     * @return string
     */
    protected static function pad($data, $blocksize = BLOCK_SIZE_TRIPLE_DES, $item = '0')
    {
        $pad = $blocksize - (strlen($data) % $blocksize);

        return $data . str_repeat(chr($item), $pad);
    }
}
