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
        $this->encrypt_key = '123456789028229955123456';
        $this->encrypt_iv = null;
        $hash = '7zd+oMo5OU+q463pQv4CfuCBf642FxxxlvyfflJwb9PI3pdVzV3nm6zdzrI+FigbhxKNDh0fAWrsVXrfK0+VYrD1yOjDwgn5r+gp4nezlI/dwHBH4BbRj5yZU3GhMnnWyRv6WOWwzlEAO916A/t1ucLuSsLQeGrr0R+o0HNfFQucTP1Reuy5M+rTTd6pMVRgJ2XK/8sLR4BS+YkgQLb7egy5TrwjJSR9Iv31zvPz81YBOSwc28n7k+C661COZzGKULdnugZwVgNZx0rQv+6RRKAcrT0isspnFBHlT0IJccH+XnaqkdE93JTYT4t2XKvYL1fjM5xffXsgXuumu31ytq9SQuMeGIHVMwMFukI3bjwGE7GX0+EAXe0HEl4QF/mjrpQDgl7n41ElUEznbQmi47AbL5jfqe0H/rXmfE0sek3erUtgruwdW2HLfQB5/3RvBPmVbWK1l3Y=';
        $hash = base64_decode($hash);


        $decrypted = openssl_decrypt(
            $hash,
            'DES-EDE3',
            $this->encrypt_key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        );

        dd($decrypted);
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
