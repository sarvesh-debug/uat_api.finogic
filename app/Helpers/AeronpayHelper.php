<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class AeronpayHelper
{
  public static function initiateUPIPayout(array $data, $request = null)
{
    try {
        $url = "https://superprodapi.aeronpay.in/api/core-services/serviceapi-prod/finance/securepay/v2/payout/upi_payout_txn";

        $headers = [
            'client-id'     => env('AERONPAY_CLIENT_ID'),
            'client-secret' => env('AERONPAY_CLIENT_SECRET'),
            'Content-Type'  => 'application/json'
        ];

        // ✅ IPs
        $clientIp   = $request ? $request->ip() : 'N/A';
        $serverIPv4 = trim(@file_get_contents('https://api.ipify.org'));
        $serverIPv6 = trim(@file_get_contents('https://api64.ipify.org'));

        $payload = [
            "bankProfileId"      => $data['bankProfileId'] ?? "1",
            "transferMode"       => "UPI", 
            "remarks"            => $data['remarks'] ?? "UPI Payout",
            "payment_type"       => $data['payment_type'] ?? "CUSTOMER_SETTLEMENT",
            "latitude"           => $data['latitude'] ?? "28.6139",
            "longitude"          => $data['longitude'] ?? "77.2090",
            "accountNumber"      => "17313821998",
            "amount"             => $data['amount'],
            "client_referenceId" => $data['refId'] ?? uniqid('UPI_'),

            "beneDetails" => [
                "vpa"      => $data['vpa'],
                "name"     => $data['name'],
                "email"    => $data['email'] ?? "credxpay@gmail.com",
                "phone"    => $data['phone'],
                "address1" => $data['address'] ?? "India"
            ]
        ];

        // 🔥 UNIQUE REQUEST ID (for tracking)
        $requestId = uniqid('AERONPAY_UPI_');

        // ✅ REQUEST LOG
        Log::info("AeronPay UPI FULL LOG", [
            'request_id'   => $requestId,
            'type'         => 'REQUEST',
            'url'          => $url,
            'headers'      => $headers,
            'payload'      => $payload,
            'client_ip'    => $clientIp,
            'server_ipv4'  => $serverIPv4,
            'server_ipv6'  => $serverIPv6,
            'timestamp'    => now()->toDateTimeString(),
        ]);

        $response = Http::withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ]
        ])->withHeaders($headers)->post($url, $payload);
            //     $response = new Response(new \GuzzleHttp\Psr7\Response(
            //     200,
            //     [],
            //     json_encode([
            //             "status" => "PENDING",
            //             "statusCode" => "201",
            //             "message" => "Transaction Under Process",
            //             "data" => [
            //                 "transactionId" => "ARNPY17762454823453PT984",
            //                 "utr" => null,
            //                 "client_referenceId" => "UPI_69df5aea0ceac",
            //                 "acknowledged" => 0
            //             ]
                    
            //     ])
            // ));

        // ✅ RESPONSE LOG
        Log::info("AeronPay UPI FULL LOG", [
            'request_id'   => $requestId,
            'type'         => 'RESPONSE',
            'status'       => $response->status(),
            'response'     => $response->json(),
            'client_ip'    => $clientIp,
            'server_ipv4'  => $serverIPv4,
            'server_ipv6'  => $serverIPv6,
            'timestamp'    => now()->toDateTimeString(),
        ]);

        return $response->json();

    } catch (\Exception $e) {

        Log::error("AeronPay UPI FULL LOG", [
            'type'         => 'ERROR',
            'message'      => $e->getMessage(),
            'client_ip'    => $clientIp ?? 'N/A',
            'server_ipv4'  => $serverIPv4 ?? 'N/A',
            'server_ipv6'  => $serverIPv6 ?? 'N/A',
            'timestamp'    => now()->toDateTimeString(),
        ]);

        return [
            "status"  => false,
            "message" => "API call failed",
            "error"   => $e->getMessage()
        ];
    }
}
   public static function initiate(array $data)
{
    try {
        $url = "https://superprodapi.aeronpay.in/api/core-services/serviceapi-prod/finance/securepay/v2/payout/imps_payment";

        $headers = [
            'client-id'     => env('AERONPAY_CLIENT_ID'),
            'client-secret' => env('AERONPAY_CLIENT_SECRET'),
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            "bankProfileId"      => $data['bankProfileId'] ?? "1",
            "accountNumber"      => "17313821998",
            "latitude"           => $data['latitude'] ?? "28.6139",
            "longitude"          => $data['longitude'] ?? "77.2090",
            "amount"             => $data['amount'],
            "client_referenceId" => $data['refId'] ?? uniqid('IMPS_'),
            "transferMode"       => "IMPS",
            "remarks"            => $data['remarks'] ?? "IMPS Payout",

            "beneDetails" => [
                "bankAccount" => $data['bankAccount'],
                "ifsc"        => $data['ifsc'],
                "name"        => $data['name'],
                "email"       => $data['email'] ?? "test@gmail.com",
                "phone"       => $data['phone'],
                "address1"    => $data['address'] ?? "India"
            ]
        ];

        $requestId = uniqid('IMPS_');

        // ✅ REQUEST LOG
        Log::info("AeronPay IMPS FULL LOG", [
            'request_id' => $requestId,
            'type'       => 'REQUEST',
            'url'        => $url,
            'headers'    => [
                'client-id' => $headers['client-id'],
                'client-secret' => '****'
            ],
            'payload'    => $payload,
            'timestamp'  => now()
        ]);

        $response = Http::withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ]
        ])->withHeaders($headers)->post($url, $payload);

        // ✅ RESPONSE LOG
        Log::info("AeronPay IMPS FULL LOG", [
            'request_id' => $requestId,
            'type'       => 'RESPONSE',
            'status'     => $response->status(),
            'response'   => $response->json(),
            'timestamp'  => now()
        ]);

        return $response->json();

    } catch (\Exception $e) {

        Log::error("AeronPay IMPS ERROR", [
            'message' => $e->getMessage()
        ]);

        return [
            "status"  => false,
            "message" => "API call failed",
            "error"   => $e->getMessage()
        ];
    }
}



    public static function status(array $data)
    {
        try {
            $url = "https://api.aeronpay.in/api/serviceapi-prod/api/reports/transactionStatus";

             $headers = [
            'client-id'     => env('AERONPAY_CLIENT_ID'),
            'client-secret' => env('AERONPAY_CLIENT_SECRET'),
            'Content-Type'  => 'application/json'
        ];

            $response = Http::withHeaders($headers)->post($url, [
                "client_referenceId"                     => $data['client_referenceId'],
                "date_of_transaction"                     => $data['date_of_transaction'],
                "mobile"                     => $data['mobile'],
               
               
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