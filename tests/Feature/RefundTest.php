<?php

namespace Omnipay\QnbFinansbank\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\QnbFinansbank\Constants\SecureType;
use Omnipay\QnbFinansbank\Constants\TxnType;
use Omnipay\QnbFinansbank\Message\RefundRequest;
use Omnipay\QnbFinansbank\Tests\TestCase;

class RefundTest extends TestCase
{
    /**
     * @throws InvalidRequestException
     * @throws \JsonException
     */
    public function test_refund_request()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/RefundRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $this->assertIsArray($data);
        $this->assertEquals('5', $data['MbrId']);
        $this->assertEquals('QNBShop001', $data['MerchantId']);
        $this->assertEquals('QNBUser', $data['UserCode']);
        $this->assertEquals('QNBPass123', $data['UserPass']);
        $this->assertEquals('50.00', $data['PurchAmount']);
        $this->assertEquals('949', $data['Currency']);
        $this->assertEquals('ORDER-12345', $data['OrgOrderId']);
        $this->assertEquals(TxnType::REFUND, $data['TxnType']);
        $this->assertEquals(SecureType::NON_SECURE, $data['SecureType']);
        $this->assertEquals('TR', $data['Lang']);
    }

    public function test_refund_request_validation_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/RefundRequest-ValidationError.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    public function test_refund_success()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/RefundRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $this->setMockHttpResponse('RefundResponseSuccess.txt');

        $response = $this->gateway->refund($options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('00', $response->getCode());
    }

    public function test_refund_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/RefundRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $this->setMockHttpResponse('RefundResponseError.txt');

        $response = $this->gateway->refund($options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('05', $response->getCode());
        $this->assertEquals('Iade edilecek islem bulunamadi', $response->getMessage());
    }

    public function test_refund_gateway_method()
    {
        $request = $this->gateway->refund([
            'merchantId' => 'QNBShop001',
            'merchantUser' => 'QNBUser',
            'merchantPassword' => 'QNBPass123',
        ]);

        $this->assertInstanceOf(RefundRequest::class, $request);
    }
}
