<?php

namespace Omnipay\QnbFinansbank\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\QnbFinansbank\Constants\SecureType;
use Omnipay\QnbFinansbank\Constants\TxnType;
use Omnipay\QnbFinansbank\Message\TransactionQueryRequest;
use Omnipay\QnbFinansbank\Tests\TestCase;

class TransactionQueryTest extends TestCase
{
    /**
     * @throws InvalidRequestException
     * @throws \JsonException
     */
    public function test_transaction_query_request()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/TransactionQueryRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new TransactionQueryRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $this->assertIsArray($data);
        $this->assertEquals('5', $data['MbrId']);
        $this->assertEquals('QNBShop001', $data['MerchantID']);
        $this->assertEquals('QNBUser', $data['UserCode']);
        $this->assertEquals('QNBPass123', $data['UserPass']);
        $this->assertEquals(SecureType::INQUIRY, $data['SecureType']);
        $this->assertEquals(TxnType::ORDER_INQUIRY, $data['TxnType']);
        $this->assertEquals('ORDER-12345', $data['OrderId']);
        $this->assertEquals('949', $data['Currency']);
        $this->assertEquals('TR', $data['Lang']);
    }

    public function test_transaction_query_request_validation_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/TransactionQueryRequest-ValidationError.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new TransactionQueryRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    public function test_transaction_query_requires_order_reference()
    {
        $request = new TransactionQueryRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize([
            'merchantId' => 'QNBShop001',
            'merchantUser' => 'QNBUser',
            'merchantPassword' => 'QNBPass123',
        ]);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    public function test_transaction_query_success()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/TransactionQueryRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $this->setMockHttpResponse('TransactionQueryResponseSuccess.txt');

        $response = $this->gateway->transactionQuery($options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('00', $response->getCode());
        $this->assertFalse($response->isVoided());
        $this->assertFalse($response->isRefunded());
    }

    public function test_transaction_query_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/TransactionQueryRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $this->setMockHttpResponse('TransactionQueryResponseError.txt');

        $response = $this->gateway->transactionQuery($options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('99', $response->getCode());
        $this->assertEquals('Siparis bulunamadi', $response->getMessage());
    }

    public function test_transaction_query_gateway_method()
    {
        $request = $this->gateway->transactionQuery([
            'merchantId' => 'QNBShop001',
            'merchantUser' => 'QNBUser',
            'merchantPassword' => 'QNBPass123',
        ]);

        $this->assertInstanceOf(TransactionQueryRequest::class, $request);
    }
}
