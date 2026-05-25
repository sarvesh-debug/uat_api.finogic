<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChaganHelper
{
    protected static $baseUrl = 'https://chagans.com/aeps';

    protected static function headers()
    {
        return [
            'client-id'     => env('BBPS_CLIENT_ID'),
            'client-secret' => env('BBPS_CLIENT_SECRET'),
            'authorization' => 'Bearer ' . env('BBPS_TOKEN'),
            'apiType'       => 'aeps',
            'Content-Type'  => 'application/json'
        ];
    }

    protected static function hitApi($endpoint, $payload = [])
    {
        //return $payload;
        try {
            $response = Http::withHeaders(self::headers())
                ->post(self::$baseUrl . $endpoint, $payload);

            $json = $response->json();
          //return $json;
            Log::error('AEPS Chagn Response: ' . json_encode($json));
            if($json['success']==true)
                {
                return [
                'status' => $json['success'],
                'code'   => $json['code'] ?? '200',
                'data'   => $json['data'] ?? $json,
                'type'  =>$json['type'] ?? '',
                 ];
                }
            else{
            return [
                'status' => $json['success'],
                'code'   => $json['code'] ?? '400',
                'data'   => $json['message'] ?? $json,
                 'type'  =>$json['type'] ?? '',
                 ];
            }
            

        } catch (\Exception $e) {

            Log::error('AEPS API Error: ' . $e->getMessage());

            return [
                'status'  => false,
                'code'    => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    // ================= MERCHANT =================

    public static function createMerchant($data)
    {
        return self::hitApi('/createMerchant', $data);
    }

    public static function merchantList($data)
    {
        return self::hitApi('/merchantList', $data);
    }

    // ================= AUTH =================

    public static function loginStatus($data)
    {
        return self::hitApi('/loginStatus', $data);
    }

    public static function aepsLogin($data)
    {
        return self::hitApi('/aepsLogin', $data);
    }

    // ================= TRANSACTION =================

    public static function aepsPayment($data)
    {
        return self::hitApi('/aepsPayment', $data);
    }
//     public static function aepsPayment($data)
// {
//     return [
//         "status" => true,
//         "code"   => "200",
//         "type"   => "ministatement", // 👈 change to withdraw / balance / mini_statement for testing

//         "data"   => [
//             "status" => "success",
//             "orderId" => "CTLAEPS" . time() . rand(1000,9999),
//             "bankName" => "INDIAN BANK",
//             "accountNumber" => "xxxxxxxx6719",
//             "amount" => "1000.00",
//             "utr" => "00",
//             "bankAccountBalance" => "17.00",

//             // ✅ Dummy Mini Statement Added
//             "miniStatement" => [
//                 ["date"=>"31/12","txnType"=>"POS/D/","amount"=>"1.0","narration"=>"31/12 POS/D/ C 1.00"],
//                 ["date"=>"26/11","txnType"=>"POS/W/","amount"=>"42.0","narration"=>"26/11 POS/W/ D 42.00"],
//                 ["date"=>"07/11","txnType"=>"POS/W/","amount"=>"300.0","narration"=>"07/11 POS/W/ D 300.00"],
//                 ["date"=>"27/10","txnType"=>"POS/W/","amount"=>"42.0","narration"=>"27/10 POS/W/ D 42.00"],
//                 ["date"=>"30/09","txnType"=>"POS/D/","amount"=>"3.0","narration"=>"30/09 POS/D/ C 3.00"],
//                 ["date"=>"27/09","txnType"=>"POS/W/","amount"=>"42.0","narration"=>"27/09 POS/W/ D 42.00"],
//                 ["date"=>"27/08","txnType"=>"POS/W/","amount"=>"42.0","narration"=>"27/08 POS/W/ D 42.00"],
//                 ["date"=>"28/07","txnType"=>"POS/W/","amount"=>"42.0","narration"=>"28/07 POS/W/ D 42.00"],
//                 ["date"=>"30/06","txnType"=>"POS/D/","amount"=>"8.0","narration"=>"30/06 POS/D/ C 8.00"]
//             ],

//             "txnId" => "CDAEPSF5VDY3"
//         ]
//     ];
// }
}