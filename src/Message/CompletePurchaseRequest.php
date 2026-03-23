<?php

namespace Omnipay\QnbFinansbank\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

class CompletePurchaseRequest extends RemoteAbstractRequest
{
	/**
	 * Return the callback data from the 3D redirect as-is.
	 *
	 * The callback POST contains ProcReturnCode, AuthCode, OrderId, ErrMsg, etc.
	 *
	 * @throws InvalidRequestException
	 * @return array<string, mixed>
	 */
	public function getData(): array
	{
		$this->validateAll();

		return [
			'ProcReturnCode' => $this->httpRequest->request->get('ProcReturnCode'),
			'AuthCode' => $this->httpRequest->request->get('AuthCode'),
			'OrderId' => $this->httpRequest->request->get('OrderId'),
			'ErrMsg' => $this->httpRequest->request->get('ErrMsg', ''),
		];
	}

	/**
	 * @throws InvalidRequestException
	 */
	protected function validateAll(): void
	{
		$this->validateSettings();
	}

	/**
	 * @param array<string, mixed> $data
	 * @return ResponseInterface|CompletePurchaseResponse
	 */
	public function sendData($data)
	{
		return $this->createResponse($data);
	}

	/**
	 * @param array<string, mixed> $data
	 * @return CompletePurchaseResponse
	 */
	protected function createResponse($data): CompletePurchaseResponse
	{
		return $this->response = new CompletePurchaseResponse($this, $data);
	}
}
