<?php

namespace Omnipay\QnbFinansbank\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\QnbFinansbank\Constants\SecureType;
use Omnipay\QnbFinansbank\Constants\TxnType;

class VoidRequest extends RemoteAbstractRequest
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
            'OrgOrderId' => $this->getOrderNumber() ?? $this->getTransactionId(),
            'TxnType' => TxnType::VOID,
            'SecureType' => SecureType::NON_SECURE,
            'Currency' => $this->getCurrencyNumeric(),
            'Lang' => 'TR',
        ];
    }

    /**
     * @throws InvalidRequestException
     */
    protected function validateAll(): void
    {
        $this->validateSettings();

        if (!$this->getOrderNumber() && !$this->getTransactionId()) {
            throw new InvalidRequestException('The orderNumber or transactionId parameter is required');
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return ResponseInterface|VoidResponse
     */
    public function sendData($data)
    {
        $responseBody = $this->postForm($data);

        return $this->createResponse($responseBody);
    }

    /**
     * @param string $data
     * @return VoidResponse
     */
    protected function createResponse($data): VoidResponse
    {
        return $this->response = new VoidResponse($this, $data);
    }
}
