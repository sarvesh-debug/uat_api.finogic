<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayoutV6Helper
{
    /**
     * Initiate Bank Transfer via Chagan API
     */
    public static function initiate(array $data)
    {
        try {
            $url = "https://chagans.com/ppi/initiateBankTransfer";

            $headers = [
                'Authorization' => 'Bearer ' ."eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwYXJ0bmVySWQiOiI2ODk5OTBlMmEyMWUzYTczMTcwY2FlN2YiLCJ0b2tlbiI6IjUzMjE1NjQ5LWM2MGUtNDJlMy04NWVhLWU3MjFhYWU4MWEwMSIsImlhdCI6MTc1NjcwMjE2NCwiZXhwIjoxNzg4MjM4MTY0fQ.UESBfOwXxDTkDPHOcO5aREncoAR98CRIC4vfu7IyHXQ",
                'client-id'     => '689990e2a21e3a73170cae7f',
                'client-secret' => '53215649-c60e-42e3-85ea-e721aae81a01',
                'Content-Type'  => 'application/json'
            ];

            $response = Http::withHeaders($headers)->post($url, [
                "amount"                     => $data['amount'],
                "txnId"                      => $data['txnId'] ?? uniqid('TXN'),
                "beneficiaryName"            => $data['beneficiaryName'],
                "beneficiaryAccountNumber"   => $data['beneficiaryAccountNumber'],
                "beneficiaryIfscCode"        => $data['beneficiaryIfscCode'],
                "paymentMode"                => $data['paymentMode'] ?? "IMPS",
                "senderMobile"               => $data['senderMobile'] ?? null,
                "callbackUrl"               => $data['callbackUrl']
            ]);

            return $response->json();

        } catch (\Exception $e) {
            Log::error("PayoutV6 API Error: " . $e->getMessage());
            return [
                "status" => false,
                "message" => "API call failed",
                "error" => $e->getMessage()
            ];
        }
    }


    public static function initiateUPI(array $data)
    {
        try {
            $url = "https://chagans.com/ppi/InitiateUpiTransfer";

            $headers = [
                'Authorization' => 'Bearer ' ."eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwYXJ0bmVySWQiOiI2ODk5OTBlMmEyMWUzYTczMTcwY2FlN2YiLCJ0b2tlbiI6IjUzMjE1NjQ5LWM2MGUtNDJlMy04NWVhLWU3MjFhYWU4MWEwMSIsImlhdCI6MTc1NjcwMjE2NCwiZXhwIjoxNzg4MjM4MTY0fQ.UESBfOwXxDTkDPHOcO5aREncoAR98CRIC4vfu7IyHXQ",
                'client-id'     => '689990e2a21e3a73170cae7f',
                'client-secret' => '53215649-c60e-42e3-85ea-e721aae81a01',
                'Content-Type'  => 'application/json'
            ];

            $response = Http::withHeaders($headers)->post($url, [
                "amount"                     => $data['amount'],
                "txnId"                      => $data['txnId'] ?? uniqid('TXN'),
                "upiId"            => $data['upiId'],
                "name"   => $data['name'],
                "callbackUrl"               => $data['callbackUrl']
               
            ]);

            return $response->json();

        } catch (\Exception $e) {
            Log::error("PayoutV6 API Error: " . $e->getMessage());
            return [
                "status" => false,
                "message" => "API call failed",
                "error" => $e->getMessage()
            ];
        }
    }

    public static function initiateUPIStatus(array $data)
    {
        try {
            $url = "https://chagans.com/ppi/checkUpiStatus";

            $headers = [
                'Authorization' => 'Bearer ' ."eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwYXJ0bmVySWQiOiI2ODk5OTBlMmEyMWUzYTczMTcwY2FlN2YiLCJ0b2tlbiI6IjUzMjE1NjQ5LWM2MGUtNDJlMy04NWVhLWU3MjFhYWU4MWEwMSIsImlhdCI6MTc1NjcwMjE2NCwiZXhwIjoxNzg4MjM4MTY0fQ.UESBfOwXxDTkDPHOcO5aREncoAR98CRIC4vfu7IyHXQ",
                'client-id'     => '689990e2a21e3a73170cae7f',
                'client-secret' => '53215649-c60e-42e3-85ea-e721aae81a01',
                'Content-Type'  => 'application/json'
            ];

            $response = Http::withHeaders($headers)->post($url, [
                "order_id"                     => $data['order_id'],
               
               
            ]);

            return $response->json();

        } catch (\Exception $e) {
            Log::error("PayoutV6 API Error: " . $e->getMessage());
            return [
                "status" => false,
                "message" => "API call failed",
                "error" => $e->getMessage()
            ];
        }
    }

     public static function initiateStatus(array $data)
    {
        try {
            $url = "https://chagans.com/ppi/checkStatus";

            $headers = [
                'Authorization' => 'Bearer ' ."eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwYXJ0bmVySWQiOiI2ODk5OTBlMmEyMWUzYTczMTcwY2FlN2YiLCJ0b2tlbiI6IjUzMjE1NjQ5LWM2MGUtNDJlMy04NWVhLWU3MjFhYWU4MWEwMSIsImlhdCI6MTc1NjcwMjE2NCwiZXhwIjoxNzg4MjM4MTY0fQ.UESBfOwXxDTkDPHOcO5aREncoAR98CRIC4vfu7IyHXQ",
                'client-id'     => '689990e2a21e3a73170cae7f',
                'client-secret' => '53215649-c60e-42e3-85ea-e721aae81a01',
                'Content-Type'  => 'application/json'
            ];

            $response = Http::withHeaders($headers)->post($url, [
                "orderId"                     => $data['order_id'],
               
               
            ]);

            return $response->json();

        } catch (\Exception $e) {
            Log::error("PayoutV6 API Error: " . $e->getMessage());
            return [
                "status" => false,
                "message" => "API call failed",
                "error" => $e->getMessage()
            ];
        }
    }

     public static function pgStatus(array $data)
    {
        try {
            $url = "https://chagans.com/partnerPg/checkPgStatus";

            $headers = [
                'Authorization' => 'Bearer ' ."eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwYXJ0bmVySWQiOiI2ODk5OTBlMmEyMWUzYTczMTcwY2FlN2YiLCJ0b2tlbiI6IjUzMjE1NjQ5LWM2MGUtNDJlMy04NWVhLWU3MjFhYWU4MWEwMSIsImlhdCI6MTc1NjcwMjE2NCwiZXhwIjoxNzg4MjM4MTY0fQ.UESBfOwXxDTkDPHOcO5aREncoAR98CRIC4vfu7IyHXQ",
                'client-id'     => '689990e2a21e3a73170cae7f',
                'client-secret' => '53215649-c60e-42e3-85ea-e721aae81a01',
                'Content-Type'  => 'application/json'
            ];

            $response = Http::withHeaders($headers)->post($url, [
                "orderId"                     => $data['order_id'],
               
               
            ]);

            return $response->json();

        } catch (\Exception $e) {
            Log::error("PayoutV6 API Error: " . $e->getMessage());
            return [
                "status" => false,
                "message" => "API call failed",
                "error" => $e->getMessage()
            ];
        }
    }
}
