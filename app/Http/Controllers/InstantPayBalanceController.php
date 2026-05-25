<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InstantPayBalanceController extends Controller
{
    public function checkBalance()
    {

        $url = "https://api.instantpay.in/accounts/balance";

        $headers = [
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'Content-Type' => 'application/json'
        ];

       // return $headers;
        $body = [
            "bankProfileId" => "0",
            "accountNumber" => "9119393863",
            "externalRef" => 'TXN1234',
            "latitude" => "20.1236",
            "longitude" => "78.3228"
        ];

        $response = Http::withHeaders($headers)->post($url, $body);
        $responseData=$response->json();
        //return $response;
        $balance=$responseData['data']['balance']['available'];
        //return $balance;
        return response()->json([
            'status' => true,
            'data' => $response->json(),
            'balamce'=>$balance
        ]);

    }
}