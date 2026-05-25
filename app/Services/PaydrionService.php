<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaydrionService
{
    public function payoutImps($data)
    {

        $payload = [
            "APIID"      => env('PAYDRION_API_ID'),
            "Token"      => env('PAYDRION_TOKEN'),
            "MethodName" => "payout",
            "Amount"     => $data['amount'],
            "Accountno"  => $data['account_no'],
            "Mobile"     => $data['mobile'],
            "IFSC"       => $data['ifsc'],
            "Name"       => $data['name'],
            "IP"         => request()->ip(),
            "BankName"   => $data['bank_name'],
            "OrderID"    => $data['order_id'],
            "Mode"       => 'IMPS',
            "Latitude"   => $data['latitude'] ?? "0",
            "Longitude"  => $data['longitude'] ?? "0",
        ];

        // Request Log
        Log::channel('PaydrionImpsService')->info(
            "Paydrion IMPS Payout Request",
            [
                'url'     => env('PAYDRION_API_URL'),
                'payload' => $payload,
            ]
        );

        try {

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(env('PAYDRION_API_URL'), $payload);

            $responseData = $response->json();

            // Response Log
            Log::channel('PaydrionImpsService')->info(
                "Paydrion IMPS Payout Response",
                [
                    'status_code' => $response->status(),
                    'response'    => $responseData,
                ]
            );

            return $responseData;

        } catch (\Exception $e) {

            // Error Log
            Log::channel('PaydrionImpsService')->error(
                "Paydrion IMPS Payout Error",
                [
                    'message' => $e->getMessage(),
                    'line'    => $e->getLine(),
                    'file'    => $e->getFile(),
                ]
            );

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }



        public function payoutUpi($data)
    {

        $payload = [
            "APIID"      => env('PAYDRION_API_ID'),
            "Token"      => env('PAYDRION_TOKEN'),
            "MethodName" => "payout",
            "Amount"     => $data['amount'],
            "Accountno"  => $data['account_no'],
            "Mobile"     => $data['mobile'],
            "IFSC"       => 'UPI',
            "Name"       => $data['name'],
            "IP"         => request()->ip(),
            "BankName"   =>'UPI',
            "OrderID"    => $data['order_id'],
            "Mode"       => 'UPI',
            "Latitude"   => $data['latitude'] ?? "0",
            "Longitude"  => $data['longitude'] ?? "0",
        ];

        // Request Log
        Log::channel('PaydrionUpiService')->info(
            "Paydrion UPI Payout Request",
            [
                'url'     => env('PAYDRION_API_URL'),
                'payload' => $payload,
            ]
        );

        try {

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(env('PAYDRION_API_URL'), $payload);

            $responseData = $response->json();

            // Response Log
            Log::channel('PaydrionUpiService')->info(
                "Paydrion UPI Payout Response",
                [
                    'status_code' => $response->status(),
                    'response'    => $responseData,
                ]
            );

            return $responseData;

        } catch (\Exception $e) {

            // Error Log
            Log::channel('PaydrionUpiService')->error(
                "Paydrion UPI Payout Error",
                [
                    'message' => $e->getMessage(),
                    'line'    => $e->getLine(),
                    'file'    => $e->getFile(),
                ]
            );

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }



//payIn

        public function payoutPayIn($data)
    {

         $payload = [
            "APIID"      => env('PAYDRION_API_ID'),
            "Token"      => env('PAYDRION_TOKEN'),
            "MethodName" => "payout",
            "Amount"     => $data['amount'],
            "Accountno"  => $data['account_no'],
            "Mobile"     => $data['mobile'],
            "IFSC"       => $data['ifsc'],
            "Name"       => $data['name'],
            "IP"         => request()->ip(),
            "BankName"   => $data['bank_name'],
            "OrderID"    => $data['order_id'],
            "Mode"       => 'PayIn',
            "Latitude"   => $data['latitude'] ?? "0",
            "Longitude"  => $data['longitude'] ?? "0",
        ];

        // Request Log
        Log::channel('PaydrionPayInService')->info(
            "Paydrion PayIn Payout Request",
            [
                'url'     => env('PAYDRION_API_URL'),
                'payload' => $payload,
            ]
        );

        try {

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(env('PAYDRION_API_URL'), $payload);

            $responseData = $response->json();

            // Response Log
            Log::channel('PaydrionPayInService')->info(
                "Paydrion PayIn Payout Response",
                [
                    'status_code' => $response->status(),
                    'response'    => $responseData,
                ]
            );

            return $responseData;

        } catch (\Exception $e) {

            // Error Log
            Log::channel('PaydrionUpiService')->error(
                "Paydrion UPI Payout Error",
                [
                    'message' => $e->getMessage(),
                    'line'    => $e->getLine(),
                    'file'    => $e->getFile(),
                ]
            );

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }




    public static function apiStatusChk(array $data)
    {
        try {
            $url = env('PAYDRION_API_URL');

            $headers = [
                'Content-Type'  => 'application/json'
            ];

            $payload=[
                  "APIID"=> env('PAYDRION_API_ID'),
                "Token"=> env('PAYDRION_TOKEN'),
                "MethodName"=> "checkstatus",
                "OrderID"=> $data['order_id'],
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
}