<?php

namespace Omnipay\QnbFinansbank\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

class CompletePurchaseRequest extends RemoteAbstractRequest
{
    /**
     * Return the full callback payload from the 3D redirect.
     *
     * QNB posts the complete 3D result (ProcReturnCode, AuthCode, OrderId,
     * ErrMsg, TransId, HostRefNum, 3DStatus, ResponseRnd, ResponseHash, ...)
     * back to OkUrl / FailUrl as form data. Every field is captured so the
     * response can both report and hash-verify the callback.
     *
     * @throws InvalidRequestException
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $this->validateAll();

        return $this->httpRequest->request->all();
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
