<?php

namespace Omnipay\QnbFinansbank\Tests\Feature;

use Omnipay\QnbFinansbank\Helpers\Helper;
use Omnipay\QnbFinansbank\Tests\TestCase;

class HelperTest extends TestCase
{
    public function test_parse_response_success()
    {
        $body = 'ProcReturnCode=00;;AuthCode=QNBAuth001;;TransId=QNBTxn001;;OrderId=ORDER-001;;ErrMsg=;;';

        $result = Helper::parseResponse($body);

        $this->assertIsArray($result);
        $this->assertEquals('00', $result['ProcReturnCode']);
        $this->assertEquals('QNBAuth001', $result['AuthCode']);
        $this->assertEquals('QNBTxn001', $result['TransId']);
        $this->assertEquals('ORDER-001', $result['OrderId']);
        $this->assertEquals('', $result['ErrMsg']);
    }

    public function test_parse_response_error()
    {
        $body = 'ProcReturnCode=05;;TransId=;;OrderId=ORDER-001;;ErrMsg=Genel red;;';

        $result = Helper::parseResponse($body);

        $this->assertEquals('05', $result['ProcReturnCode']);
        $this->assertEquals('', $result['TransId']);
        $this->assertEquals('Genel red', $result['ErrMsg']);
    }

    public function test_parse_response_empty()
    {
        $result = Helper::parseResponse('');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_hash_3d()
    {
        $hash = Helper::hash3D(
            '5',
            'ORDER-001',
            '100.00',
            'https://example.com/success',
            'https://example.com/fail',
            'Auth',
            '0',
            'random123',
            'StoreKey456',
        );

        $this->assertNotEmpty($hash);
        $this->assertIsString($hash);

        // Verify it matches manual calculation
        $hashString = '5' . 'ORDER-001' . '100.00' . 'https://example.com/success' . 'https://example.com/fail' . 'Auth' . '0' . 'random123' . 'StoreKey456';
        $expectedHash = base64_encode(sha1($hashString, true));

        $this->assertEquals($expectedHash, $hash);
    }

    public function test_hash_3d_produces_base64()
    {
        $hash = Helper::hash3D(
            '5',
            'ORDER-001',
            '100.00',
            'https://example.com/ok',
            'https://example.com/fail',
            'Auth',
            '0',
            'rnd',
            'key',
        );

        $decoded = base64_decode($hash, true);
        $this->assertNotFalse($decoded);

        // SHA1 produces 20 bytes
        $this->assertEquals(20, strlen($decoded));
    }

    public function test_hash_response()
    {
        $hash = Helper::hashResponse(
            'QNBShop001',
            'StoreKey456',
            'ORDER-001',
            'AUTH123',
            '00',
            '1',
            'rnd-xyz',
            'QNBUser',
        );

        $this->assertNotEmpty($hash);
        $this->assertIsString($hash);

        // Verify it matches manual calculation
        $hashString = 'QNBShop001' . 'StoreKey456' . 'ORDER-001' . 'AUTH123' . '00' . '1' . 'rnd-xyz' . 'QNBUser';
        $expectedHash = base64_encode(sha1($hashString, true));

        $this->assertEquals($expectedHash, $hash);
        $this->assertEquals(20, strlen((string) base64_decode($hash, true)));
    }
}
