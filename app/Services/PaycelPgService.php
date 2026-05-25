<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaycelPgService
{
    protected $baseUrl;
    protected $clientId;
    protected $secretKey;
    protected $accessToken;

    public function __construct()
    {
        $this->baseUrl     = env('PAYCEL_BASE_URL');
        $this->clientId    = env('PAYCEL_CLIENT_ID');
        $this->secretKey   = env('PAYCEL_SECRET_KEY');
        $this->accessToken = env('PAYCEL_ACCESS_TOKEN');
    }

    /**
     * Create Payment Request
     */
    public function createPaymentRequest($data)
    {
        try {

            $url = $this->baseUrl . '/member/api/v1/2xp1/transaction';

            $payload = [
                "pycid" => $this->clientId,

                "customer_details" => [
                    "customer_id"    => $data['customer_id'],
                    "customer_name"  => $data['customer_name'],
                    "customer_phone" => $data['customer_phone'],
                    "customer_email" => $data['customer_email'],
                ],

                "amount"        => $data['amount'],
                "reference_id"  => $data['reference_id'],
                "payment_mode"  => $data['payment_mode'],
                "callback_url"  => $data['callback_url'] ?? '',
                "webhook_url"   => $data['webhook_url'] ?? '',
            ];

            Log::info('PAYCEL PAYMENT REQUEST', [
                'url'     => $url,
                'payload' => $payload
            ]);

            $response = Http::withoutVerifying()
            ->withHeaders([
                'secret-key'  => $this->secretKey,
                'Authorization' => $this->accessToken,
                'Content-Type'  => 'application/vnd.api+json',
            ])->post($url, $payload);

            Log::info('PAYCEL PAYMENT RESPONSE', [
                'response' => $response->json()
            ]);

            return [
                'success' => $response->successful(),
                'status'  => $response->status(),
                'data'    => $response->json()
            ];

        } catch (\Exception $e) {

            Log::error('PAYCEL PAYMENT ERROR', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check Transaction Status
     */
   public function checkStatus($referenceId)
{
    try {

        $url = "https://paycel.in/member/api/v1/2xp1/check-status/{$referenceId}";

        $response = Http::withoutVerifying()
            ->withHeaders([
                'secret-key'  => $this->secretKey,
                'Authorization' => $this->accessToken,
                'Content-Type'  => 'application/vnd.api+json',
            ])->get($url);

        $data = $response->json();

        // Amount ko paise se rupees me convert karo
        if (isset($data['data']['response']['ppc_Amount'])) {
            $data['data']['response']['ppc_Amount'] =
                $data['data']['response']['ppc_Amount'] / 100;
        }

        Log::info('PAYCEL STATUS RESPONSE', [
            'response' => $data
        ]);

        return $data;

    } catch (\Exception $e) {

        Log::error('PAYCEL STATUS ERROR', [
            'message' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
    
// public function checkStatus($referenceId)
// {
//     try {

//         $url = "https://paycel.in/member/api/v1/2xp1/check-status/{$referenceId}";

//         $headers = [
//             'secret-key'   => $this->secretKey,
//             'Authorization'=> $this->accessToken,
//             'Content-Type' => 'application/vnd.api+json',
//         ];

//         $response = Http::withoutVerifying()
//             ->withHeaders($headers)
//             ->get($url);

//         return [
//             'request' => [
//                 'url' => $url,
//                 'method' => 'GET',
//                 'headers' => $headers,
//                 'reference_id' => $referenceId
//             ],

//             'provider_response' => [
//                 'status_code' => $response->status(),
//                 'headers' => $response->headers(),
//                 'body' => $response->json(),
//                 'raw' => $response->body()
//             ]
//         ];

//     } catch (\Exception $e) {

//         return [
//             'success' => false,
//             'message' => $e->getMessage()
//         ];
//     }
// }

}