# Omnipay: QNB Finansbank

**QNB Finansbank sanal pos gateway for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP. This package implements QNB Finansbank support for Omnipay.

## Installation

```bash
composer require tcgunel/omnipay-qnbfinansbank
```

## Available Methods

| Method | Description |
|--------|-------------|
| `purchase()` | Direct (non-3D) sale or 3D Secure redirect |
| `completePurchase()` | Complete 3D Secure payment after bank callback (with response-hash verification) |
| `void()` | Cancel/void a transaction |
| `refund()` | Refund a transaction (full or partial) |
| `transactionQuery()` | Order inquiry — query the bank-side status of an order |

## Supported Features

| Feature | Supported |
|---------|-----------|
| 3D Secure | Yes |
| Non-3D (direct) | Yes |
| Cancel (void) | Yes |
| Refund | Yes |
| Order inquiry (payment status) | Yes |
| BIN lookup | No (not offered by the QNB VPos API) |
| Installment query | No (not offered by the QNB VPos API) |

## Usage

### Gateway Initialization

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('QnbFinansbank');

$gateway->setMerchantId('your_merchant_id');
$gateway->setMerchantUser('your_user_code');
$gateway->setMerchantPassword('your_user_password');
$gateway->setMerchantStorekey('your_store_key'); // Required for 3D Secure
$gateway->setTestMode(true); // Use test endpoint
```

### Non-3D Purchase (Direct Sale)

```php
$response = $gateway->purchase([
    'amount'      => '100.00',
    'currency'    => 'TRY',
    'transactionId' => 'ORDER-12345',
    'secure'      => false,
    'card'        => [
        'number'      => '4508034508034509',
        'expiryMonth' => '12',
        'expiryYear'  => '2030',
        'cvv'         => '000',
    ],
])->send();

if ($response->isSuccessful()) {
    echo 'Transaction ID: ' . $response->getTransactionReference();
} else {
    echo 'Error: ' . $response->getMessage();
}
```

### 3D Secure Purchase

```php
$response = $gateway->purchase([
    'amount'      => '100.00',
    'currency'    => 'TRY',
    'transactionId' => 'ORDER-12345',
    'secure'      => true,
    'returnUrl'   => 'https://yoursite.com/payment/success',
    'cancelUrl'   => 'https://yoursite.com/payment/fail',
    'card'        => [
        'number'      => '4508034508034509',
        'expiryMonth' => '12',
        'expiryYear'  => '2030',
        'cvv'         => '000',
    ],
])->send();

if ($response->isRedirect()) {
    $response->redirect(); // POSTs card data to the bank 3D Secure page
}
```

### Complete 3D Secure Purchase (Callback Handler)

After the bank posts back to your `returnUrl`:

```php
$response = $gateway->completePurchase([])->send();

if ($response->isSuccessful()) {
    echo 'Payment confirmed! Transaction: ' . $response->getTransactionReference();
} else {
    echo 'Payment failed: ' . $response->getMessage();
}
```

The full callback POST payload is read directly from `$_POST` by the request.
`completePurchase()` is successful only when `ProcReturnCode` is `00` **and** the
QNB response hash verifies — see [Response Hash Verification](#response-hash-verification).

### Void (Cancel)

```php
$response = $gateway->void([
    'orderNumber' => 'ORDER-12345',
    'currency'    => 'TRY',
])->send();

if ($response->isSuccessful()) {
    echo 'Transaction voided.';
} else {
    echo 'Error: ' . $response->getMessage();
}
```

### Refund

```php
$response = $gateway->refund([
    'orderNumber' => 'ORDER-12345',
    'amount'      => '50.00',
    'currency'    => 'TRY',
])->send();

if ($response->isSuccessful()) {
    echo 'Refund processed.';
} else {
    echo 'Error: ' . $response->getMessage();
}
```

### Order Inquiry (Payment Status)

Query the current bank-side status of an order — useful for reconciliation
when a 3D callback is never received:

```php
$response = $gateway->transactionQuery([
    'transactionId' => 'ORDER-12345',
    'currency'      => 'TRY',
])->send();

if ($response->isSuccessful()) {
    echo 'Order found at the bank.';
    echo $response->isVoided()   ? ' (voided)'   : '';
    echo $response->isRefunded() ? ' (refunded)' : '';
} else {
    echo 'Not found / failed: ' . $response->getMessage();
}
```

## Hash Algorithm

### Request hash (3D Secure)

```
SHA1Base64(MbrId + OrderId + PurchAmount + OkUrl + FailUrl + TxnType + InstallmentCount + Rnd + MerchantPass)
```

### Response Hash Verification

After a 3D callback, the response hash is checked to confirm it genuinely
came from QNB:

```
SHA1Base64(MerchantId + MerchantPass + OrderId + AuthCode + ProcReturnCode + 3DStatus + ResponseRnd + UserCode)
```

`completePurchase()->isSuccessful()` returns `true` only when `ProcReturnCode`
is `00` and this hash matches. Verification is skipped (not failed) when no
`merchantStorekey` is configured or the bank returns no `ResponseHash`, so a
genuine payment is never rejected purely because the key is missing.

**Note:** `MbrId` is always `5` for QNB Finansbank. `MerchantPass` is the 3D
Secure store key (`merchantStorekey`); it is only used for hashing and is
never transmitted in a request.

## Endpoints

| Environment | URL |
|-------------|-----|
| Test | `https://vpostest.qnb.com.tr/Gateway/Default.aspx` |
| Production | `https://vpos.qnb.com.tr/Gateway/Default.aspx` |

## Key Differences from Denizbank

- Uses `MbrId: 5` field in all requests
- Uses `ErrMsg` instead of `ErrorMessage` for error messages
- Different endpoint URLs (`vpos.qnb.com.tr` vs `inter-vpos.com.tr`)

## Running Tests

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Style

```bash
composer lint
```

## License

MIT License. See [LICENSE](LICENSE) for details.
