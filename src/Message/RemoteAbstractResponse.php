<?php

namespace Omnipay\QnbFinansbank\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\QnbFinansbank\Helpers\Helper;

abstract class RemoteAbstractResponse extends AbstractResponse
{
    /** @var array<string, string> */
    protected $parsedData = [];

    /**
     * @param RequestInterface $request
     * @param string|array<string, string> $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        if (is_string($data)) {
            $this->parsedData = Helper::parseResponse($data);
        } elseif (is_array($data)) {
            $this->parsedData = $data;
        }
    }

    public function isSuccessful(): bool
    {
        return ($this->parsedData['ProcReturnCode'] ?? '') === '00';
    }

    public function getMessage(): ?string
    {
        return $this->parsedData['ErrMsg'] ?? null;
    }

    public function getCode(): ?string
    {
        return $this->parsedData['ProcReturnCode'] ?? null;
    }

    public function getTransactionReference(): ?string
    {
        return $this->parsedData['TransId'] ?? $this->parsedData['AuthCode'] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function getData(): array
    {
        return $this->parsedData;
    }

    public function getRedirectData()
    {
        return null;
    }

    public function getRedirectUrl(): string
    {
        return '';
    }
}
