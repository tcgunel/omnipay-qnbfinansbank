<?php

namespace Omnipay\QnbFinansbank\Helpers;

class Helper
{
    /**
     * Parse semicolon-separated response from QNB Finansbank VPos.
     *
     * Response format: "Key1=Value1;;Key2=Value2;;Key3=Value3;;"
     *
     * @param string $responseBody
     * @return array<string, string>
     */
    public static function parseResponse(string $responseBody): array
    {
        $result = [];

        $pairs = explode(';;', $responseBody);

        foreach ($pairs as $pair) {
            $pair = trim($pair);

            if ($pair === '') {
                continue;
            }

            $eqPos = strpos($pair, '=');

            if ($eqPos === false) {
                continue;
            }

            $key = substr($pair, 0, $eqPos);
            $value = substr($pair, $eqPos + 1);

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Generate the 3D Secure request hash for QNB Finansbank VPos.
     *
     * Hash = base64(SHA1(MbrId + OrderId + PurchAmount + OkUrl + FailUrl + TxnType + InstallmentCount + Rnd + MerchantPass))
     *
     * MerchantPass (the 3D Secure store key) is only used to build the hash;
     * it is never sent in the request itself (QNB doc, section 5.2).
     *
     * @param string $mbrId
     * @param string $orderId
     * @param string $amount
     * @param string $okUrl
     * @param string $failUrl
     * @param string $txnType
     * @param string $installmentCount
     * @param string $rnd
     * @param string $merchantPass
     * @return string
     */
    public static function hash3D(
        string $mbrId,
        string $orderId,
        string $amount,
        string $okUrl,
        string $failUrl,
        string $txnType,
        string $installmentCount,
        string $rnd,
        string $merchantPass,
    ): string {
        $hashString = $mbrId . $orderId . $amount . $okUrl . $failUrl . $txnType . $installmentCount . $rnd . $merchantPass;

        return base64_encode(sha1($hashString, true));
    }

    /**
     * Generate the expected 3D Secure response hash for QNB Finansbank VPos.
     *
     * Hash = base64(SHA1(MerchantId + MerchantPass + OrderId + AuthCode + ProcReturnCode + 3DStatus + ResponseRnd + UserCode))
     *
     * Used to confirm a 3D callback genuinely originated from QNB (QNB doc, section 5.2).
     *
     * @param string $merchantId
     * @param string $merchantPass
     * @param string $orderId
     * @param string $authCode
     * @param string $procReturnCode
     * @param string $threeDStatus
     * @param string $responseRnd
     * @param string $userCode
     * @return string
     */
    public static function hashResponse(
        string $merchantId,
        string $merchantPass,
        string $orderId,
        string $authCode,
        string $procReturnCode,
        string $threeDStatus,
        string $responseRnd,
        string $userCode,
    ): string {
        $hashString = $merchantId . $merchantPass . $orderId . $authCode . $procReturnCode . $threeDStatus . $responseRnd . $userCode;

        return base64_encode(sha1($hashString, true));
    }
}
