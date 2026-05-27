<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpaymentHelper
{
    protected static $baseUrl;

    protected static function init()
    {
        self::$baseUrl = env('IPAY_BASE_URL');
    }

    protected static function headers()
    {
        return [
            'Authorization' => env('IPAY_AUTH'),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    protected static function request($method, $endpoint, $payload = [])
    {
        self::init();

        try {

            $url = self::$baseUrl . $endpoint;

            $http = Http::withHeaders(self::headers());

            if (strtoupper($method) == 'GET') {
                $response = $http->get($url);
            } else {
                $response = $http->post($url, $payload);
            }

            $json = $response->json();

            Log::info('IPAY REQUEST', [
                'url' => $url,
                'payload' => $payload,
                'response' => $json
            ]);

            return [
                'status'  => ($json['status'] ?? '') == 'SUCCESS',
                'code'    => $json['code'] ?? '0x0205',
                'message' => $json['message'] ?? '',
                'data'    => $json['data'] ?? [],
                'raw'     => $json
            ];

        } catch (\Exception $e) {

            Log::error('IPAY API ERROR', [
                'message' => $e->getMessage()
            ]);

            return [
                'status'  => false,
                'code'    => '0x0205',
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Merchant KYC
    |--------------------------------------------------------------------------
    */

    public static function merchantKyc($data)
    {
        return self::request(
            'POST',
            '/v1/service/aeps/kyc',
            $data
        );
    }

    public static function merchantKycStatus($kid)
    {
        return self::request(
            'GET',
            '/v1/service/aeps/kyc/' . $kid
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 2FA
    |--------------------------------------------------------------------------
    */

    public static function twoFactorAuth($data)
    {
        return self::request(
            'POST',
            '/v1/service/aeps/airtel/2fa',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AEPS Transaction
    |--------------------------------------------------------------------------
    */

    public static function aepsTransaction($data)
    {
        return self::request(
            'POST',
            '/v1/service/aeps/airtel/txn',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Transaction Status
    |--------------------------------------------------------------------------
    */

    public static function transactionStatus($data)
    {
        return self::request(
            'POST',
            '/v1/service/aeps/transactionStatus',
            $data
        );
    }
}