<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class PayoutV2
{
    protected static $baseUrl = "https://sandbox-api.passway.in/api/v1/payout";
    protected static $apiKey;

    public static function init()
    {
        self::$apiKey = config('services.passway.api_key'); // store in config/services.php
    }

    /**
     * Initiate payout request
     */
    public static function initiate(array $payload)
    {
        self::init();
        return self::sendRequest(self::$baseUrl, $payload);
    }

    /**
     * Submit OTP
     */
    public static function submitOtp(array $payload)
    {
        self::init();
        return self::sendRequest(self::$baseUrl . '/submit/otp', $payload);
    }

    /**
     * Resend OTP
     */
    public static function resendOtp(array $payload)
    {
        self::init();
        return self::sendRequest(self::$baseUrl . '/resend/otp', $payload);
    }

    /**
     * Check payout status
     */
    public static function status(array $payload)
    {
        self::init();
        return self::sendRequest(self::$baseUrl . '/status', $payload);
    }

    /**
     * Common request handler
     */
    private static function sendRequest($url, $payload)
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => self::$apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            return $response->json();
        } catch (\Exception $e) {
            return [
                'status'  => 'ERROR',
                'message' => $e->getMessage(),
            ];
        }
    }
}
