<?php

namespace Omnipay\QnbFinansbank\Message;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\QnbFinansbank\Constants\SecureType;
use Omnipay\QnbFinansbank\Constants\TxnType;
use Omnipay\QnbFinansbank\Helpers\Helper;

class PurchaseRequest extends RemoteAbstractRequest
{
	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @return array<string, mixed>
	 */
	public function getData(): array
	{
		$this->validateAll();

		if ($this->getSecure()) {
			return $this->get3DData();
		}

		return $this->getNon3DData();
	}

	/**
	 * Build data for non-3D (direct) purchase.
	 *
	 * @return array<string, mixed>
	 */
	protected function getNon3DData(): array
	{
		$data = [
			'MbrId' => '5',
			'MerchantId' => $this->getMerchantId(),
			'UserCode' => $this->getMerchantUser(),
			'UserPass' => $this->getMerchantPassword(),
			'PurchAmount' => $this->getAmount(),
			'Currency' => $this->getCurrencyNumeric(),
			'OrderId' => $this->getOrderId() ?? $this->getTransactionId(),
			'TxnType' => TxnType::AUTH,
			'InstallmentCount' => $this->getInstallment() ?: '0',
			'SecureType' => SecureType::NON_SECURE,
			'Pan' => $this->getCardAttribute('getNumber'),
			'Cvv2' => $this->getCardAttribute('getCvv'),
			'Expiry' => $this->getCardExpiry(),
			'Lang' => 'TR',
		];

		return $data;
	}

	/**
	 * Build data for 3D Secure purchase (form POST to bank).
	 *
	 * @return array<string, mixed>
	 */
	protected function get3DData(): array
	{
		$orderId = $this->getOrderId() ?? $this->getTransactionId();
		$amount = $this->getAmount();
		$installment = $this->getInstallment() ?: '0';
		$rnd = microtime();

		$hash = Helper::hash3D(
			'5',
			$orderId,
			$amount,
			$this->getReturnUrl(),
			$this->getCancelUrl(),
			TxnType::AUTH,
			$installment,
			$rnd,
			$this->getMerchantStorekey(),
			$this->getMerchantId(),
		);

		$data = [
			'MbrId' => '5',
			'MerchantId' => $this->getMerchantId(),
			'UserCode' => $this->getMerchantUser(),
			'UserPass' => $this->getMerchantPassword(),
			'PurchAmount' => $amount,
			'Currency' => $this->getCurrencyNumeric(),
			'OrderId' => $orderId,
			'TxnType' => TxnType::AUTH,
			'InstallmentCount' => $installment,
			'SecureType' => SecureType::THREE_D_PAY,
			'Pan' => $this->getCardAttribute('getNumber'),
			'Cvv2' => $this->getCardAttribute('getCvv'),
			'Expiry' => $this->getCardExpiry(),
			'Lang' => 'TR',
			'OkUrl' => $this->getReturnUrl(),
			'FailUrl' => $this->getCancelUrl(),
			'Rnd' => $rnd,
			'Hash' => $hash,
		];

		return $data;
	}

	/**
	 * Get card expiry in MMYY format.
	 *
	 * @return string|null
	 */
	protected function getCardExpiry(): ?string
	{
		if (!$this->getCard()) {
			return null;
		}

		$month = str_pad((string) $this->getCard()->getExpiryMonth(), 2, '0', STR_PAD_LEFT);
		$year = substr((string) $this->getCard()->getExpiryYear(), -2);

		return $month . $year;
	}

	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 */
	protected function validateAll(): void
	{
		$this->validateSettings();

		$this->validate('card', 'amount', 'currency');

		$this->getCard()->validate();

		if ($this->getSecure()) {
			$this->validate('merchantStorekey', 'returnUrl', 'cancelUrl');
		}
	}

	/**
	 * @param array<string, mixed> $data
	 * @return ResponseInterface|PurchaseResponse
	 */
	public function sendData($data)
	{
		if ($this->getSecure()) {
			return $this->createResponse($data);
		}

		$responseBody = $this->postForm($data);

		return $this->createResponse($responseBody);
	}

	/**
	 * @param string|array<string, mixed> $data
	 * @return PurchaseResponse
	 */
	protected function createResponse($data): PurchaseResponse
	{
		return $this->response = new PurchaseResponse($this, $data);
	}
}
