<?php

namespace Omnipay\QnbFinansbank\Message;

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;

class PurchaseResponse extends RemoteAbstractResponse implements RedirectResponseInterface
{
	/** @var bool */
	private $is3D = false;

	/**
	 * @param RequestInterface $request
	 * @param string|array<string, mixed> $data
	 */
	public function __construct(RequestInterface $request, $data)
	{
		if (is_array($data)) {
			$this->is3D = true;
			$this->parsedData = $data;
			parent::__construct($request, $data);
		} else {
			parent::__construct($request, $data);
		}
	}

	public function isSuccessful(): bool
	{
		if ($this->is3D) {
			return false;
		}

		return parent::isSuccessful();
	}

	public function isRedirect(): bool
	{
		return $this->is3D;
	}

	public function getRedirectUrl(): string
	{
		if (!$this->is3D) {
			return '';
		}

		/** @var PurchaseRequest $request */
		$request = $this->getRequest();

		return $request->getApiUrl();
	}

	public function getRedirectMethod(): string
	{
		return 'POST';
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getRedirectData(): array
	{
		return $this->parsedData;
	}
}
