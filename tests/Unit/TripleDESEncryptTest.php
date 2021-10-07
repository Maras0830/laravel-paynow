<?php

namespace Tests\Unit;

use Maras0830\PayNowSDK\Traits\TripleDESEncrypt;
use Orchestra\Testbench\TestCase;

class TripleDESEncryptTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('paynow.debug_mode', true);
        $app['config']->set('paynow.web_no', '13099407');
        $app['config']->set('paynow.password', '12345');
        $app['config']->set('paynow.ec_name', 'ECName');
        $app['config']->set('paynow.refund_encrypt_key', '123456789028229955123456');
    }

    /**
     * @group unit.trait
     */
    public function testEncrypt()
    {
        //Arrange
        $data = [
            "mem_type" => 2,
            "buysafeno" => "8000001910145799460",
            "mem_cid" => "13099407",
            "passcode" => "2B24518AA4C2536CAF7ADCBC635C0751699BB7CC",
            "mem_bankaccno" => "",
            "accountbankno" => "",
            "mem_bankaccount" => "",
            "refundvalue" => "退款測試",
            "refundmode" => 1,
            "buyerid" => "harley@gs8899.com.tw",
            "buyername" => "Harley",
            "buyeremail" => "harley@gs8899.com.tw",
            "refundprice" => "860.0000"
        ];

        $target = new FakeClass();

        $result = $target->fakeEncrypt(json_encode($data, JSON_UNESCAPED_UNICODE));

        $expected = '7zd+oMo5OU+q463pQv4CfuCBf642FxxxlvyfflJwb9PI3pdVzV3nm6zdzrI+FigbhxKNDh0fAWrsVXrfK0+VYrD1yOjDwgn5r+gp4nezlI/dwHBH4BbRj5yZU3GhMnnWyRv6WOWwzlEAO916A/t1ucLuSsLQeGrr0R+o0HNfFQucTP1Reuy5M+rTTd6pMVRgJ2XK/8sLR4BS+YkgQLb7egy5TrwjJSR9Iv31zvPz81YBOSwc28n7k+C661COZzGKULdnugZwVgNZx0rQv+6RRKAcrT0isspnFBHlT0IJccH+XnaqkdE93JTYT4t2XKvYL1fjM5xffXsgXuumu31ytq9SQuMeGIHVMwMFukI3bjwGE7GX0+EAXe0HEl4QF/mjrpQDgl7n41ElUEznbQmi47AbL5jfqe0H/rXmfE0sek3erUtgruwdW2HLfQB5/3RvBPmVbWK1l3Y=';

        $this->assertEquals($expected, $result);
    }
}

class FakeClass
{
    use TripleDESEncrypt;

    public function __construct()
    {
        $this->encrypt_key = config('paynow.refund_encrypt_key');
    }

    public function fakeEncrypt($data)
    {
        return $this->encrypt($data);
    }
}
