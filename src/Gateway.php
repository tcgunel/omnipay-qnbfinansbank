<?php

namespace Omnipay\QnbFinansbank;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\QnbFinansbank\Message\CompletePurchaseRequest;
use Omnipay\QnbFinansbank\Message\PurchaseRequest;
use Omnipay\QnbFinansbank\Message\RefundRequest;
use Omnipay\QnbFinansbank\Message\VoidRequest;
use Omnipay\QnbFinansbank\Traits\PurchaseGettersSetters;

/**
 * QNB Finansbank Gateway
 *
 * (c) Tolga Can Gunel
 * 2015, mobius.studio
 * http://www.github.com/tcgunel/omnipay-qnbfinansbank
 *
 * @method \Omnipay\Common\Message\NotificationInterface acceptNotification(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface authorize(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface createCard(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface updateCard(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface deleteCard(array $options = [])
 */
class Gateway extends AbstractGateway
{
	use PurchaseGettersSetters;

	public function getName(): string
	{
		return 'QnbFinansbank';
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getDefaultParameters(): array
	{
		return [
			'clientIp' => '127.0.0.1',
			'merchantId' => '',
			'merchantUser' => '',
			'merchantPassword' => '',
			'merchantStorekey' => '',
			'installment' => '',
			'secure' => false,
		];
	}

	/**
	 * @param array<string, mixed> $options
	 * @return AbstractRequest|PurchaseRequest
	 */
	public function purchase(array $options = [])
	{
		return $this->createRequest(PurchaseRequest::class, $options);
	}

	/**
	 * @param array<string, mixed> $options
	 * @return AbstractRequest|CompletePurchaseRequest
	 */
	public function completePurchase(array $options = [])
	{
		return $this->createRequest(CompletePurchaseRequest::class, $options);
	}

	/**
	 * @param array<string, mixed> $options
	 * @return AbstractRequest|VoidRequest
	 */
	public function void(array $options = [])
	{
		return $this->createRequest(VoidRequest::class, $options);
	}

	/**
	 * @param array<string, mixed> $options
	 * @return AbstractRequest|RefundRequest
	 */
	public function refund(array $options = [])
	{
		return $this->createRequest(RefundRequest::class, $options);
	}
}
