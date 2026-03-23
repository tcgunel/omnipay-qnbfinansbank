<?php

namespace Omnipay\QnbFinansbank\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\QnbFinansbank\Constants\SecureType;
use Omnipay\QnbFinansbank\Constants\TxnType;

class RefundRequest extends RemoteAbstractRequest
{
    /**
     * @throws InvalidRequestException
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $this->validateAll();

        return [
            'MbrId' => '5',
            'MerchantId' => $this->getMerchantId(),
            'UserCode' => $this->getMerchantUser(),
            'UserPass' => $this->getMerchantPassword(),
            'PurchAmount' => $this->getAmount(),
            'Currency' => $this->getCurrencyNumeric(),
            'OrgOrderId' => $this->getOrderNumber() ?? $this->getTransactionId(),
            'TxnType' => TxnType::REFUND,
            'SecureType' => SecureType::NON_SECURE,
            'Lang' => 'TR',
        ];
    }

    /**
     * @throws InvalidRequestException
     */
    protected function validateAll(): void
    {
        $this->validateSettings();

        $this->validate('amount', 'currency');

        if (!$this->getOrderNumber() && !$this->getTransactionId()) {
            throw new InvalidRequestException('The orderNumber or transactionId parameter is required');
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return ResponseInterface|RefundResponse
     */
    public function sendData($data)
    {
        $responseBody = $this->postForm($data);

        return $this->createResponse($responseBody);
    }

    /**
     * @param string $data
     * @return RefundResponse
     */
    protected function createResponse($data): RefundResponse
    {
        return $this->response = new RefundResponse($this, $data);
    }
}
