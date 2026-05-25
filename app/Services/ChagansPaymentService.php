<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ChagansPaymentService
{
    protected $client;
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $accessToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = 'https://chagans.com';

        $this->clientId     = '689990e2a21e3a73170cae7f';
        $this->clientSecret = '53215649-c60e-42e3-85ea-e721aae81a01';
        $this->accessToken  = 'Bearer ' ."eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJwYXJ0bmVySWQiOiI2ODk5OTBlMmEyMWUzYTczMTcwY2FlN2YiLCJ0b2tlbiI6IjUzMjE1NjQ5LWM2MGUtNDJlMy04NWVhLWU3MjFhYWU4MWEwMSIsImlhdCI6MTc1NjcwMjE2NCwiZXhwIjoxNzg4MjM4MTY0fQ.UESBfOwXxDTkDPHOcO5aREncoAR98CRIC4vfu7IyHXQ";

    }

    /**
     * Create Payment Request
     */
    public function createPaymentRequest($amount, $pgType, $txnId,$callback,$webhook)
    {

        // return [
        //     'amt'=>$amount,
        //     'pgtype'=>$pgType,
        //     'txb'=>$txnId,
        //     'callback' =>$callback,
        //     'hook'=>$webhook

        // ];
        // die();

        $url = $this->baseUrl . "/partnerPg/payRequest";


    // ✅ Request Log
    info('Chagans Payment Request', [
        'url' => $url,
        'headers' => [
            'client-id'     => $this->clientId,
            'client-secret' => $this->clientSecret,
            'Authorization' => $this->accessToken,
            'Content-Type'  => 'application/json',
        ],
        'payload' => [
            'amount' => $amount,
            'pgType' => 'chagans',
            'mode'   => 't0',
            'txnId'  => $txnId,
            'callback'=> $callback,
            'webhook'=> $webhook
        ]
    ]);

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'client-id'     => $this->clientId,
                    'client-secret' => $this->clientSecret,
                    'Authorization' =>$this->accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'amount' => $amount,
                    'pgType' => 'chagans',
                    'mode'   => 't0',
                    'txnId'  => $txnId,
                    'callback'=>$callback,
                    'webhook'=>$webhook
                ]
            ]);

                info('Chagans Payment Request Response: ' . $response->getBody());


            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true)
            ];

        } catch (RequestException $e) {

            return [
                'success' => false,
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'response' => $e->hasResponse()
                    ? json_decode($e->getResponse()->getBody(), true)
                    : null
            ];
        }
    }
}
