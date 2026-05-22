<?php

namespace Omnipay\QnbFinansbank\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\QnbFinansbank\Constants\SecureType;
use Omnipay\QnbFinansbank\Constants\TxnType;

/**
 * QNB Finansbank order inquiry — "Sipariş Sorgulama" (QNB doc, section 5.19).
 *
 * Queries the current bank-side status of a previously submitted order so a
 * merchant can reconcile payments whose 3D callback was never received.
 */
class TransactionQueryRequest extends RemoteAbstractRequest
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
            'MerchantID' => $this->getMerchantId(),
            'UserCode' => $this->getMerchantUser(),
            'UserPass' => $this->getMerchantPassword(),
            'SecureType' => SecureType::INQUIRY,
            'TxnType' => TxnType::ORDER_INQUIRY,
            'OrderId' => $this->getOrderNumber() ?? $this->getTransactionId(),
            'Currency' => $this->getCurrencyNumeric() ?? '949',
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
     * @return ResponseInterface|TransactionQueryResponse
     */
    public function sendData($data)
    {
        $responseBody = $this->postForm($data);

        return $this->createResponse($responseBody);
    }

    /**
     * @param string $data
     * @return TransactionQueryResponse
     */
    protected function createResponse($data): TransactionQueryResponse
    {
        return $this->response = new TransactionQueryResponse($this, $data);
    }
}
