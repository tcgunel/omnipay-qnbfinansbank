<?php

namespace Omnipay\QnbFinansbank\Tests\Feature;

use Omnipay\QnbFinansbank\Message\CompletePurchaseRequest;
use Omnipay\QnbFinansbank\Message\PurchaseRequest;
use Omnipay\QnbFinansbank\Message\RefundRequest;
use Omnipay\QnbFinansbank\Message\VoidRequest;
use Omnipay\QnbFinansbank\Tests\TestCase;

class GatewayTest extends TestCase
{
    public function test_gateway_name()
    {
        $this->assertEquals('QnbFinansbank', $this->gateway->getName());
    }

    public function test_gateway_default_parameters()
    {
        $defaults = $this->gateway->getDefaultParameters();

        $this->assertArrayHasKey('clientIp', $defaults);
        $this->assertArrayHasKey('merchantId', $defaults);
        $this->assertArrayHasKey('merchantUser', $defaults);
        $this->assertArrayHasKey('merchantPassword', $defaults);
        $this->assertArrayHasKey('merchantStorekey', $defaults);
        $this->assertArrayHasKey('installment', $defaults);
        $this->assertArrayHasKey('secure', $defaults);

        $this->assertEquals('127.0.0.1', $defaults['clientIp']);
        $this->assertFalse($defaults['secure']);
    }

    public function test_gateway_purchase_returns_correct_request()
    {
        $request = $this->gateway->purchase([]);

        $this->assertInstanceOf(PurchaseRequest::class, $request);
    }

    public function test_gateway_complete_purchase_returns_correct_request()
    {
        $request = $this->gateway->completePurchase([]);

        $this->assertInstanceOf(CompletePurchaseRequest::class, $request);
    }

    public function test_gateway_void_returns_correct_request()
    {
        $request = $this->gateway->void([]);

        $this->assertInstanceOf(VoidRequest::class, $request);
    }

    public function test_gateway_refund_returns_correct_request()
    {
        $request = $this->gateway->refund([]);

        $this->assertInstanceOf(RefundRequest::class, $request);
    }

    public function test_gateway_getters_setters()
    {
        $this->gateway->setMerchantId('QNBShop001');
        $this->assertEquals('QNBShop001', $this->gateway->getMerchantId());

        $this->gateway->setMerchantUser('QNBUser');
        $this->assertEquals('QNBUser', $this->gateway->getMerchantUser());

        $this->gateway->setMerchantPassword('QNBPass123');
        $this->assertEquals('QNBPass123', $this->gateway->getMerchantPassword());

        $this->gateway->setMerchantStorekey('QNBStoreKey456');
        $this->assertEquals('QNBStoreKey456', $this->gateway->getMerchantStorekey());

        $this->gateway->setInstallment(3);
        $this->assertEquals(3, $this->gateway->getInstallment());

        $this->gateway->setSecure(true);
        $this->assertTrue($this->gateway->getSecure());

        $this->gateway->setClientIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $this->gateway->getClientIp());
    }
}
