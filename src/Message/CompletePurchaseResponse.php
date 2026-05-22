<?php

namespace Omnipay\QnbFinansbank\Message;

use Omnipay\QnbFinansbank\Helpers\Helper;

class CompletePurchaseResponse extends RemoteAbstractResponse
{
    /**
     * A 3D payment is successful only when the bank approved it (ProcReturnCode
     * "00") and the response hash proves the callback genuinely came from QNB.
     */
    public function isSuccessful(): bool
    {
        return ($this->parsedData['ProcReturnCode'] ?? '') === '00'
            && $this->isHashValid();
    }

    /**
     * Verify the QNB 3D Secure response hash.
     *
     * Returns true when the hash matches. Also returns true when verification
     * is not possible — when the bank returned no ResponseHash, or when no
     * merchantStorekey (MerchantPass) is configured — so a genuine payment is
     * never rejected merely because the key is absent. Returns false only on an
     * explicit mismatch, i.e. a tampered callback.
     */
    public function isHashValid(): bool
    {
        $request = $this->getRequest();

        if (!$request instanceof RemoteAbstractRequest) {
            return true;
        }

        $merchantPass = (string) $request->getMerchantStorekey();
        $responseHash = (string) ($this->parsedData['ResponseHash'] ?? '');

        if ($merchantPass === '' || $responseHash === '') {
            return true;
        }

        $expected = Helper::hashResponse(
            (string) $request->getMerchantId(),
            $merchantPass,
            (string) ($this->parsedData['OrderId'] ?? ''),
            (string) ($this->parsedData['AuthCode'] ?? ''),
            (string) ($this->parsedData['ProcReturnCode'] ?? ''),
            (string) ($this->parsedData['3DStatus'] ?? ''),
            (string) ($this->parsedData['ResponseRnd'] ?? ''),
            (string) $request->getMerchantUser(),
        );

        return hash_equals($expected, $responseHash);
    }
}
