<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayoutHelper
{
    /*
    |--------------------------------------------------------------------------
    | HEADERS
    |--------------------------------------------------------------------------
    */

    public static function headers($signature)
    {
        return [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: " . env('PAYOUT_AUTH'),
            "signature: " . $signature
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE SIGNATURE
    |--------------------------------------------------------------------------
    */

    public static function generateSignature($url, $payload = null)
    {
        $client = env('CLIENT_KEY');
        $salt   = env('SALT_KEY');

        /*
        |--------------------------------------------------------------------------
        | POST API
        |--------------------------------------------------------------------------
        */

        if ($payload) {

            $base64 = base64_encode(
                json_encode($payload)
            );

            return hash(
                'sha256',
                $base64 . $url . $client . "####" . $salt
            );
        }

        /*
        |--------------------------------------------------------------------------
        | GET API
        |--------------------------------------------------------------------------
        */

        return hash(
            'sha256',
            $url . $client . "####" . $salt
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HIT API
    |--------------------------------------------------------------------------
    */

    public static function hitApi(
        $method,
        $url,
        $payload = []
    ) {

        $signature = self::generateSignature(
            $url,
            $payload
        );

        $fullUrl = env('IPAY_BASE_URL') . $url;

        Log::info("Payout API Request", [
            'url'       => $fullUrl,
            'method'    => $method,
            'payload'   => $payload,
            'signature' => $signature
        ]);

        $response = Http::withHeaders(
            self::headers($signature)
        );

        if ($method == "POST") {

            $response = $response->post(
                $fullUrl,
                $payload
            );

        } else {

            $response = $response->get($fullUrl);
        }

        $result = $response->json();

        Log::info("Payout API Response", [
            'response' => $result
        ]);

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE CONTACT
    |--------------------------------------------------------------------------
    */

    public static function createContact($payload)
    {
        $url = "/v1/service/payout/contacts";

        return self::hitApi(
            "POST",
            $url,
            $payload
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET CONTACT
    |--------------------------------------------------------------------------
    */

    public static function getContact($contactId)
    {
        $url = "/v1/service/payout/contacts/" . $contactId;

        return self::hitApi(
            "GET",
            $url
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

    public static function createOrder($payload)
    {
        $url = "/v1/service/payout/orders";

        return self::hitApi(
            "POST",
            $url,
            $payload
        );
    }
}