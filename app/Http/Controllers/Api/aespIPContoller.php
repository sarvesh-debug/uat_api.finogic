<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\IpaymentHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
 use Illuminate\Support\Facades\Log;


class aespIPContoller extends Controller
{
        public function localAuth($apiKey, $merchantId, $request)
{
    $clientIp = $request->ip();

    $merchant = DB::table('remittances')
        ->where('apikey', $apiKey)
        ->where('remId', $merchantId)
        ->first();

        //return $merchant;
    /* ===============================
       1️⃣ API Key / Merchant Validation
    ================================ */
    if (!$merchant) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'INVALID_CREDENTIALS',
                'message' => 'Invalid API Key or Merchant ID'
            ],
            'meta' => [
                'timestamp'  => now(),
                'request_ip' => $clientIp
            ]
        ], 401);
    }

    /* ===============================
       2️⃣ Merchant Status Check
    ================================ */
    if ($merchant->status !== 'success') {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE',
                'message' => 'Merchant account is inactive'
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'timestamp'  => now()
            ]
        ], 403);
    }
     /* ===============================
       2️⃣ Merchant Service  Active
    ================================ */
    if ($merchant->isAEPS !== 1) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE_SRVICE',
                'message' => 'Merchant Aeps Service is inactive'
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'timestamp'  => now()
            ]
        ], 403);
    }
     $service = DB::table('apis')
    ->where('name', 'AEPS')
    ->first();

if (!$service || $service->status != 1) {
    return response()->json([
        'success' => false,
        'error'   => [
            'code'    => 'SERVICE_INACTIVE',
            'message' => $service->message ?? 'Service is currently inactive'
        ],
        'meta' => [
            'merchantId' => $merchantId,
            'timestamp'  => now()
        ]
    ], 403);
}
     /* ===============================
       2️⃣ Merchant Service  Active
    ================================ */
    if ($merchant->isKyc !== 1) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE_SERVICE',
                'message' => 'Merchant kYC Is pending'
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'timestamp'  => now()
            ]
        ], 403);
    }

    /* ===============================
       3️⃣ IP Whitelisting Check
    ================================ */
    if ($merchant->ipAddress !== null) {

        $allowedIps = is_array($merchant->ipAddress)
            ? $merchant->ipAddress
            : explode(',', $merchant->ipAddress);

        if (!in_array($clientIp, $allowedIps)) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'IP_NOT_WHITELISTED',
                    'message' => 'Access denied from this IP address'
                ],
                'meta' => [
                    'merchantId' => $merchantId,
                    'request_ip' => $clientIp,
                    'timestamp'  => now()
                ]
            ], 403);
        }
    }

    /* ===============================
       ✅ Auth Successful
    ================================ */
    return $merchant;
}

    /* ============================================================
       🔐 COMMON HEADER + AUTH VALIDATION
    ============================================================ */
    private function validateAuth(Request $request, string $requestId)
    {
        $apiKey     = $request->header('X-API-KEY');
        $merchantId = $request->header('X-MERCHANT-ID');

        if (!$apiKey || !$merchantId) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'AUTH_HEADER_MISSING',
                    'message' => 'X-API-KEY and X-MERCHANT-ID headers are required'
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 401);
        }

        $auth = $this->localAuth($apiKey, $merchantId, $request);

        if ($auth instanceof JsonResponse) {
            return $auth;
        }

        return true;
    }

        // ================= CREATE MERCHANT =================


//use Illuminate\Support\Facades\Log;

public function merchantOnboarding(Request $request)
{

        //return $request;
    $requestId = 'req_' . Str::uuid();

    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

      $validator = Validator::make($request->all(), [
        'retailerId' =>'required',
        'mobile' => 'required|digits:10',
        'name' => 'required|string',
        'aadhaarNo' => 'required',
        'pan' => 'required',
        'email' => 'required|email',
        'latitude' => 'required',
        'longitude' => 'required',
    ]);

    if ($validator->fails()) {

        return response()->json([
            'status' => false,
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
            'requestId' => $requestId
        ], 422);
    }


    $outletId = $request->header('X-MERCHANT-ID');

    if (!$outletId) {
        return response()->json([
            'status' => false,
            'code' => 400,
            'requestId' => $requestId,
            'message' => 'Outlet ID missing'
        ], 400);
    }

    $merchantDetails = DB::table('remittances')
        ->where('remId', $outletId)
        ->first();

    if (!$merchantDetails) {
        return response()->json([
            'status' => false,
            'code' => 404,
            'requestId' => $requestId,
            'message' => 'Invalid Merchant'
        ], 404);
    }

    // Payload
      $payload = [
        "mobile" => $request->mobile,
        "name" => $request->name,
        "email" => $request->email,
        "latitude" => $request->latitude,
        "longitude" => $request->longitude,
    ];

    

    // ✅ Request Log
    Log::info('AEPS Create Merchant Request', [
        'requestId' => $requestId,
        'payload'   => $payload
    ]);

  $api = IpaymentHelper::merchantKyc($payload);

    // ✅ Response Log
    Log::info('AEPS Create Merchant Response', [
        'requestId' => $requestId,
        'response'  => $api
    ]);

    // ❗ FIXED CONDITION
    if ($api['status'] != true) {
        return response()->json([
            'status' => false,
            'code' => $api['code'],
            'requestId' => $requestId,
            'message' => $api['data']['providerResponse'] ?? 'Bank Server Down Time'
        ], 200);
    }

    try {

        $aepsMerchantId = $api['data']['kid'] ?? null;

        AepsMerchant::create([
            'request_id' => $requestId,
            'outlet_id' => $request->retailerId,
            'merchant_id' => $outletId,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'aadhaar' => $request->aadhaarNo,
            'pan' => strtoupper($request->pan),
            'request_payload' => $payload,
            'response_payload' => $api,
            'status' => 'success',
            'kyc_status' => $api['data']['kycStatus'] ?? 'pending',
            'kycId' =>$aepsMerchantId,
        ]);

    } catch (\Exception $e) {

        // ❌ Error Log
        Log::error('AEPS DB Error', [
            'requestId' => $requestId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status' => 'FAILURE',
            'code' => '0x0202',
            'requestId' => $requestId,
            'message' => 'DB Error'
        ], 500);
    }

     return response()->json([
        'status' => $api['status'],
        'code' => $api['code'],
        'message' => $api['message'],
        'requestId' => $requestId,
        'data' => $api['data']
    ]);
}


public function merchantKycStatus(Request $request, $kid)
{
    $requestId = 'req_' . Str::uuid();

    // ✅ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);

    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    $api = IpaymentHelper::merchantKycStatus($kid);

    // ✅ Proper Update
    AepsMerchant::where('kycId', $kid)
        ->update([
            'kycdata' => json_encode($api['data'])
        ]);

    return response()->json([
        'status' => $api['status'],
        'code' => $api['code'],
        'message' => $api['message'],
        'requestId' => $requestId,
        'data' => $api['data']
    ]);
}



//2FA
public function twoFactorAuth(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // ✅ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);

    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // ✅ Validation
    $validator = Validator::make($request->all(), [
        'merchantLoginId' => 'required',
        'rdRequest'       => 'required',
        'latitude'        => 'required',
        'longitude'       => 'required',
    ]);

    if ($validator->fails()) {

        return response()->json([
            'status'    => false,
            'code'      => 422,
            'requestId' => $requestId,
            'message'   => 'Validation Error',
            'errors'    => $validator->errors()
        ], 422);
    }

    // ✅ Payload
    $payload = [
        "merchantLoginId" => $request->merchantLoginId,
        "rdRequest"       => $request->rdRequest,
        "latitude"        => $request->latitude,
        "longitude"       => $request->longitude,
    ];

    // ✅ Request Log
    Log::info('AEPS 2FA Request', [
        'requestId' => $requestId,
        'payload'   => $payload
    ]);

    // ✅ API Call
    $api = IpaymentHelper::twoFactorAuth($payload);

    // ✅ Response Log
    Log::info('AEPS 2FA Response', [
        'requestId' => $requestId,
        'response'  => $api
    ]);

    // ✅ Final Response
    return response()->json([
        'status'    => $api['status'],
        'code'      => $api['code'],
        'message'   => $api['message'],
        'requestId' => $requestId,
        'data'      => $api['data'] ?? []
    ]);
}

public function cashWithdrawal(Request $request)
{
    return $this->aepsTxn($request, 'cw');
}


public function balanceEnquiry(Request $request)
{
    return $this->aepsTxn($request, 'be');
}


public function miniStatement(Request $request)
{
    return $this->aepsTxn($request, 'ms');
}



private function aepsTxn(Request $request, $type)
{
    $requestId = 'req_' . Str::uuid();

    // ✅ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);

    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    $validator = Validator::make($request->all(), [
        'merchantLoginId' => 'required',
        'aadhaar' => 'required',
        'mobile' => 'required',
        'rdRequest' => 'required',
        'bankiin' => 'required',
        'latitude' => 'required',
        'longitude' => 'required',
        'refId'    => 'required',
        'pan' => 'required',
    ]);

    $merchantId=$request->header('X-MERCHANT-ID');
     // ✅ Check Duplicate RefNo
        $existingTxn = DB::table('merchant_aeps_v2_transactions')
            ->where('merchant_id', $merchantId)
            ->where('refId', $request->refId)
            ->first();

        if ($existingTxn) {
            return response()->json([
                'status'  => false,
                'code'     =>409,
                'message' => 'Duplicate RefNo. Transaction with this RefNo already exists.'
            ], 409);
        }


    // ✅ amount required only for CW
    if ($type == 'cw') {

        $validator->addRules([
            'amount' => 'required|numeric|min:1'
        ]);



    }
     if ($validator->fails()) {

        return response()->json([
            'status' => false,
            'code' => 422,
            'requestId' => $requestId,
            'errors' => $validator->errors()
        ], 422);
    }

    if($type=='cw')
        {
              $merchant = DB::table('remittances')
            ->where('remId', $merchantId)
            ->lockForUpdate()
            ->first();

        if (!$merchant) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'MERCHANT_NOT_FOUND',
                    'message' => 'Invalid Merchant ID'
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp' => now()
                ]
            ], 404);


        }

        $merchantOpBalance  = (float) $merchant->amount;;

            $openingBal  = $merchantOpBalance;
            $amount     = $request->amount;
  
        // Fetch local commissions for the remittance package
            $commissions = DB::table('commissions')
                ->where('packagesId', $merchant->packageId)
                ->where('service', 'AEPS')
                ->get() ?? [];

        if ($commissions->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No commission structure found for your package. Please contact Admin.'
                ], 400);
            }
        // return $commissions;die();
            // Initialize charges and tds
            $charges = 0;
            $tds = 0;
            $comm=0;

            // Loop through commissions to find the applicable range
        foreach ($commissions as $item) {
        // Ensure numeric comparison
        $from = (float) $item->from_amount;
        $to   = (float) $item->to_amount;

        if ($item->service === 'AEPS' && $amount >= $from && $amount <= $to) {
            // Calculate charges
            $charges = $item->charge_in === 'Percentage'
                ? $amount * ((float) $item->charge) / 100
                : (float) $item->charge;
            
            $comm=$item->commissions_in==='Percentage'
                ? $amount *((float)$item->commissions)/100
                :(float) $item->commissions;

            // Calculate TDS
            $tds = $item->tds_in === 'Percentage'
                ? $comm * ((float) $item->tds) / 100
                : (float) $item->tds;

            // Exit loop once a matching commission is found
            break;
        }
    }


            // $charges and $tds now have the calculated values
        //dd($charges, $tds);die();

            // ✅ Fallback charges
           if ($comm == 0 && $amount >= 100) {
                $comm = $amount * 0.01;
                $tds  = $comm * 0.05; // example 5%
            }

            $totalDeduct = ($amount + $comm) - $tds;
            $closingBal  = $openingBal + $totalDeduct;

            
        }

    $payload = [
        "merchantLoginId" => $request->merchantLoginId,
        "aadhaar" => $request->aadhaar,
        "txnType" => $type,
        "mobile" => $request->mobile,
        "amount" => $request->amount ?? 0,
        "rdRequest" => $request->rdRequest,
        "bankiin" => $request->bankiin,
        "latitude" => $request->latitude,
        "longitude" => $request->longitude,
        "pan" => strtoupper($request->pan),
    ];



    Log::info('AEPS Transaction Request', [
        'requestId' => $requestId,
        'type' => $type,
        'payload' => $payload
    ]);

    $api = IpaymentHelper::aepsTransaction($payload);

    Log::info('AEPS Transaction Response', [
        'requestId' => $requestId,
        'response' => $api
    ]);


    $response=$api;

      if (($response['code'] ) === "0x0200" && ($response['status'] ?? '') === 'SUCCESS') {
        
    $openingBalance = $openingBal ?? 0;
    $closingBalance = $closingBal ?? 0;

    DB::table('remittances')
        ->where('remId', $merchantId)
        ->increment('amount',$closingBalance);

    DB::table('merchant_aeps_v2_transactions')->insert([
        'refId'             =>$request->refId,
        'request_id'        => $requestId,
        'merchant_id'       => $merchantId,
        'outlet_id'         => $request->merchantId,
        'transaction_type'  => $request->type,
        'transaction_mode'  => 'CR',

        'tds'        => $tds ?? 0,
        'commission' => $comm ?? 0,
        'charges'    => $charges ?? 0,

        // ❌ Old fields removed
        'provider_status'   => $response['status'] ?? null,
        'actcode'           => null,
        'ipay_uuid'         => null,
        'orderid'           => $response['data']['stanNo'] ?? null,
        'environment'       => null,

        'transaction_amount'=> $amount ?? 0,
        'payable_amount'    => $totalDeduct ?? 0,
        'opening_balance'   => $openingBalance,
        'closing_balance'   => $closingBalance,

        'bank_name'         => $response['data']['bankName'] ?? null,
        'account_last_four' => substr($response['data']['accountNumber'] ?? '', -4),
        'external_ref'      => $response['data']['rrn'] ?? null, // ✅ updated
        'operator_id'       => null,
        'is_onus_txn'       => null,

        'provider_response' => json_encode($api),
        'status'            => 'SUCCESS',

        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    

    return response()->json([
        'success' => "SUCCESS",
        'statuscode'=>'TXN',
        'type'=>$response['type'] ?? '',
        'message' => $response['message'] ?? 'Transaction Successful',
        'data' => [
            'transaction_amount'=> $amount ?? 0,
            'payable_amount'    => $totalDeduct ?? 0,
            'opening_balance'   => $openingBalance,
            'closing_balance'   => $closingBalance,

            'bank_name'         => $response['data']['bankName'] ?? null,
            'account_last_four' => substr($response['data']['accountNumber'] ?? '', -4),
            'utr'               => $response['data']['utr'] ?? null,
            'orderId'           => $response['data']['orderId'] ?? null,
            'balance'           => $response['data']['bankAccountBalance'] ?? null,
            'miniStatement'         => $response['data']['miniStatement'] ?? null,
        ],
        'meta' => [
            'request_id' => $requestId,
            'timestamp'  => now()
        ]
    ], 200);
}
    return response()->json([
        'status' => $api['status'],
        'code' => $api['code'],
        'message' => $api['message'],
        'requestId' => $requestId,
        'data' => $api['data']
    ]);
}


public function transactionStatus(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // ✅ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);

    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    $validator = Validator::make($request->all(), [
        'merchantLoginId' => 'required',
        'clientRefId' => 'required',
    ]);

    if ($validator->fails()) {

        return response()->json([
            'status' => false,
            'code' => 422,
            'requestId' => $requestId,
            'errors' => $validator->errors()
        ], 422);
    }

    $payload = [
        "merchantLoginId" => $request->merchantLoginId,
        "clientRefId" => $request->clientRefId,
    ];

    Log::info('AEPS Status Request', [
        'requestId' => $requestId,
        'payload' => $payload
    ]);

    $api = IpaymentHelper::transactionStatus($payload);

    Log::info('AEPS Status Response', [
        'requestId' => $requestId,
        'response' => $api
    ]);

    return response()->json([
        'status' => $api['status'],
        'code' => $api['code'],
        'message' => $api['message'],
        'requestId' => $requestId,
        'data' => $api['data']
    ]);
}

}
