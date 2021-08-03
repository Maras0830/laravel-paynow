<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Maras0830\PayNowSDK\PaymentRefund;
use Orchestra\Testbench\TestCase;

class PaymentRefundTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        $app['config']->set('paynow.debug_mode', true);
        $app['config']->set('paynow.web_no', '13099407');
        $app['config']->set('paynow.password', '12345');
        $app['config']->set('paynow.ec_name', 'ECName');
        $app['config']->set('paynow.refund_encrypt_key', '123456789028229955123456');
    }

    /**
     * @group unit.PaymentRefund
     */
    public function testPaymentRefund()
    {
        //Arrange
        $mock = new MockHandler([
            new Response(200, [], 'S_我是成功訊息,我是passcode'),
            // Add more responses for each response you need
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->app->instance(Client::class, $client);

        //Act
        /** @var PaymentRefund $target */
        $target = app(PaymentRefund::class);
        $res = $target->refund(
            '8000001910145799460',
            '860.0000',
            '退款測試',
            1,
            '',
            '',
            '',
            'harley@gs8899.com.tw',
            'Harley',
            'harley@gs8899.com.tw'
        );

        //Assert
        $this->assertInstanceOf(PaymentRefund::class, $res);
        $this->assertNotEmpty($res->getLastResponse());
        $this->assertArrayHasKey('status', $res->getLastResponse());
        $this->assertArrayHasKey('message', $res->getLastResponse());
        $this->assertArrayHasKey('passcode', $res->getLastResponse());
        $this->assertEquals('S', $res->getLastResponse()['status']);
        $this->assertEquals('我是成功訊息', $res->getLastResponse()['message']);
        $this->assertEquals('我是passcode', $res->getLastResponse()['passcode']);
    }


    /**
     * @group unit.PaymentRefund
     */
    public function testPaymentRefundFail()
    {
        //Arrange
        $mock = new MockHandler([
            new Response(200, [], 'F_錯誤訊息'),
            // Add more responses for each response you need
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->app->instance(Client::class, $client);

        //Act
        /** @var PaymentRefund $target */
        $target = app(PaymentRefund::class);
        $res = $target->refund(
            '8000001910145799460',
            '860.0000',
            '退款測試',
            1,
            '',
            '',
            '',
            'harley@gs8899.com.tw',
            'Harley',
            'harley@gs8899.com.tw'
        );

        //Assert
        $this->assertInstanceOf(PaymentRefund::class, $res);
        $this->assertNotEmpty($res->getLastResponse());
        $this->assertArrayHasKey('status', $res->getLastResponse());
        $this->assertArrayHasKey('message', $res->getLastResponse());
        $this->assertArrayHasKey('passcode', $res->getLastResponse());
        $this->assertEquals('F', $res->getLastResponse()['status']);
        $this->assertEquals('錯誤訊息', $res->getLastResponse()['message']);
        $this->assertEquals('', $res->getLastResponse()['passcode']);
    }
}
