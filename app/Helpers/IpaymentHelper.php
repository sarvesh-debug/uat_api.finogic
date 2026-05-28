<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IPaymentHelper
{
    protected static $baseUrl;
    protected static $auth;

    public static function init()
    {
        self::$baseUrl = env('PAYCC_BASE_URL');
        self::$auth = env('PAYCC_AUTH');
    }

    protected static function getAuthorization()
    {
        $username = env('IPAY_USERNAME_PAYCC');
        $password = env('IPAY_PASSWORD_PAYCC');

        return 'Basic ' . base64_encode($username . ':' . $password);
    }

    public static function headers()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => self::getAuthorization(),
        ];
    }

    /**
     * Common API Request Handler
     */
    protected static function request($method, $endpoint, $payload = [])
    {
        self::init();

        $url = self::$baseUrl . $endpoint;

        try {

            // Request Log
            Log::channel('daily')->info('PAYCC API REQUEST', [
                'method'   => strtoupper($method),
                'url'      => $url,
                'payload'  => $payload,
                'headers'  => self::headers(),
                'datetime' => now()->toDateTimeString(),
            ]);

            // API Call
            $response = Http::withHeaders(self::headers());

            if (strtolower($method) === 'get') {
                $response = $response->get($url);
            } else {
                $response = $response->post($url, $payload);
            }

            $responseData = $response->json();

            // Response Log
            Log::channel('daily')->info('PAYCC API RESPONSE', [
                'method'      => strtoupper($method),
                'url'         => $url,
                'payload'     => $payload,
                'status_code' => $response->status(),
                'response'    => $responseData,
                'datetime'    => now()->toDateTimeString(),
            ]);

            return $responseData;

        } catch (\Exception $e) {

            // Exception Log
            Log::channel('daily')->error('PAYCC API ERROR', [
                'method'    => strtoupper($method),
                'url'       => $url,
                'payload'   => $payload,
                'message'   => $e->getMessage(),
                'line'      => $e->getLine(),
                'file'      => $e->getFile(),
                'datetime'  => now()->toDateTimeString(),
            ]);

            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public static function initKyc($data)
    {
        return self::request(
            'post',
            '/v1/service/paycc/init/kyc',
            $data
        );
    }

    public static function kycStatus($kid)
    {
        return self::request(
            'get',
            '/v1/service/paycc/kyc/status/' . $kid
        );
    }

    public static function customerCheck($data)
    {
        return self::request(
            'post',
            '/v1/service/paycc/customer',
            $data
        );
    }

    public static function addCard($data)
    {
        return self::request(
            'post',
            '/v1/service/paycc/add/verify/card',
            $data
        );
    }

    public static function deleteCard($data)
    {
        return self::request(
            'post',
            '/v1/service/paycc/delete/creditcard',
            $data
        );
    }

    public static function categories($data)
    {
        return self::request(
            'post',
            '/v1/service/paycc/category',
            $data
        );
    }
}