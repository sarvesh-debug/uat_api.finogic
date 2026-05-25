<?php

namespace App\Services;

use App\Helpers\chaganHelper;
use Illuminate\Support\Facades\DB;

class AepsService
{
    /* ================= MERCHANT ================= */

    public static function createMerchant($data)
    {
        $response = chaganHelper::createMerchant($data);

        // Save in DB (reseller mapping)
        if ($response['status'] == 200 && $response['data']['success']) {
            DB::table('aeps_merchants')->insert([
                'merchant_id' => $response['data']['data']['merchantId'],
                'name'        => $data['name'],
                'mobile'      => $data['mobile'],
                'created_at'  => now()
            ]);
        }

        return $response;
    }

    /* ================= LOGIN ================= */

    public static function login($data)
    {
        return chaganHelper::aepsLogin($data);
    }

    public static function loginStatus($data)
    {
        return chaganHelper::loginStatus($data);
    }

    /* ================= TRANSACTION ================= */

    public static function transaction($data)
    {
        // 🔥 Reseller Commission Logic Example
        $commission = 0;

        if ($data['type'] == 'withdraw') {
            $commission = 5; // example
        }

        $response = chaganHelper::aepsPayment($data);

        // Save transaction
        DB::table('aeps_transactions')->insert([
            'merchant_id' => $data['merchantId'],
            'type'        => $data['type'],
            'amount'      => $data['amount'] ?? 0,
            'commission'  => $commission,
            'response'    => json_encode($response),
            'created_at'  => now()
        ]);

        return $response;
    }
} 