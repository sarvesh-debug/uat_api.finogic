<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    protected $client;
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $accessToken;

    public function __construct()
    {
        $this->client = new Client();

        $this->baseUrl = env('IPAY_BASE_URL');


        $username = 'ipayment_f80ba7098a1878941895152405928243';
        $password = '9d48bfbfae5595eaaf3d5a8a618577c21895152405934835';

        $this->accessToken = 'Basic ' . base64_encode($username . ':' . $password);
    }

    /**
     * Create Payment Request
     */
    public function createPaymentRequest(
        $amount,
        
        $txnId,
        $name,
        $email,
        $mobile,
    
    ) {

        $url = $this->baseUrl . "/v1/service/pgcollect/jio/order/generate";

        $payload = [
            'merchantCode'   => 'LSJHD74910470',
            'amount'         => $amount,
            'settlementType' => 'instant',
            'name'           => $name,
            'email'          => $email,
            'mobile'         => $mobile,
            'clientRefId'    => $txnId,
            'redirectUrl'    => 'https://api.finogic.services/api/dynamic/pg/callback',
          
        ];

        // ✅ Request Log
        Log::channel('fundtransfer')->info('Payment Gateway Request', [
            'url'     => $url,
            'headers' => [
                'Authorization' => $this->accessToken,
            ],
            'payload' => $payload
        ]);

        try {

           $response = $this->client->post($url, [
                'force_ip_resolve' => 'v4',
                'headers' => [
                    'Authorization' => $this->accessToken,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'json' => $payload
            ]);
                        $responseBody = json_decode($response->getBody(), true);

            // ✅ Success Log
            Log::channel('fundtransfer')->info('Payment Gateway Response', [
                'txnId'   => $txnId,
                'response' => $responseBody
            ]);

            return [
                'success' => true,
                'status'  => $response->getStatusCode(),
                'data'    => $responseBody
            ];

        } catch (RequestException $e) {

            $errorResponse = null;

            if ($e->hasResponse()) {
                $errorResponse = json_decode(
                    $e->getResponse()->getBody(),
                    true
                );
            }

            // ❌ Error Log
            Log::channel('fundtransfer')->error('Payment Gateway Failed', [
                'txnId'   => $txnId,
                'message' => $e->getMessage(),
                'response'=> $errorResponse
            ]);

            return [
                'success' => false,
                'status'  => $e->getCode(),
                'message' => $e->getMessage(),
                'response'=> $errorResponse
            ];
        }
    }
}