<?php

namespace Omnipay\QnbFinansbank\Message;

/**
 * QNB Finansbank order inquiry response — "Sipariş Sorgulama" (QNB doc, section 5.19).
 *
 * A "00" ProcReturnCode means the inquiry found the order. IsVoided / IsRefunded
 * report whether it was later cancelled or refunded.
 */
class TransactionQueryResponse extends RemoteAbstractResponse
{
    /**
     * Whether the queried order has been voided/cancelled.
     */
    public function isVoided(): bool
    {
        return strcasecmp((string) ($this->parsedData['IsVoided'] ?? ''), 'true') === 0;
    }

    /**
     * Whether the queried order has been refunded.
     */
    public function isRefunded(): bool
    {
        return strcasecmp((string) ($this->parsedData['IsRefunded'] ?? ''), 'true') === 0;
    }
}
