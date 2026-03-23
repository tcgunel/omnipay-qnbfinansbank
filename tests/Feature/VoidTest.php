<?php

namespace Omnipay\QnbFinansbank\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\QnbFinansbank\Constants\SecureType;
use Omnipay\QnbFinansbank\Constants\TxnType;
use Omnipay\QnbFinansbank\Message\VoidRequest;
use Omnipay\QnbFinansbank\Tests\TestCase;

class VoidTest extends TestCase
{
    /**
     * @throws InvalidRequestException
     * @throws \JsonException
     */
    public function test_void_request()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $this->assertIsArray($data);
        $this->assertEquals('5', $data['MbrId']);
        $this->assertEquals('QNBShop001', $data['MerchantId']);
        $this->assertEquals('QNBUser', $data['UserCode']);
        $this->assertEquals('QNBPass123', $data['UserPass']);
        $this->assertEquals('ORDER-12345', $data['OrgOrderId']);
        $this->assertEquals(TxnType::VOID, $data['TxnType']);
        $this->assertEquals(SecureType::NON_SECURE, $data['SecureType']);
        $this->assertEquals('949', $data['Currency']);
        $this->assertEquals('TR', $data['Lang']);
    }

    public function test_void_request_validation_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest-ValidationError.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    public function test_void_success()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $this->setMockHttpResponse('VoidResponseSuccess.txt');

        $response = $this->gateway->void($options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('00', $response->getCode());
    }

    public function test_void_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $this->setMockHttpResponse('VoidResponseError.txt');

        $response = $this->gateway->void($options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('05', $response->getCode());
        $this->assertEquals('Iptal edilecek islem bulunamadi', $response->getMessage());
    }

    public function test_void_gateway_method()
    {
        $request = $this->gateway->void([
            'merchantId' => 'QNBShop001',
            'merchantUser' => 'QNBUser',
            'merchantPassword' => 'QNBPass123',
        ]);

        $this->assertInstanceOf(VoidRequest::class, $request);
    }
}
