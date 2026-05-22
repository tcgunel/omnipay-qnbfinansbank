<?php

namespace Omnipay\QnbFinansbank\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\QnbFinansbank\Helpers\Helper;
use Omnipay\QnbFinansbank\Message\CompletePurchaseRequest;
use Omnipay\QnbFinansbank\Message\CompletePurchaseResponse;
use Omnipay\QnbFinansbank\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CompletePurchaseTest extends TestCase
{
    public function test_complete_purchase_response_success()
    {
        $data = [
            'ProcReturnCode' => '00',
            'AuthCode' => 'QNBAuth001',
            'OrderId' => 'ORDER-12345',
            'ErrMsg' => '',
        ];

        $response = new CompletePurchaseResponse(
            $this->getMockRequest(),
            $data
        );

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('00', $response->getCode());
        $this->assertEquals('QNBAuth001', $response->getTransactionReference());
    }

    public function test_complete_purchase_response_error()
    {
        $data = [
            'ProcReturnCode' => '05',
            'AuthCode' => '',
            'OrderId' => 'ORDER-12345',
            'ErrMsg' => '3D dogrulama basarisiz',
        ];

        $response = new CompletePurchaseResponse(
            $this->getMockRequest(),
            $data
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('05', $response->getCode());
        $this->assertEquals('3D dogrulama basarisiz', $response->getMessage());
    }

    public function test_complete_purchase_reads_from_post()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/CompletePurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        // Simulate POST data from bank callback
        $httpRequest = new Request([], [
            'ProcReturnCode' => '00',
            'AuthCode' => 'QNBAuth999',
            'OrderId' => 'ORDER-12345',
            'ErrMsg' => '',
        ]);

        $request = new CompletePurchaseRequest($this->getHttpClient(), $httpRequest);
        $request->initialize($options);

        $data = $request->getData();

        $this->assertEquals('00', $data['ProcReturnCode']);
        $this->assertEquals('QNBAuth999', $data['AuthCode']);
        $this->assertEquals('ORDER-12345', $data['OrderId']);
        $this->assertEquals('', $data['ErrMsg']);
    }

    public function test_complete_purchase_gateway_method()
    {
        $request = $this->gateway->completePurchase([
            'merchantId' => 'QNBShop001',
            'merchantUser' => 'QNBUser',
            'merchantPassword' => 'QNBPass123',
        ]);

        $this->assertInstanceOf(CompletePurchaseRequest::class, $request);
    }

    public function test_complete_purchase_request_validation_error()
    {
        $request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize([]);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    public function test_complete_purchase_accepts_valid_response_hash()
    {
        $request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize([
            'merchantId'       => 'QNBShop001',
            'merchantUser'     => 'QNBUser',
            'merchantPassword' => 'QNBPass123',
            'merchantStorekey' => 'QNBStoreKey456',
        ]);

        $data = [
            'ProcReturnCode' => '00',
            'AuthCode'       => 'QNBAuth001',
            'OrderId'        => 'ORDER-12345',
            '3DStatus'       => '1',
            'ResponseRnd'    => 'RND-123',
        ];

        $data['ResponseHash'] = Helper::hashResponse(
            'QNBShop001',
            'QNBStoreKey456',
            'ORDER-12345',
            'QNBAuth001',
            '00',
            '1',
            'RND-123',
            'QNBUser',
        );

        $response = new CompletePurchaseResponse($request, $data);

        $this->assertTrue($response->isHashValid());
        $this->assertTrue($response->isSuccessful());
    }

    public function test_complete_purchase_rejects_tampered_response_hash()
    {
        $request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize([
            'merchantId'       => 'QNBShop001',
            'merchantUser'     => 'QNBUser',
            'merchantPassword' => 'QNBPass123',
            'merchantStorekey' => 'QNBStoreKey456',
        ]);

        $data = [
            'ProcReturnCode' => '00',
            'AuthCode'       => 'QNBAuth001',
            'OrderId'        => 'ORDER-12345',
            '3DStatus'       => '1',
            'ResponseRnd'    => 'RND-123',
            'ResponseHash'   => 'tampered-hash-value',
        ];

        $response = new CompletePurchaseResponse($request, $data);

        $this->assertFalse($response->isHashValid());
        $this->assertFalse($response->isSuccessful());
    }

    public function test_complete_purchase_skips_hash_check_when_no_storekey()
    {
        $request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize([
            'merchantId'       => 'QNBShop001',
            'merchantUser'     => 'QNBUser',
            'merchantPassword' => 'QNBPass123',
        ]);

        $data = [
            'ProcReturnCode' => '00',
            'AuthCode'       => 'QNBAuth001',
            'OrderId'        => 'ORDER-12345',
            'ResponseHash'   => 'any-hash-value',
        ];

        $response = new CompletePurchaseResponse($request, $data);

        // No merchantStorekey configured -> verification skipped, payment not blocked.
        $this->assertTrue($response->isHashValid());
        $this->assertTrue($response->isSuccessful());
    }
}
