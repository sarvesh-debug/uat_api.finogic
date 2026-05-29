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
    protected static function getAuthorization()
    {
        $username = env('IPAY_USERNAME_PAYOUT');
        $password = env('IPAY_PASSWORD_PAYOUT');

        return 'Basic ' . base64_encode($username . ':' . $password);
    }

    public static function headers($signature)
    {
        return [

            "Content-Type" => "application/json",

            "Accept" => "application/json",

            "Authorization" => self::getAuthorization(),

            "signature" => $signature
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE SIGNATURE
    |--------------------------------------------------------------------------
    */

   public static function generateSignature($url, $payload = null)
{
    $client = env('IPAY_USERNAME_PAYOUT');

   $salt = base64_decode(env('SALT_KEY'));
    /*
    |--------------------------------------------------------------------------
    | POST API
    |--------------------------------------------------------------------------
    */

    if (!empty($payload)) {

        /*
        |--------------------------------------------------------------------------
        | EXACT JSON
        |--------------------------------------------------------------------------
        */

        $jsonPayload = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );

        /*
        |--------------------------------------------------------------------------
        | BASE64
        |--------------------------------------------------------------------------
        */

        $base64 = base64_encode($jsonPayload);

        /*
        |--------------------------------------------------------------------------
        | RAW STRING
        |--------------------------------------------------------------------------
        */

        $rawString =
            $base64 .
            $url .
            $client .
            "####" .
            $salt;

        /*
        |--------------------------------------------------------------------------
        | SIGNATURE
        |--------------------------------------------------------------------------
        */

        $signature = hash(
            'sha256',
            $rawString
        );

        /*
        |--------------------------------------------------------------------------
        | LOG
        |--------------------------------------------------------------------------
        */

        Log::channel('fundtransfer')->info(
            "Signature Generated",
            [
                'jsonPayload' => $jsonPayload,
                'base64'      => $base64,
                'rawString'   => $rawString,
                'signature'   => $signature
            ]
        );

        return $signature;
    }

    /*
    |--------------------------------------------------------------------------
    | GET API SIGNATURE
    |--------------------------------------------------------------------------
    */

    $rawString =
        $url .
        $client .
        "####" .
        $salt;

    $signature = hash(
        'sha256',
        $rawString
    );

    Log::channel('fundtransfer')->info(
        "GET Signature Generated",
        [
            'rawString' => $rawString,
            'signature' => $signature
        ]
    );

    return $signature;
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