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
	 * Generate 3D Secure hash for QNB Finansbank VPos.
	 *
	 * Hash = base64(SHA1(MbrId + OrderId + PurchAmount + OkUrl + FailUrl + TxnType + InstallmentCount + Rnd + MerchantPass + MerchantId))
	 *
	 * Note: QNB Finansbank uses MerchantPass (storekey) at the end, unlike Denizbank which also includes MerchantId.
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
	 * @param string $merchantId
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
		string $merchantId,
	): string {
		$hashString = $mbrId . $orderId . $amount . $okUrl . $failUrl . $txnType . $installmentCount . $rnd . $merchantPass;

		return base64_encode(sha1($hashString, true));
	}
}
