<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class bbpsHelper
{
    protected static $baseUrl = 'https://chagans.com';

    protected static function headers()
    {
        return [
            'client-id'     => env('BBPS_CLIENT_ID'),
            'client-secret' => env('BBPS_CLIENT_SECRET'),
            'authorization' => 'Bearer ' . env('BBPS_TOKEN'),
            'apiType'       => 'bbps',
            'Content-Type'  => 'application/json'
        ];
    }

    // ✅ 1. Get Categories
    public static function getCategory()
    {
        try {
            $response = Http::withHeaders(self::headers())
                ->post(self::$baseUrl . '/bbps/getCategory');

                //return $response;

            $data = $response->json();

            $categories = [];

            foreach ($data['data'] ?? [] as $key => $value) {
                $categories[] = [
                    'id' => $key,
                    'name' => $value
                ];
            }
    // return "wddwdqwd";

            if($response['success']==true)
                {
                   // return "ok";
                return [
                'success' => true,
                'message' => 'Categories fetched',
                'data' => $categories
            ];
                }
            else{

            //return "not ok";
                 return [
                'success' => true,
                'message' => 'Categories fetched Failed',
                'data' => $categories
            ];
            }
           

        } catch (\Exception $e) {
            return self::error($e);
        }
    }

    // ✅ 2. Get Billers
    public static function getBillers(array $requestData)
    {
        try {
            $response = Http::withHeaders(self::headers())
                ->post(self::$baseUrl . '/bbps/getBiller', [
                    'categoryKey' => $requestData['category_id']
                ]);
           // return $response;

            if($response['success']==true)
                {

            //return "Jello";
                return [
                'success' => true,
                'message' => 'Billers fetched',
                'category'=>$response['category'] ?? '',
                'categoryName'=>$response['categoryName'] ?? '',
                'data' => $response['data'] ?? []
            ];
                }
            else
                {
                    //return "hhh";
                     return [
                'success' => false,
                'message' => 'Billers fetched Failed',
                'data' => $response['data'] ?? []
            ];
                }
           

        } catch (\Exception $e) {
            return self::error($e);
        }
    }

    // ✅ 3. Get Biller Fields
    public static function getBillerFields(array $requestData)
    {
        try {
            $response = Http::withHeaders(self::headers())
                ->post(self::$baseUrl . '/bbps/getBillerField', [
                    'billerId' => $requestData['biller_id']
                ]);

                //return $response;
                 if($response['success']==true)
                {

            //return "Jello";
                return [
                'success' => true,
                'message' => 'Billers fetched',
                'category'=>$response['category'] ?? '',
                'categoryName'=>$response['categoryName'] ?? '',
                'data' => [
                    'biller_name' => $response['data']['billerName'] ?? '',
                    'enquiry_id'  => $response['enquiryId'] ?? '',
                    'fields'      => $response['data']['requiredFields'] ?? []
                ]
            ];
                }
            else
                {
                    //return "hhh";
                     return [
                'success' => false,
                'message' => 'Billers fetched Failed',
                'data' => $response['data'] ?? []
            ];
                }
            

        } catch (\Exception $e) {
            return self::error($e);
        }
    }

    // ✅ 4. Fetch Bill
    public static function fetchBill(array $requestData ,string $merchantId)
    {
        try {
            $response = Http::withHeaders(self::headers())
                ->post(self::$baseUrl . '/bbps/fetchBill', [
                    'billerId'   => $requestData['biller_id'],
                    'enquiryId'  => $requestData['enquiry_id'],
                    'parameters' => $requestData['params']
                ]);
    
            //return $response;
             if($response['success']==true) 
                {
                $RawbillAmount= $response['data']['billerResponse']['billAmount'] ?? 0;

                    $PayAbleAmount=$RawbillAmount/100;      
                DB::table('bbps_fetches')->insert([
                'username'      => $merchantId,
                'enquiryId'     => $requestData['enquiry_id'],
                'service'       => $request->categoryKey ?? 'BBPS',
                'amount'        => $PayAbleAmount,
                'response_body' => json_encode($response['data']),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
                     return [
                'success' => true,
                'message' => $response['message'] ?? 'Bill fetched',
                'data' => $response['data'] ?? []
            ];
                } 
                
                else
                    {
                        return [
                'success' => true,
                'message' => $response['message'] ?? 'Bill fetched failed',
                'details' => $response['details'] ?? []
            ];
                    }
            

        } catch (\Exception $e) {
            return self::error($e);
        }
    }

   public static function payBill(array $requestData)
{
    try {
        $response = Http::withHeaders(self::headers())
            ->post(self::$baseUrl . '/bbps/payBill', [
                'billerId'    => $requestData['biller_id'],
                'enquiryId'   => $requestData['enquiry_id'],
                'amount'      => $requestData['amount'],
                'externalRef' => $requestData['externalRef'],
                'merchantId'  => "69d883645988ede9d37450e5",
                'customerName'=>'Ganesh Kumar Sharma',
                'customerMobile'=>'9119393863',
                'customerEmail'=>'ganeshsharmahr6@gmail.com',
                'customerPan' => $requestData['customer_pan'] ?? 'HJCPS6875P'
            ]);

        $responseData = $response->json();
        Log::info('PayBill Response', $responseData);
            //return $requestData;
        // ✅ HTTP + API  check
        if ($response->successful() && isset($responseData['success']) && $responseData['success'] == true) {
            return [
                'success' => true,
                // 'response1'=>$responseData,
                'message' => $responseData['message'] ?? 'Payment processed',
                'data' =>  $responseData['data']
            ];
        } else {
            return [
                'success' => false,
                // 'response1'=>$responseData,
                'message' => $responseData['message'] ?? 'Payment failed',
                'data' => $responseData['data'] ?? []
            ];
        }

    } catch (\Exception $e) {

        return [
            'success' => false,
            'message' => $e,
        ];
    }
}
    // ❌ Common Error Handler
    private static function error($e)
    {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'data' => null
        ];
    }

   // ✅ Create Merchant API
    public static function createMerchant($data)
    {
        try {
            $response = Http::withHeaders(self::headers())
                ->post('https://chagans.com/bbps/createMerchant', $data);


                return $response;
            return [
                'status' => true,
                'response' => $response->json()
            ];
            

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}