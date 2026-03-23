<?php

namespace Omnipay\QnbFinansbank\Tests\Feature;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\QnbFinansbank\Constants\SecureType;
use Omnipay\QnbFinansbank\Constants\TxnType;
use Omnipay\QnbFinansbank\Message\PurchaseRequest;
use Omnipay\QnbFinansbank\Message\PurchaseResponse;
use Omnipay\QnbFinansbank\Tests\TestCase;

class PurchaseTest extends TestCase
{
	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_non3d_purchase_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$this->assertIsArray($data);

		// Verify MbrId is 5
		$this->assertEquals('5', $data['MbrId']);

		// Verify merchant info
		$this->assertEquals('QNBShop001', $data['MerchantId']);
		$this->assertEquals('QNBUser', $data['UserCode']);
		$this->assertEquals('QNBPass123', $data['UserPass']);

		// Verify transaction info
		$this->assertEquals('100.00', $data['PurchAmount']);
		$this->assertEquals('949', $data['Currency']);
		$this->assertEquals('ORDER-12345', $data['OrderId']);
		$this->assertEquals(TxnType::AUTH, $data['TxnType']);
		$this->assertEquals('0', $data['InstallmentCount']);
		$this->assertEquals(SecureType::NON_SECURE, $data['SecureType']);

		// Verify card info
		$this->assertEquals('4355084355084358', $data['Pan']);
		$this->assertEquals('000', $data['Cvv2']);
		$this->assertEquals('1230', $data['Expiry']);

		// Verify no 3D specific fields
		$this->assertArrayNotHasKey('OkUrl', $data);
		$this->assertArrayNotHasKey('FailUrl', $data);
		$this->assertArrayNotHasKey('Hash', $data);
		$this->assertArrayNotHasKey('Rnd', $data);
	}

	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_3d_purchase_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest3D.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$this->assertIsArray($data);

		// Verify MbrId is 5
		$this->assertEquals('5', $data['MbrId']);

		// Verify 3D specific fields
		$this->assertEquals(SecureType::THREE_D_PAY, $data['SecureType']);
		$this->assertEquals('https://example.com/payment/success', $data['OkUrl']);
		$this->assertEquals('https://example.com/payment/fail', $data['FailUrl']);
		$this->assertNotEmpty($data['Hash']);
		$this->assertNotEmpty($data['Rnd']);

		// Verify card info
		$this->assertEquals('4355084355084358', $data['Pan']);
		$this->assertEquals('000', $data['Cvv2']);
		$this->assertEquals('1230', $data['Expiry']);
	}

	public function test_purchase_request_validation_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest-ValidationError.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}

	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_3d_purchase_response_is_redirect()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest3D.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		/** @var PurchaseResponse $response */
		$response = $request->initialize($options)->send();

		$this->assertFalse($response->isSuccessful());

		$this->assertTrue($response->isRedirect());

		$this->assertEquals('POST', $response->getRedirectMethod());

		$this->assertEquals(
			'https://vpostest.qnbfinansbank.com/Gateway/Default.aspx',
			$response->getRedirectUrl()
		);

		$redirectData = $response->getRedirectData();

		$this->assertIsArray($redirectData);
		$this->assertArrayHasKey('MbrId', $redirectData);
		$this->assertArrayHasKey('Hash', $redirectData);
	}

	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_3d_purchase_response_prod_endpoint()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest3D.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$options['testMode'] = false;

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		/** @var PurchaseResponse $response */
		$response = $request->initialize($options)->send();

		$this->assertEquals(
			'https://vpos.qnbfinansbank.com/Gateway/Default.aspx',
			$response->getRedirectUrl()
		);
	}

	public function test_non3d_purchase_sends_http_request_success()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->setMockHttpResponse('PurchaseResponseSuccess.txt');

		$response = $this->gateway->purchase($options)->send();

		$this->assertTrue($response->isSuccessful());

		$this->assertFalse($response->isRedirect());

		$this->assertEquals('00', $response->getCode());

		// Verify the HTTP request was sent
		$requests = $this->getMockedRequests();
		$this->assertCount(1, $requests);

		$httpRequest = $requests[0];
		$this->assertEquals('POST', $httpRequest->getMethod());
		$this->assertStringContainsString(
			'vpostest.qnbfinansbank.com/Gateway/Default.aspx',
			(string) $httpRequest->getUri()
		);

		// Verify the body is form-encoded with MbrId
		$body = (string) $httpRequest->getBody();
		$this->assertStringContainsString('MbrId=5', $body);
		$this->assertStringContainsString('MerchantId=QNBShop001', $body);
	}

	public function test_non3d_purchase_sends_http_request_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->setMockHttpResponse('PurchaseResponseError.txt');

		$response = $this->gateway->purchase($options)->send();

		$this->assertFalse($response->isSuccessful());

		$this->assertFalse($response->isRedirect());

		$this->assertEquals('05', $response->getCode());

		$this->assertEquals('Genel red - Kart numarasi hatali', $response->getMessage());
	}

	public function test_non3d_purchase_prod_endpoint()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$options['testMode'] = false;

		$this->setMockHttpResponse('PurchaseResponseSuccess.txt');

		$this->gateway->purchase($options)->send();

		$requests = $this->getMockedRequests();
		$httpRequest = $requests[0];

		$this->assertStringContainsString(
			'vpos.qnbfinansbank.com/Gateway/Default.aspx',
			(string) $httpRequest->getUri()
		);
	}

	public function test_purchase_gateway_method()
	{
		$request = $this->gateway->purchase([
			'merchantId' => 'QNBShop001',
			'merchantUser' => 'QNBUser',
			'merchantPassword' => 'QNBPass123',
		]);

		$this->assertInstanceOf(PurchaseRequest::class, $request);
	}

	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_purchase_with_installment()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$options['installment'] = 3;

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$this->assertEquals('3', $data['InstallmentCount']);
	}
}
